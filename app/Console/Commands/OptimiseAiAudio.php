<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;

/**
 * Bersihkan & kecilkan audio AI:
 *   - kekalkan hanya sebutan utama — buang bunyi berasingan selepasnya (model Gemini 3.x
 *     sering menambah nafas/klik pendek selepas senyap di hujung klip);
 *   - fade masuk/keluar pendek supaya tiada bunyi "klik" pada potongan;
 *   - buang dengung frekuensi rendah;
 *   - WAV → MP3 (≈10× lebih kecil) untuk simpanan offline di telefon.
 * Memerlukan ffmpeg. Fail yang sudah dibersihkan ditanda dalam index.json (`clean`).
 */
class OptimiseAiAudio extends Command
{
    protected $signature = 'audio:optimise {--semula : Proses semula semua fail walaupun sudah dibersihkan}';

    protected $description = 'Bersihkan hujung audio AI (buang bunyi ekor) & tukar ke MP3 — perlukan ffmpeg';

    /** Naikkan bila langkah pembersihan berubah — fail lama akan diproses semula. */
    public const CLEAN_VERSION = 2;

    private const SILENCE_DB = -40;      // di bawah ini dikira senyap
    private const MIN_SILENCE = 0.12;    // senyap sekurang-kurangnya ini memisahkan bunyi
    private const JOIN_GAP = 0.15;       // jurang lebih kecil = masih sebutan yang sama
    private const FADE = 0.06;

    public function handle(): int
    {
        $finder = new ExecutableFinder;
        $ffmpeg = $finder->find('ffmpeg');
        if (!$ffmpeg) {
            $this->warn('ffmpeg tiada — audio dibiarkan seperti asal (masih berfungsi).');
            return self::SUCCESS;
        }

        $indexPath = public_path('audio/ai/index.json');
        if (!is_file($indexPath)) {
            $this->info('Tiada audio AI untuk diproses.');
            return self::SUCCESS;
        }
        $index = json_decode(file_get_contents($indexPath), true);

        $done = 0;
        $trimmed = 0;
        foreach ($index['items'] as $key => $item) {
            if (!$this->option('semula') && ($item['clean'] ?? 0) >= self::CLEAN_VERSION) {
                continue;
            }
            $src = public_path(ltrim($item['file'], '/'));
            if (!is_file($src)) {
                continue;
            }

            [$duration, $start, $end] = $this->mainSpeech($ffmpeg, $src);
            if ($end === null) {
                $this->warn("Tiada sebutan dikesan dalam {$item['file']} — dilangkau.");
                continue;
            }
            if ($duration - $end > 0.08) {
                $trimmed++;
            }

            $length = $end - $start;
            $filter = implode(',', [
                'highpass=f=60',
                'afade=t=in:d=0.01',
                sprintf('afade=t=out:st=%.3f:d=%.3f', max(0, $length - self::FADE), self::FADE),
                'adelay=80:all=1',       // ruang kecil di depan supaya pelayar tidak memotong suku kata pertama
                'apad=pad_dur=0.1',
            ]);

            $tmp = tempnam(sys_get_temp_dir(), 'aud').'.mp3';
            $process = new Process([$ffmpeg, '-hide_banner', '-loglevel', 'error', '-y',
                '-ss', sprintf('%.3f', $start), '-to', sprintf('%.3f', $end), '-i', $src,
                '-af', $filter, '-ac', '1', '-ar', '44100', '-c:a', 'libmp3lame', '-b:a', '96k', $tmp]);
            $process->run();

            if (!$process->isSuccessful() || !filesize($tmp)) {
                $this->warn("Gagal proses {$item['file']}: ".trim($process->getErrorOutput()));
                @unlink($tmp);
                continue;
            }

            $mp3 = preg_replace('/-[0-9a-f]{8}\.(wav|mp3)$/', '-'.substr(md5_file($tmp), 0, 8).'.mp3', $item['file']);
            File::move($tmp, public_path(ltrim($mp3, '/')));
            if ($mp3 !== $item['file']) {
                File::delete($src);
            }

            $index['items'][$key]['file'] = $mp3;
            $index['items'][$key]['clean'] = self::CLEAN_VERSION;
            $done++;
        }

        file_put_contents($indexPath, json_encode($index, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        $this->info("{$done} fail dibersihkan ({$trimmed} ada bunyi ekor yang dibuang).");

        return self::SUCCESS;
    }

    /**
     * Cari julat sebutan utama: himpunan bunyi terpanjang selepas mencantumkan bunyi
     * yang dipisahkan jurang pendek. Pulangkan [tempoh fail, mula, tamat].
     */
    private function mainSpeech(string $ffmpeg, string $file): array
    {
        $process = new Process([$ffmpeg, '-hide_banner', '-i', $file,
            '-af', sprintf('silencedetect=noise=%ddB:d=%.2f', self::SILENCE_DB, self::MIN_SILENCE), '-f', 'null', '-']);
        $process->run();
        $log = $process->getErrorOutput();

        preg_match('/Duration: (\d+):(\d+):([\d.]+)/', $log, $d);
        $duration = $d ? $d[1] * 3600 + $d[2] * 60 + (float) $d[3] : 0;
        preg_match_all('/silence_start: ([\d.]+)/', $log, $s);
        preg_match_all('/silence_end: ([\d.]+)/', $log, $e);

        // Bunyi = pelengkap kepada senyap.
        $silences = [];
        foreach ($s[1] as $i => $start) {
            $silences[] = [(float) $start, isset($e[1][$i]) ? (float) $e[1][$i] : $duration];
        }
        $sounds = [];
        $cursor = 0.0;
        foreach ($silences as [$a, $b]) {
            if ($a > $cursor + 0.01) {
                $sounds[] = [$cursor, $a];
            }
            $cursor = max($cursor, $b);
        }
        if ($cursor < $duration - 0.01) {
            $sounds[] = [$cursor, $duration];
        }
        if (!$sounds) {
            return [$duration, 0, null];
        }

        // Cantumkan bunyi yang hampir (masih satu sebutan), pilih himpunan terpanjang.
        $clusters = [$sounds[0]];
        foreach (array_slice($sounds, 1) as [$a, $b]) {
            $last = &$clusters[count($clusters) - 1];
            if ($a - $last[1] < self::JOIN_GAP) {
                $last[1] = $b;
            } else {
                $clusters[] = [$a, $b];
            }
            unset($last);
        }
        usort($clusters, fn ($x, $y) => ($y[1] - $y[0]) <=> ($x[1] - $x[0]));
        [$start, $end] = $clusters[0];

        return [$duration, max(0, $start - 0.03), min($duration, $end + 0.06)];
    }
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;

/**
 * Kecilkan audio AI: buang senyap di awal/akhir dan tukar WAV → MP3 (≈10× lebih kecil),
 * supaya simpanan offline di telefon ringan. Memerlukan ffmpeg.
 */
class OptimiseAiAudio extends Command
{
    protected $signature = 'audio:optimise';

    protected $description = 'Buang senyap & tukar audio AI (WAV) kepada MP3 — perlukan ffmpeg';

    public function handle(): int
    {
        $ffmpeg = (new ExecutableFinder)->find('ffmpeg');
        if (!$ffmpeg) {
            $this->warn('ffmpeg tiada — audio dibiarkan dalam WAV (masih berfungsi, cuma lebih besar).');
            return self::SUCCESS;
        }

        $indexPath = public_path('audio/ai/index.json');
        if (!is_file($indexPath)) {
            $this->info('Tiada audio AI untuk dioptimumkan.');
            return self::SUCCESS;
        }
        $index = json_decode(file_get_contents($indexPath), true);

        $done = 0;
        $before = 0;
        $after = 0;
        foreach ($index['items'] as $key => $item) {
            if (!str_ends_with($item['file'], '.wav')) {
                continue;
            }
            $src = public_path(ltrim($item['file'], '/'));
            if (!is_file($src)) {
                continue;
            }

            $tmp = tempnam(sys_get_temp_dir(), 'aud').'.mp3';
            // Buang senyap hujung-ke-hujung (kekal 0.1 s), padding kecil di depan supaya suku kata pertama tidak terpotong oleh pelayar.
            $filter = 'silenceremove=start_periods=1:start_threshold=-45dB:start_silence=0.1,'
                .'areverse,silenceremove=start_periods=1:start_threshold=-45dB:start_silence=0.15,areverse,'
                .'adelay=80:all=1';
            $process = new Process([$ffmpeg, '-hide_banner', '-loglevel', 'error', '-y', '-i', $src,
                '-af', $filter, '-ac', '1', '-ar', '44100', '-c:a', 'libmp3lame', '-b:a', '64k', $tmp]);
            $process->run();

            if (!$process->isSuccessful() || !filesize($tmp)) {
                $this->warn("Gagal tukar {$item['file']}: ".trim($process->getErrorOutput()));
                @unlink($tmp);
                continue;
            }

            $mp3 = preg_replace('/-[0-9a-f]{8}\.wav$/', '-'.substr(md5_file($tmp), 0, 8).'.mp3', $item['file']);
            File::move($tmp, public_path(ltrim($mp3, '/')));
            $before += filesize($src);
            $after += filesize(public_path(ltrim($mp3, '/')));
            File::delete($src);

            $index['items'][$key]['file'] = $mp3;
            $done++;
        }

        file_put_contents($indexPath, json_encode($index, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        $this->info(sprintf('%d fail ditukar: %.1f MB → %.1f MB', $done, $before / 1048576, $after / 1048576));

        return self::SUCCESS;
    }
}

<?php

namespace App\Console\Commands;

use App\Models\ArabicLetter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Import rakaman sebutan dari folder. Nama fail ikut nombor urutan huruf:
 * 01.mp3 = Alif, 02.mp3 = Ba, … 29.mp3 = Hamzah.
 */
class ImportLetterAudio extends Command
{
    protected $signature = 'audio:import {folder : Folder berisi 01.mp3 … 29.mp3 (atau .ogg/.m4a/.wav/.webm)}';

    protected $description = 'Import rakaman sebutan huruf dari folder (nama fail = nombor urutan huruf)';

    public function handle(): int
    {
        $folder = rtrim($this->argument('folder'), '/');
        if (!is_dir($folder)) {
            $this->error("Folder tidak wujud: {$folder}");
            return self::FAILURE;
        }

        $imported = 0;
        foreach (ArabicLetter::orderBy('order_num')->get() as $letter) {
            $matches = glob(sprintf('%s/%02d.{mp3,ogg,m4a,wav,webm}', $folder, $letter->order_num), GLOB_BRACE);
            if (!$matches) {
                $this->line(sprintf('  %02d %s — tiada fail, kekal TTS', $letter->order_num, $letter->name));
                continue;
            }

            $ext = pathinfo($matches[0], PATHINFO_EXTENSION);
            $path = 'audio/huruf-'.$letter->id.'-'.time().'.'.$ext;
            Storage::disk('public')->put($path, file_get_contents($matches[0]));
            $letter->update(['audio_url' => '/storage/'.$path]);

            $this->info(sprintf('  %02d %s ✓', $letter->order_num, $letter->name));
            $imported++;
        }

        $this->newLine();
        $this->info("{$imported} rakaman diimport.");

        return self::SUCCESS;
    }
}

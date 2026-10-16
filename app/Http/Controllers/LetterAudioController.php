<?php

namespace App\Http\Controllers;

use App\Models\ArabicLetter;
use App\Support\OpenAudio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Rakaman sebutan rasmi (guru/qari) bagi setiap huruf — menggantikan suara TTS.
 */
class LetterAudioController extends Controller
{
    public function store(Request $request, ArabicLetter $letter)
    {
        $this->authorizeTeacher($request);

        $request->validate([
            // Chrome merakam audio/webm (dikesan sebagai video/webm), Safari audio/mp4.
            'audio' => 'required|file|max:2048|mimetypes:audio/webm,video/webm,audio/ogg,audio/mpeg,audio/mp4,video/mp4,audio/x-m4a,audio/wav,audio/x-wav',
        ]);

        $this->deleteStored($letter);

        $ext = $request->file('audio')->guessExtension() ?: 'webm';
        $path = $request->file('audio')->storeAs('audio', 'huruf-'.$letter->id.'-'.time().'.'.$ext, 'public');

        // Laluan relatif supaya tetap berfungsi walau APP_URL berbeza (telefon, domain lain).
        $letter->update(['audio_url' => '/storage/'.$path]);

        return response()->json(['audio_url' => $letter->audio_url]);
    }

    public function destroy(Request $request, ArabicLetter $letter)
    {
        $this->authorizeTeacher($request);

        $this->deleteStored($letter);
        $letter->update(['audio_url' => null]);

        // Kembali kepada rakaman terbuka (jika ada) — pelayar kemas kini peta audio dengan ini.
        return response()->json(['audio_url' => null, 'fallback' => OpenAudio::clientEntry($letter->arabic)]);
    }

    private function authorizeTeacher(Request $request): void
    {
        $key = config('hafalan.audio_key');

        abort_if(blank($key), 403, 'Mod Guru belum diaktifkan (HAFALAN_AUDIO_KEY).');
        abort_unless(hash_equals((string) $key, (string) $request->header('X-Teacher-Key')), 403, 'Kunci guru salah.');
    }

    private function deleteStored(ArabicLetter $letter): void
    {
        if ($letter->audio_url && str_starts_with($letter->audio_url, '/storage/')) {
            Storage::disk('public')->delete(substr($letter->audio_url, strlen('/storage/')));
        }
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class TtsController extends Controller
{
    public function speak(Request $request)
    {
        $text = $request->query('text', '');

        if (empty($text)) {
            return response('', 400);
        }

        $url = 'https://translate.google.com/translate_tts';
        $response = Http::withHeaders([
            'User-Agent' => 'Mozilla/5.0 (compatible; Googlebot/2.1)',
            'Referer'    => 'https://translate.google.com/',
        ])->timeout(8)->get($url, [
            'ie'       => 'UTF-8',
            'q'        => $text,
            'tl'       => 'ar',
            'client'   => 'tw-ob',
            'ttsspeed' => '0.8',
        ]);

        if (!$response->successful()) {
            return response('', 502);
        }

        return response($response->body(), 200)
            ->header('Content-Type', 'audio/mpeg')
            ->header('Cache-Control', 'public, max-age=86400')
            ->header('Access-Control-Allow-Origin', '*');
    }
}

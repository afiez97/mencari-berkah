<?php

return [
    // Kunci "Mod Guru" untuk menyimpan rakaman sebutan rasmi. Kosong = rakaman rasmi dimatikan.
    'audio_key' => env('HAFALAN_AUDIO_KEY'),

    // Enjin suara AI lalai untuk audio:ai — gemini | chat (OpenAI, seperti ChatGPT) | tts (OpenAI TTS).
    // Kosong = gemini jika GEMINI_API_KEY ada, jika tidak chat.
    'ai_audio_engine' => env('AI_AUDIO_ENGINE'),

    // Jeda (saat) antara permintaan audio:ai. Kosong = 6 untuk Gemini (had free tier), 0 untuk lain.
    'ai_audio_delay' => env('AI_AUDIO_DELAY'),

    // Masa tunggu (ms) sebelum cuba semula bila API sibuk (429/503).
    'ai_retry_ms' => (int) env('AI_RETRY_MS', 2000),
];

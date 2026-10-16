<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    // Suara AI untuk sebutan huruf & suku kata (php artisan audio:ai).
    'openai' => [
        'key' => env('OPENAI_API_KEY'),
        'audio_model' => env('OPENAI_AUDIO_MODEL', 'gpt-audio'),
        'audio_voice' => env('OPENAI_AUDIO_VOICE', 'cedar'),
        'tts_model' => env('OPENAI_TTS_MODEL', 'gpt-4o-mini-tts'),
        'tts_voice' => env('OPENAI_TTS_VOICE', 'onyx'),
    ],

    // Suara AI Google Gemini (php artisan audio:ai --engine=gemini). Kunci percuma: aistudio.google.com/apikey
    'gemini' => [
        'key' => env('GEMINI_API_KEY'),
        // Dicuba ikut susunan; setiap model ada kuota harian free tier sendiri.
        'tts_models' => array_map('trim', explode(',', env('GEMINI_TTS_MODELS',
            'gemini-3.8-flash-tts,gemini-2.5-flash-preview-tts,gemini-3.1-flash-tts-preview,gemini-2.5-pro-preview-tts,gemini-3.8-flash-lite-tts'))),
        'tts_voice' => env('GEMINI_TTS_VOICE', 'Charon'),
        // Model yang "mendengar semula" setiap audio untuk sahkan huruf yang disebut (dicuba ikut susunan).
        'check_models' => array_map('trim', explode(',', env('GEMINI_CHECK_MODELS',
            'gemini-3.5-flash,gemini-3.8-flash,gemini-3.7-flash,gemini-3.6-flash,gemini-flash-latest,gemini-3.5-flash-lite'))),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

];

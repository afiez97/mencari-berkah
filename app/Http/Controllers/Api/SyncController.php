<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProgressSync;
use Illuminate\Http\Request;

/**
 * Selaras kemajuan antara peranti menggunakan kod rahsia (tanpa akaun).
 * Penggabungan dibuat di pelayar; pelayan hanya menyimpan salinan terkini.
 */
class SyncController extends Controller
{
    // Tiada 0/O, 1/I/L supaya kod mudah ditaip semula.
    private const ALPHABET = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';

    private const MAX_BYTES = 256 * 1024;

    public function store()
    {
        do {
            $code = '';
            for ($i = 0; $i < 12; $i++) {
                $code .= self::ALPHABET[random_int(0, strlen(self::ALPHABET) - 1)];
            }
        } while (ProgressSync::where('code', $code)->exists());

        ProgressSync::create(['code' => $code, 'data' => []]);

        return response()->json(['code' => $code], 201);
    }

    public function show(string $code)
    {
        $sync = ProgressSync::where('code', $this->normalise($code))->firstOrFail();

        return response()->json(['data' => $sync->data ?: new \stdClass, 'updated_at' => $sync->updated_at]);
    }

    public function update(Request $request, string $code)
    {
        $sync = ProgressSync::where('code', $this->normalise($code))->firstOrFail();

        abort_if(strlen($request->getContent()) > self::MAX_BYTES, 413, 'Data terlalu besar.');
        $validated = $request->validate(['data' => 'present|array']);

        $sync->update(['data' => $validated['data']]);
        $sync->touch();

        return response()->json(['updated_at' => $sync->updated_at]);
    }

    private function normalise(string $code): string
    {
        return strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $code));
    }
}

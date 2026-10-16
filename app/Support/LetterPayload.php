<?php

namespace App\Support;

use App\Models\ArabicLetter;

/**
 * Data huruf yang dihantar ke pelayar untuk sesi kilat, koleksi huruf dan peta kemajuan.
 */
class LetterPayload
{
    public static function all(): array
    {
        // Susunan huruf baharu ikut pelan (keluarga bentuk), bukan abjad.
        $planOrder = array_flip(StudyPlan::introOrder());

        return ArabicLetter::orderBy('order_num')->get()->map(fn ($l) => [
            'id' => $l->id,
            'arabic' => $l->arabic,
            'name' => $l->name,
            'mnemonic' => $l->mnemonic,
            'color' => $l->family_color,
            'family' => $l->group_label,
            'plan' => $planOrder[$l->arabic] ?? 99,
            'forms' => collect([$l->form_initial, $l->form_medial, $l->form_final])
                ->reject(fn ($f) => $f === $l->form_isolated)->unique()->values()->all(),
        ])->all();
    }

    /** Keluarga warna untuk legenda: [label, warna, contoh huruf]. */
    public static function legend(): array
    {
        return ArabicLetter::orderBy('order_num')->get()->groupBy('group_name')
            ->map(fn ($g) => ['label' => $g->first()->group_label, 'color' => $g->first()->family_color, 'sample' => $g->take(4)->pluck('arabic')->implode(' ')])
            ->values()->all();
    }
}

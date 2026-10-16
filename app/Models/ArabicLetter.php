<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArabicLetter extends Model
{
    protected $fillable = [
        'order_num', 'arabic', 'name', 'sound', 'transliteration', 'audio_url', 'group_name',
        'mnemonic', 'visual_description', 'form_isolated', 'form_initial',
        'form_medial', 'form_final', 'example_word', 'example_translation', 'difficulty',
    ];

    protected $casts = [
        'difficulty' => 'integer',
        'order_num' => 'integer',
    ];

    public function scopeByGroup($query, $group)
    {
        return $query->where('group_name', $group);
    }

    public function scopeByDifficulty($query, $level)
    {
        return $query->where('difficulty', $level);
    }

    /**
     * Returns a reliable audio URL for this letter.
     * Uses custom audio_url from DB if set, otherwise Google TTS (unofficial, no key needed).
     */
    public function getTtsUrlAttribute(): string
    {
        if ($this->audio_url) {
            return $this->audio_url;
        }
        return 'https://translate.google.com/translate_tts?ie=UTF-8&q='
            . urlencode($this->arabic)
            . '&tl=ar&client=tw-ob&ttsspeed=0.8';
    }

    /**
     * Warna tetap bagi setiap keluarga huruf — isyarat visual supaya bentuk serupa
     * diingat sebagai satu kumpulan (biru = titik, ungu = gigi, …).
     */
    public const FAMILY_COLORS = [
        'dot_family' => '#2F6FA8',
        'round_family' => '#B5552B',
        'straight_family' => '#3F7A47',
        'teeth_family' => '#7A4FA0',
        'tail_family' => '#9A6A1F',
        'misc' => '#A23E63',
    ];

    public function getFamilyColorAttribute(): string
    {
        return self::FAMILY_COLORS[$this->group_name] ?? '#A6845E';
    }

    public function getGroupLabelAttribute(): string
    {
        return match($this->group_name) {
            'dot_family' => 'Keluarga Titik',
            'round_family' => 'Keluarga Bulat',
            'straight_family' => 'Keluarga Lurus',
            'teeth_family' => 'Keluarga Gigi',
            'tail_family' => 'Keluarga Ekor',
            'misc' => 'Lain-lain',
            default => $this->group_name,
        };
    }
}

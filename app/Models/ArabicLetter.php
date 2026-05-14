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

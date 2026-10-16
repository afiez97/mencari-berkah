<?php

namespace App\Http\Controllers;

use App\Models\ArabicLetter;
use App\Support\Harakat;
use Illuminate\Http\Request;

class BarisController extends Controller
{
    public function index()
    {
        $letters = ArabicLetter::all();
        $rows = collect(Harakat::VOWELS)->map(fn ($v, $key) => $v + ['syllables' => Harakat::row($letters, $key)]);

        return view('baris.index', ['rows' => $rows, 'lessons' => Harakat::LESSONS]);
    }

    public function practice(Request $request)
    {
        $lesson = array_key_exists($request->get('pelajaran'), Harakat::LESSONS) ? $request->get('pelajaran') : 'campur';
        $mode = in_array($request->get('mode'), ['see', 'hear', 'mixed'], true) ? $request->get('mode') : 'mixed';
        $count = max(5, min(50, (int) $request->get('count', 15)));

        return view('baris.latih', [
            'lesson' => $lesson,
            'lessonTitle' => Harakat::LESSONS[$lesson]['title'],
            'questions' => Harakat::questions(ArabicLetter::all(), $lesson, $mode, $count),
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\ArabicLetter;
use Illuminate\Http\Request;

class HafalanController extends Controller
{
    public function home()
    {
        return view('home');
    }

    public function learn(Request $request)
    {
        $groups = ArabicLetter::all()->groupBy('group_name');
        if ($request->has('group')) {
            $letters = ArabicLetter::byGroup($request->group)->orderBy('order_num')->get();
            return view('learn.index', compact('groups', 'letters'));
        }
        return view('learn.index', compact('groups'));
    }

    public function show($id)
    {
        $letter = ArabicLetter::findOrFail($id);
        $prevId = ArabicLetter::where('order_num', '<', $letter->order_num)->orderBy('order_num', 'desc')->value('id');
        $nextId = ArabicLetter::where('order_num', '>', $letter->order_num)->orderBy('order_num')->value('id');
        return view('learn.show', compact('letter', 'prevId', 'nextId'));
    }

    public function quiz()
    {
        return view('quiz.index');
    }

    public function quizPlay(Request $request)
    {
        $mode = $request->get('mode', 'mixed');
        $count = $request->get('count', 10);
        $groups = $request->get('groups', []);

        $query = ArabicLetter::query();
        if (!empty($groups)) {
            $query->whereIn('group_name', $groups);
        }
        $allLetters = $query->get();

        if ($count !== 'all') {
            $allLetters = $allLetters->random(min((int)$count, $allLetters->count()));
        }

        $questions = $allLetters->map(function ($letter) use ($mode) {
            $questionMode = $mode === 'mixed' ? collect(['name', 'letter'])->random() : $mode;
            $wrongOptions = ArabicLetter::where('id', '!=', $letter->id)->inRandomOrder()->limit(3)->get();

            if ($questionMode === 'name') {
                $options = $wrongOptions->pluck('name')->push($letter->name)->shuffle()->values();
                return [
                    'id' => $letter->id,
                    'mode' => 'name',
                    'question' => $letter->arabic,
                    'answer' => $letter->name,
                    'options' => $options,
                    'mnemonic' => $letter->mnemonic,
                    'tts_url' => $letter->tts_url,
                    'transliteration' => $letter->transliteration,
                ];
            } else {
                $options = $wrongOptions->pluck('arabic')->push($letter->arabic)->shuffle()->values();
                return [
                    'id' => $letter->id,
                    'mode' => 'letter',
                    'question' => $letter->name,
                    'answer' => $letter->arabic,
                    'options' => $options,
                    'mnemonic' => $letter->mnemonic,
                    'tts_url' => $letter->tts_url,
                    'transliteration' => $letter->transliteration,
                ];
            }
        });

        return view('quiz.play', ['questions' => $questions->values()]);
    }

    public function result()
    {
        return view('quiz.result');
    }

    public function stats()
    {
        return view('stats.index');
    }
}

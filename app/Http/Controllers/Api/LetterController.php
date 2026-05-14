<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ArabicLetter;
use App\Models\QuizSession;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LetterController extends Controller
{
    public function index(Request $request)
    {
        $query = ArabicLetter::query();
        if ($request->has('group')) {
            $query->where('group_name', $request->group);
        }
        if ($request->has('difficulty')) {
            $query->where('difficulty', $request->difficulty);
        }
        return response()->json($query->orderBy('order_num')->get());
    }

    public function show(string $id)
    {
        return response()->json(ArabicLetter::findOrFail($id));
    }

    public function saveSession(Request $request)
    {
        $validated = $request->validate([
            'mode' => 'required|string',
            'total_questions' => 'required|integer',
            'correct' => 'required|integer',
            'wrong' => 'required|integer',
            'duration_seconds' => 'required|integer',
        ]);

        $token = Str::random(32);

        \DB::table('quiz_sessions')->insert([
            'session_token' => $token,
            'mode' => $validated['mode'],
            'total_questions' => $validated['total_questions'],
            'correct' => $validated['correct'],
            'wrong' => $validated['wrong'],
            'duration_seconds' => $validated['duration_seconds'],
            'created_at' => now(),
        ]);

        return response()->json(['session_token' => $token]);
    }
}

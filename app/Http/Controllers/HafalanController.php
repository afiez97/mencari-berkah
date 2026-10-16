<?php

namespace App\Http\Controllers;

use App\Models\ArabicLetter;
use App\Support\LetterFamilies;
use App\Support\LetterPayload;
use App\Support\StudyPlan;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;

class HafalanController extends Controller
{
    public function home()
    {
        return view('home', ['letters' => LetterPayload::all(), 'legend' => LetterPayload::legend()]);
    }

    /** Sesi Kilat 5 minit — satu butang, semua jenis latihan bercampur. */
    public function kilat()
    {
        return view('kilat.index', [
            'letters' => LetterPayload::all(),
            'lookalikes' => LetterFamilies::LOOKALIKES,
            'soundalikes' => LetterFamilies::SOUNDALIKES,
        ]);
    }

    public function latihan()
    {
        return view('latihan.index');
    }

    /**
     * Peringatan berulang setiap hari (RRULE) pada beberapa waktu — sesuai untuk sesi pendek 5 minit.
     */
    public function reminders(Request $request)
    {
        $validated = $request->validate([
            'masa' => 'required|array|min:1|max:4',
            'masa.*' => ['required', 'regex:/^([01]\d|2[0-3]):[0-5]\d$/'],
            'mula' => 'nullable|date_format:Y-m-d',
        ]);

        $start = $validated['mula'] ?? now()->format('Y-m-d');
        $url = route('kilat');
        $stamp = now()->utc()->format('Ymd\THis\Z');
        $lines = ['BEGIN:VCALENDAR', 'VERSION:2.0', 'PRODID:-//Hafal Huruf Arab//Peringatan//MS', 'CALSCALE:GREGORIAN', 'METHOD:PUBLISH', 'X-WR-CALNAME:Hijaiyah 5 minit'];

        foreach (array_unique($validated['masa']) as $time) {
            $begin = CarbonImmutable::createFromFormat('Y-m-d H:i', $start.' '.$time);
            array_push($lines,
                'BEGIN:VEVENT',
                'UID:hijaiyah-kilat-'.str_replace(':', '', $time).'@'.$request->getHost(),
                'DTSTAMP:'.$stamp,
                'DTSTART:'.$begin->format('Ymd\THis'),
                'DTEND:'.$begin->addMinutes(5)->format('Ymd\THis'),
                'RRULE:FREQ=DAILY;COUNT=60',
                'SUMMARY:'.$this->icsEscape('🕌 Hijaiyah 5 minit'),
                'DESCRIPTION:'.$this->icsEscape("Satu sesi kilat — buka: {$url}"),
                'URL:'.$url,
                'BEGIN:VALARM', 'ACTION:DISPLAY', 'DESCRIPTION:'.$this->icsEscape('Masa untuk 5 minit hijaiyah'), 'TRIGGER:PT0M', 'END:VALARM',
                'END:VEVENT',
            );
        }
        $lines[] = 'END:VCALENDAR';

        return response(implode("\r\n", array_map([$this, 'icsFold'], $lines))."\r\n", 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="peringatan-hijaiyah.ics"',
        ]);
    }

    public function credits()
    {
        return view('kredit.index', [
            'entries' => \App\Support\OpenAudio::entries(),
            'ai' => \App\Support\OpenAudio::ai(),
            'names' => ArabicLetter::pluck('name', 'arabic'),
        ]);
    }

    public function plan()
    {
        $plan = StudyPlan::days(ArabicLetter::all());
        return view('plan.index', compact('plan'));
    }

    /**
     * Fail .ics supaya setiap hari pelan muncul sebagai peringatan dalam Google Calendar / telefon.
     */
    public function planCalendar(Request $request)
    {
        $validated = $request->validate([
            'mula' => 'required|date_format:Y-m-d',
            'masa' => ['required', 'regex:/^([01]\d|2[0-3]):[0-5]\d$/'],
        ]);

        $start = CarbonImmutable::createFromFormat('Y-m-d H:i', $validated['mula'].' '.$validated['masa']);
        $planUrl = route('plan');
        $stamp = now()->utc()->format('Ymd\THis\Z');

        $lines = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//Hafal Huruf Arab//Pelan 21 Hari//MS',
            'CALSCALE:GREGORIAN',
            'METHOD:PUBLISH',
            'X-WR-CALNAME:Hafal Huruf Hijaiyah',
        ];

        foreach (StudyPlan::days(ArabicLetter::all()) as $day) {
            $begin = $start->addDays($day['day'] - 1);
            $summary = 'Hijaiyah Hari '.$day['day'].': '.$day['title']
                .($day['letters'] ? ' ('.implode(' ', $day['letters']).')' : '');
            $description = $day['tip']."\n\n1. Sesi Hafalan\n2. ".$day['quiz_label']."\n\nBuka pelan: ".$planUrl;

            array_push($lines,
                'BEGIN:VEVENT',
                'UID:hijaiyah-'.$start->format('Ymd').'-hari-'.$day['day'].'@'.$request->getHost(),
                'DTSTAMP:'.$stamp,
                'DTSTART:'.$begin->format('Ymd\THis'),
                'DTEND:'.$begin->addMinutes(20)->format('Ymd\THis'),
                'SUMMARY:'.$this->icsEscape($summary),
                'DESCRIPTION:'.$this->icsEscape($description),
                'URL:'.$planUrl,
                'BEGIN:VALARM',
                'ACTION:DISPLAY',
                'DESCRIPTION:'.$this->icsEscape($summary),
                'TRIGGER:-PT5M',
                'END:VALARM',
                'END:VEVENT',
            );
        }
        $lines[] = 'END:VCALENDAR';

        $body = implode("\r\n", array_map([$this, 'icsFold'], $lines))."\r\n";

        return response($body, 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="pelan-hijaiyah-21-hari.ics"',
        ]);
    }

    private function icsEscape(string $text): string
    {
        return str_replace(['\\', ';', ',', "\n"], ['\\\\', '\\;', '\\,', '\\n'], $text);
    }

    /** RFC 5545: baris maksimum 75 oktet, sambungan dimulakan dengan satu ruang. */
    private function icsFold(string $line): string
    {
        $out = '';
        $current = '';
        foreach (mb_str_split($line) as $char) {
            if (strlen($current.$char) > 74) {
                $out .= $current."\r\n ";
                $current = '';
            }
            $current .= $char;
        }
        return $out.$current;
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
        $totalLetters = ArabicLetter::count();
        return view('learn.show', compact('letter', 'prevId', 'nextId', 'totalLetters'));
    }

    public function quiz()
    {
        return view('quiz.index');
    }

    private const MODES = ['name', 'letter', 'audio', 'form'];

    private const FORM_LABELS = ['initial' => 'awal', 'medial' => 'tengah', 'final' => 'akhir'];

    public function quizPlay(Request $request)
    {
        $mode = $request->get('mode', 'mixed');
        $count = $request->get('count', 10);
        $groups = (array) $request->get('groups', []);
        $ids = array_filter((array) $request->get('ids', []), 'is_numeric');

        $all = ArabicLetter::orderBy('order_num')->get();

        $pool = $all;
        if (!empty($ids)) {
            $pool = $pool->whereIn('id', $ids);
        } elseif (!empty($groups)) {
            $pool = $pool->whereIn('group_name', $groups);
        }
        $pool = $pool->shuffle()->values();

        if ($pool->isEmpty()) {
            return view('quiz.play', ['questions' => collect()]);
        }

        if ($count !== 'all') {
            $target = max(1, min(100, (int) $count));
            $repeated = collect();
            while ($repeated->count() < $target) {
                $repeated = $repeated->concat($pool->shuffle());
            }
            $pool = $repeated->take($target);
        }

        $questions = $pool->map(function ($letter) use ($mode, $all) {
            $questionMode = in_array($mode, self::MODES, true) ? $mode : collect(self::MODES)->random();
            // Hamzah tidak bersambung — tiada bentuk lain untuk diuji.
            if ($questionMode === 'form' && !$this->hasJoinedForms($letter)) {
                $questionMode = 'name';
            }
            $wrong = LetterFamilies::similar($letter->arabic, $all, $questionMode === 'audio' ? LetterFamilies::SOUNDALIKES : LetterFamilies::LOOKALIKES)
                ->map(fn ($char) => $all->firstWhere('arabic', $char));

            $base = [
                'id' => $letter->id,
                'mode' => $questionMode,
                'arabic' => $letter->arabic,
                'name' => $letter->name,
                'mnemonic' => $letter->mnemonic,
                'transliteration' => $letter->transliteration,
            ];

            return match ($questionMode) {
                'name' => $base + [
                    'question' => $letter->arabic,
                    'answer' => $letter->name,
                    'options' => $wrong->pluck('name')->push($letter->name)->shuffle()->values(),
                ],
                'letter' => $base + [
                    'question' => $letter->name,
                    'answer' => $letter->arabic,
                    'options' => $wrong->pluck('arabic')->push($letter->arabic)->shuffle()->values(),
                ],
                'audio' => $base + [
                    'question' => $letter->arabic,
                    'answer' => $letter->arabic,
                    'options' => $wrong->pluck('arabic')->push($letter->arabic)->shuffle()->values(),
                ],
                'form' => $base + $this->formQuestion($letter) + [
                    'answer' => $letter->name,
                    'options' => $wrong->pluck('name')->push($letter->name)->shuffle()->values(),
                ],
            };
        });

        return view('quiz.play', ['questions' => $questions->values()]);
    }

    private function joinedForms(ArabicLetter $letter)
    {
        return collect(self::FORM_LABELS)
            ->map(fn ($label, $key) => ['form' => $letter->{'form_'.$key}, 'form_label' => $label])
            ->reject(fn ($f) => $f['form'] === $letter->form_isolated);
    }

    private function hasJoinedForms(ArabicLetter $letter): bool
    {
        return $this->joinedForms($letter)->isNotEmpty();
    }

    /**
     * Pilih satu bentuk bersambung yang betul-betul berbeza daripada bentuk asal.
     */
    private function formQuestion(ArabicLetter $letter): array
    {
        $chosen = $this->joinedForms($letter)->random();

        return ['question' => $chosen['form'], 'form_label' => $chosen['form_label']];
    }

    public function result()
    {
        return view('quiz.result');
    }

    public function stats()
    {
        return view('stats.index');
    }

    public function hafalan()
    {
        $groups = ArabicLetter::all()->groupBy('group_name');
        $totalLetters = ArabicLetter::count();
        return view('hafalan.index', compact('groups', 'totalLetters'));
    }

    public function hafalanSesi(Request $request)
    {
        $group = $request->get('group');
        $query = ArabicLetter::query();
        if ($group && $group !== 'all') {
            $query->where('group_name', $group);
        }
        $cards = $query->orderBy('order_num')->get()->map(fn ($l) => [
            'id'              => $l->id,
            'arabic'          => $l->arabic,
            'name'            => $l->name,
            'transliteration' => $l->transliteration,
            'sound'           => $l->sound,
            'mnemonic'        => $l->mnemonic,
            'visual'          => $l->visual_description,
            'example_word'    => $l->example_word,
            'example_tr'      => $l->example_translation,
            'form_isolated'   => $l->form_isolated,
            // Bentuk bersambung yang berbeza daripada bentuk bebas — untuk uji di muka kad.
            'forms'           => collect([$l->form_initial, $l->form_medial, $l->form_final])
                ->reject(fn ($f) => $f === $l->form_isolated)->unique()->values(),
            'all_forms'       => [$l->form_isolated, $l->form_initial, $l->form_medial, $l->form_final],
        ]);
        $newIds = array_map('intval', array_filter((array) $request->get('baru_ids', []), 'is_numeric'));
        $newLimit = max(0, min(29, (int) $request->get('baru', 5)));
        return view('hafalan.sesi', compact('cards', 'newIds', 'newLimit'));
    }
}

<?php

namespace App\Http\Controllers\Client;

use App\Models\Quiz;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use App\Http\Requests\QuizStoreRequest;
use Illuminate\Support\Facades\DB;
use App\Actions\Quiz\SyncQuestions;


class QuizController extends Controller
{
    public function index(Request $request): Response
    {
        $time = $request->string('time')->toString() ?: 'all'; // ← дефолт: all
        $from = match ($time) {
            '7d'  => now()->subDays(7)->toDateString(),
            '30d' => now()->subDays(30)->toDateString(),
            default => null, // all time => без фільтра дати
        };

        $quizzes = Quiz::query()
            ->where('user_id', $request->user()->id)
            ->latest('id')
            ->select('id','uuid','title','is_active','created_at','description')
            ->paginate(10)
            ->withQueryString();

        $ids = $quizzes->getCollection()->pluck('id')->all();

        $rows = \DB::table('quiz_stats_daily')
            ->selectRaw('quiz_id, SUM(impressions) impressions, SUM(leads) leads')
            ->when($from, fn($q) => $q->where('date','>=',$from)) // якщо $from=null → без where по даті
            ->whereIn('quiz_id', $ids)
            ->groupBy('quiz_id')
            ->get();

        $statsByQuiz = $rows->keyBy('quiz_id')->map(function ($r) {
            $impr = (int)($r->impressions ?? 0);
            $leads = (int)($r->leads ?? 0);
            $cr = $impr ? round($leads / $impr * 100, 1) : 0.0;
            return ['impressions'=>$impr, 'leads'=>$leads, 'conversion'=>$cr];
        });

        $quizzes->getCollection()->transform(function ($q) use ($statsByQuiz) {
            $q->stats = $statsByQuiz->get($q->id) ?? ['impressions'=>0,'leads'=>0,'conversion'=>0.0];
            return $q;
        });

        return Inertia::render('Client/Quiz/Index', [
            'quizzes' => $quizzes,
            'filters' => ['time' => $time], // повертаємо 'all'
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Client/Quiz/Edit', [
            'quiz' => [
                'id' => null,
                'uuid' => null,
                'title' => '',
                'description' => '',
                'domain_allowlist' => [],
                'is_active' => true,
                'settings' => [],
                'questions' => [],
            ],
        ]);
    }

    public function store(QuizStoreRequest $request)
    {
        $data = $request->validated();

        DB::transaction(function () use ($data) {
            Quiz::create([
                'user_id' => auth()->id(),
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'domain_allowlist' => $data['domain_allowlist'] ?? null,
                'is_active' => $data['is_active'] ?? true,
                'settings' => $data['settings'] ?? [],
            ]);
        });

        return redirect()->route('quizzes.index')->with('success', 'Quiz created');
    }

    public function edit(Quiz $quiz): Response
    {
        $quiz->load(['questions.answers']);

        return Inertia::render('Client/Quiz/Edit', ['quiz' => $quiz]);
    }

    public function update(QuizStoreRequest $request, Quiz $quiz, SyncQuestions $action)
    {
        $data = $request->validated();


        DB::transaction(function () use ($quiz, $data, $action) {
            $quiz->update([
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'domain_allowlist' => $data['domain_allowlist'] ?? null,
                'is_active' => $data['is_active'] ?? true,
                'settings' => $data['settings'] ?? [],
            ]);

            $action->execute($quiz, $data['questions'] ?? []);
        });

        return redirect()
            ->route('quizzes.edit', ['quiz' => $quiz])
            ->with('success', 'Quiz created');
    }

    public function destroy(Quiz $quiz)
    {
        $quiz->delete();
        return back()->with('success', 'Quiz deleted');
    }

    public function show(Quiz $quiz): Response
    {
        $quiz->load('questions.answers');

        return Inertia::render('Client/Quiz/Preview', ['quiz' => $quiz]);
    }
}

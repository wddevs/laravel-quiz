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
        $quizzes = Quiz::query()
            ->where('user_id', $request->user()->id)
            ->latest('id')
            ->select('id','uuid','title','is_active','created_at', 'description')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Client/Quiz/Index', [
            'quizzes' => $quizzes,
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

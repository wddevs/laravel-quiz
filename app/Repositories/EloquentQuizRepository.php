<?php

namespace App\Repositories;

use App\Models\Quiz;
use App\Repositories\Contracts\QuizRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class EloquentQuizRepository implements QuizRepositoryInterface
{
    public function getAll(): Collection
    {
        return Quiz::with('questions.answers')->get();
    }

    public function getPaginated(int $perPage = 15): LengthAwarePaginator
    {
        return Quiz::with('questions.answers')->paginate($perPage);
    }

    public function findById(int $id): ?Quiz
    {
        return Quiz::with('questions.answers')->find($id);
    }

    public function create(array $data): Quiz
    {
        return DB::transaction(function () use ($data) {
            $quiz = Quiz::create([
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'is_active' => $data['is_active'] ?? true,
            ]);

            if (isset($data['questions'])) {
                $this->createQuestions($quiz, $data['questions']);
            }

            return $quiz->load('questions.options');
        });
    }

    public function update(Quiz $quiz, array $data): Quiz
    {
        $quiz->update($data);
        return $quiz->fresh();
    }

    public function delete(Quiz $quiz): bool
    {
        return $quiz->delete();
    }

    public function withRelations(Quiz $quiz, array $relations): Quiz
    {
        return $quiz->load($relations);
    }
}

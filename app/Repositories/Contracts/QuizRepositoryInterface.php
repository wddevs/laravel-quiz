<?php

namespace App\Repositories\Contracts;

use App\Models\Quiz;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface QuizRepositoryInterface
{
    public function getAll(): Collection;
    public function getPaginated(int $perPage = 15): LengthAwarePaginator;
    public function findById(int $id): ?Quiz;
    public function create(array $data): Quiz;
    public function update(Quiz $quiz, array $data): Quiz;
    public function delete(Quiz $quiz): bool;
    public function withRelations(Quiz $quiz, array $relations): Quiz;
}

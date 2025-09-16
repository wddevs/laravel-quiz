<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Services\QuizConfig\QuizConfigBuilder;
use Illuminate\Http\Request;

class QuizConfigController extends Controller
{
    public function __construct(private QuizConfigBuilder $builder) {}

    public function show(string $uuid, Request $request)
    {
        $quiz = Quiz::query()
            ->where('uuid',$uuid)
//            ->where('is_active',true)
            ->with(['questions.answers'])
            ->first();

        if (!$quiz) {
            return response()->json([
                'status'  => 'not_found',
                'message' => 'Quiz not found',
            ], 404);
        }

        if (!$quiz->is_active) {
            // повертаємо мінімальні метадані (опційно)
            return response()
                ->json([
                    'status'  => 'inactive',
                    'message' => 'Цей квіз ще не активовано.',
                    'meta'    => [
                        'uuid'        => $quiz->uuid,
                        'title'       => $quiz->title,
                        'description' => $quiz->description,
                    ],
                ], 423) // Locked
                ->header('Cache-Control', 'no-store');
        }

        $etag = $this->builder->etag($quiz);

        if ($request->header('If-None-Match') === $etag) {
            return response('',304)->header('ETag',$etag);
        }

        $payload = $this->builder->build($quiz);

        return response()
            ->json($payload)
            ->header('ETag',$etag)
            ->header('Cache-Control','public, max-age=30');
    }
}

<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Quiz;

class QuizConfigController extends Controller
{
    public function show(string $uuid, Request $request)
    {
        $quiz = Quiz::query()
            ->where('uuid', $uuid)
            ->where('is_active', true)
            ->with(['questions.answers'])
            ->firstOrFail();

        $payload = [
            'uuid' => $quiz->uuid,
            'title' => $quiz->title,
            'description' => $quiz->description,
            'settings' => $quiz->settings ?? [],
            'steps' => $quiz->questions->map(function ($q) {
                return [
                    'id' => $q->id,
                    'order' => $q->order,
                    'title' => $q->title,
                    'type' => $q->type,
                    'required' => (bool) $q->required,
                    'help_text' => $q->help_text,
                    'image' => $q->image_url, // accessor
                    'answers' => $q->answers->map(function ($a) {
                        return [
                            'id' => $a->id,
                            'order' => $a->order,
                            'label' => $a->label,
                            'value' => $a->value,
                            'image' => $a->image_url,
                        ];
                    })->values(),
                ];
            })->values(),
        ];

        return response()->json($payload)
            ->header('Cache-Control', 'public, max-age=30');
    }
}

<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QuizSubmissionController extends Controller
{
    public function store(string $uuid, Request $request): JsonResponse
    {

        return response()->json([
            'success' => true,
//            'id'      => $submission->uuid,
        ], 201);
    }
}

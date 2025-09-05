<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

use Illuminate\Validation\Rule;
use App\Enums\QuestionType;

class QuizStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required','string','max:255'],
            'description' => ['nullable','string','max:2000'],
            'is_active' => ['boolean'],
            'settings' => ['nullable','array'],

            'questions' => ['array'],
            'questions.*.id' => ['nullable','integer'], // для update
            'questions.*.order' => ['required','integer','min:1'],
            'questions.*.title' => ['required','string','max:500'],
            'questions.*.type' => ['required',  Rule::enum(QuestionType::class)],
            'questions.*.required' => ['boolean'],
            'questions.*.help_text' => ['nullable','string','max:500'],
            'questions.*.image_path' => ['nullable','string','max:1024'],

            'questions.*.answers' => ['array'],
            'questions.*.answers.*.id' => ['nullable','integer'],
            'questions.*.answers.*.order' => ['required','integer','min:1'],
            'questions.*.answers.*.label' => ['nullable','string','max:500'],
            'questions.*.answers.*.value' => ['nullable','string','max:255'],
            'questions.*.answers.*.image_path' => ['nullable','string','max:1024'],
        ];
    }
}

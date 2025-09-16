<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreQuizSubmissionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'version' => ['required','string','max:10'],
            'answers' => ['required','array','min:1'],
            'answers.*.q' => ['required','string','max:1000'],
            'answers.*.t' => ['nullable','string','max:50'],
            'answers.*.a' => [''], // рядок або масив
            'contacts' => ['nullable','array'],
            'contacts.name' => ['nullable','string','max:255'],
            'contacts.phone'=> ['nullable','string','max:64'],
            'contacts.email'=> ['nullable','email','max:255'],
            'contacts.text' => ['nullable','string','max:1000'],
            'extra'   => ['nullable','array'],
            'extra.href' => ['nullable','string','max:2048'],
            'extra.lang' => ['nullable','string','max:32'],
            'extra.cookies' => ['nullable','array'],
            'extra.visitor' => ['nullable','string','max:64'],
            'result'  => ['nullable','array'],
        ];
    }
}

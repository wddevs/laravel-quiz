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

            'domain_allowlist'   => ['nullable','array'],
            'domain_allowlist.*' => ['string','max:255', function ($attr, $value, $fail) {
                if (!$this->isValidDomain($value)) {
                    $fail("Invalid domain: {$value}");
                }
            }],

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

    protected function prepareForValidation(): void
    {
        $input = $this->input('domain_allowlist');

        // якщо прийшов рядок — парсимо
        if (is_string($input)) {
            $domains = $this->splitDomains($input);
            $this->merge(['domain_allowlist' => $domains]);
        }
        // якщо прийшов масив — нормалізуємо кожен елемент
        if (is_array($input)) {
            $domains = array_map([$this, 'normalizeDomain'], $input);
            $domains = array_values(array_unique(array_filter($domains)));
            $this->merge(['domain_allowlist' => $domains]);
        }
    }

    private function splitDomains(string $text): array
    {
        // деLimiter: коми, пробіли, переноси рядків, крапка з комою
        $parts = preg_split('/[,\s;]+/u', $text, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $parts = array_map([$this, 'normalizeDomain'], $parts);
        // фільтруємо порожні та унікалізуємо
        return array_values(array_unique(array_filter($parts)));
    }

    private function normalizeDomain(string $value): string
    {
        $value = trim(mb_strtolower($value));

        // Якщо це URL — дістанемо host
        $host = parse_url($value, PHP_URL_HOST);
        if ($host) $value = $host;

        // приберемо протоколи/шляхи, якщо залишилось
        $value = preg_replace('#^https?://#', '', $value);
        $value = preg_replace('#/.*$#', '', $value);

        // прибрати "www." на початку (не обов’язково, але часто зручно)
        $value = preg_replace('/^www\./', '', $value);

        // прибрати зайві крапки/пробіли
        $value = trim($value, " \t\n\r\0\x0B.");

        return $value;
    }

    private function isValidDomain(string $domain): bool
    {
        // дозволяємо wildcard на початку
        if (str_starts_with($domain, '*.')) {
            $domain = substr($domain, 2);
            // не дозволяємо wildcard типу "*." без домену
            if ($domain === '' ) return false;
        }

        // простий чек домену (IDN/punycode — за бажанням впровадити)
        // приклад: example.com, sub.example.co.uk
        return (bool) preg_match('/^(?!-)([a-z0-9-]+\.)+[a-z]{2,}$/i', $domain);
    }
}

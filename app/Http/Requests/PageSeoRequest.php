<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Services\Seo\JsonLdNormalizer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class PageSeoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $limits = config('seo.limits');
        $id = $this->route('seo')?->id;

        return [
            'page_key' => [
                'required', 'string', 'max:255', 'regex:#^/#',
                Rule::unique('seo_meta', 'page_key')->ignore($id),
            ],
            'page_name' => ['required', 'string', 'max:255'],

            'meta_title' => ['nullable', 'string', 'max:' . $limits['title']],
            'meta_description' => ['nullable', 'string', 'max:' . $limits['description']],
            'meta_keywords' => ['nullable', 'string', 'max:255'],
            'canonical_url' => ['nullable', 'url', 'max:255'],
            'robots' => ['nullable', Rule::in([
                'index, follow', 'noindex, follow', 'index, nofollow', 'noindex, nofollow',
            ])],

            'og_title' => ['nullable', 'string', 'max:' . $limits['title']],
            'og_description' => ['nullable', 'string', 'max:' . $limits['description']],
            'og_image' => ['nullable', 'string', 'max:255'],
            'og_type' => ['nullable', 'string', 'max:50'],

            'twitter_title' => ['nullable', 'string', 'max:' . $limits['title']],
            'twitter_description' => ['nullable', 'string', 'max:' . $limits['description']],
            'twitter_image' => ['nullable', 'string', 'max:255'],
            'twitter_card' => ['nullable', Rule::in(['summary', 'summary_large_image'])],

            'schema_markup' => ['nullable', 'string'],
            'faq_schema' => ['nullable', 'string'],

            'faqs' => ['nullable', 'array'],
            'faqs.*.question' => ['nullable', 'string', 'max:' . $limits['faq_question']],
            'faqs.*.answer' => ['nullable', 'string', 'max:' . $limits['faq_answer']],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'page_key.regex' => 'The page key must start with a forward slash, e.g. /catering.',
            'page_key.unique' => 'Another page already uses that key.',
        ];
    }

    protected function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $this->validateJsonLd($validator, 'schema_markup', 'Structured data');
            $this->validateJsonLd($validator, 'faq_schema', 'FAQ JSON-LD');
            $this->validateFaqRows($validator);
        });
    }

    /**
     * Normalisation runs before validation, so a paste that only needed the
     * script wrapper stripped or newlines escaped is accepted, not rejected.
     */
    private function validateJsonLd(Validator $validator, string $field, string $label): void
    {
        $raw = $this->input($field);

        if ($raw === null || trim((string) $raw) === '') {
            return;
        }

        $result = app(JsonLdNormalizer::class)->normalize($raw);

        if (!$result['ok']) {
            $validator->errors()->add($field, $label . ': ' . $result['error']);
        }
    }

    /**
     * A row with only one half filled is a mistake worth reporting; a wholly
     * blank row is just an unused slot and is dropped silently.
     *
     * Errors are keyed by the row index the editor actually sees.
     */
    private function validateFaqRows(Validator $validator): void
    {
        foreach ((array) $this->input('faqs', []) as $index => $row) {
            $question = trim((string) ($row['question'] ?? ''));
            $answer = trim((string) ($row['answer'] ?? ''));

            if ($question === '' && $answer === '') {
                continue;
            }

            if ($question === '') {
                $validator->errors()->add("faqs.{$index}.question", 'FAQ #' . ((int) $index + 1) . ' has an answer but no question.');
            }

            if ($answer === '') {
                $validator->errors()->add("faqs.{$index}.answer", 'FAQ #' . ((int) $index + 1) . ' has a question but no answer.');
            }
        }
    }

    /**
     * Blank FAQ rows never reach the database.
     *
     * @return array<int, array{question: string, answer: string}>
     */
    public function cleanFaqs(): array
    {
        $out = [];

        foreach ((array) $this->input('faqs', []) as $row) {
            $question = trim((string) ($row['question'] ?? ''));
            $answer = trim((string) ($row['answer'] ?? ''));

            if ($question !== '' && $answer !== '') {
                $out[] = ['question' => $question, 'answer' => $answer];
            }
        }

        return $out;
    }
}

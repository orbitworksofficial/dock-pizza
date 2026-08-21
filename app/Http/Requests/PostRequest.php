<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\BlogPost;
use App\Services\Seo\JsonLdNormalizer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class PostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role->canAuthor() ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $limits = config('seo.limits');

        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'content' => ['required', 'string'],
            'featured_image' => ['nullable', 'string', 'max:255'],
            'featured_image_alt' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::in(BlogPost::STATUSES)],
            'published_at' => ['nullable', 'date'],
            'blog_category_id' => ['nullable', 'exists:blog_categories,id'],
            'tags' => ['nullable', 'string', 'max:1000'],
            'is_featured' => ['nullable', 'boolean'],
            'allow_comments' => ['nullable', 'boolean'],

            'seo_title' => ['nullable', 'string', 'max:' . $limits['title']],
            'seo_description' => ['nullable', 'string', 'max:' . $limits['description']],
            'seo_keywords' => ['nullable', 'string', 'max:255'],
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
            'slug.regex' => 'The slug may contain only lowercase letters, numbers and hyphens.',
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
     * Validate before filtering, so error indexes match the rows on screen.
     */
    private function validateFaqRows(Validator $validator): void
    {
        foreach ((array) $this->input('faqs', []) as $index => $row) {
            $q = trim((string) ($row['question'] ?? ''));
            $a = trim((string) ($row['answer'] ?? ''));

            if ($q === '' && $a === '') {
                continue;
            }

            if ($q === '') {
                $validator->errors()->add("faqs.{$index}.question", 'FAQ #' . ((int) $index + 1) . ' has an answer but no question.');
            }

            if ($a === '') {
                $validator->errors()->add("faqs.{$index}.answer", 'FAQ #' . ((int) $index + 1) . ' has a question but no answer.');
            }
        }
    }

    /**
     * @return array<int, array{question: string, answer: string}>
     */
    public function cleanFaqs(): array
    {
        $out = [];

        foreach ((array) $this->input('faqs', []) as $row) {
            $q = trim((string) ($row['question'] ?? ''));
            $a = trim((string) ($row['answer'] ?? ''));

            if ($q !== '' && $a !== '') {
                $out[] = ['question' => $q, 'answer' => $a];
            }
        }

        return $out;
    }
}

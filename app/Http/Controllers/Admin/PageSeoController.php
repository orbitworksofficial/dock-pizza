<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\PageSeoRequest;
use App\Models\Faq;
use App\Models\SeoMeta;
use App\Services\Seo\JsonLdNormalizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PageSeoController extends Controller
{
    public function __construct(private readonly JsonLdNormalizer $jsonLd)
    {
    }

    public function index(): View
    {
        $rows = SeoMeta::forPages()->withCount('faqs')->orderBy('page_key')->get();

        // Routes that have config defaults but no row yet, so the editor can
        // see what is still running on hardcoded values alone.
        $unconfigured = collect(config('seo.pages'))
            ->reject(fn (array $page) => $page['hidden'] ?? false)
            ->keys()
            ->diff($rows->pluck('page_key'))
            ->values();

        return view('admin.seo.index', compact('rows', 'unconfigured'));
    }

    public function create(): View
    {
        return view('admin.seo.form', [
            'seo' => new SeoMeta(['robots' => 'index, follow', 'og_type' => 'website', 'twitter_card' => 'summary_large_image']),
            'faqs' => [],
        ]);
    }

    public function edit(SeoMeta $seo): View
    {
        return view('admin.seo.form', [
            'seo' => $seo,
            'faqs' => $seo->faqs()->get()->map(fn (Faq $f) => [
                'question' => $f->question,
                'answer' => $f->answer,
            ])->all(),
        ]);
    }

    public function store(PageSeoRequest $request): RedirectResponse
    {
        $seo = DB::transaction(function () use ($request) {
            $seo = SeoMeta::create($this->payload($request));
            $this->syncFaqs($seo, $request->cleanFaqs());

            return $seo;
        });

        return redirect()
            ->route('admin.seo.edit', $seo)
            ->with('success', 'SEO settings created for ' . $seo->page_key);
    }

    public function update(PageSeoRequest $request, SeoMeta $seo): RedirectResponse
    {
        DB::transaction(function () use ($request, $seo) {
            $seo->update($this->payload($request));
            $this->syncFaqs($seo, $request->cleanFaqs());
        });

        return redirect()
            ->route('admin.seo.edit', $seo)
            ->with('success', 'SEO settings saved. Changes are live immediately.');
    }

    public function destroy(SeoMeta $seo): RedirectResponse
    {
        $key = $seo->page_key;
        $seo->delete();

        return redirect()
            ->route('admin.seo.index')
            ->with('success', $key . ' reverted to its hardcoded defaults.');
    }

    /**
     * Normalise JSON-LD before storing so what is saved is already clean.
     *
     * @return array<string, mixed>
     */
    private function payload(PageSeoRequest $request): array
    {
        $data = $request->safe()->except(['faqs', 'schema_markup', 'faq_schema']);

        foreach (['schema_markup', 'faq_schema'] as $field) {
            $result = $this->jsonLd->normalize($request->input($field));
            $data[$field] = $result['ok'] && $result['json'] !== '' ? $result['json'] : null;
        }

        return $data;
    }

    /**
     * @param  array<int, array{question: string, answer: string}>  $faqs
     */
    private function syncFaqs(SeoMeta $seo, array $faqs): void
    {
        // Replacing wholesale keeps the editor's row order authoritative and
        // avoids orphan rows when one is removed.
        $seo->faqs()->delete();

        foreach ($faqs as $index => $faq) {
            $seo->faqs()->create([
                'question' => $faq['question'],
                'answer' => $faq['answer'],
                'sort_order' => $index,
                'is_active' => true,
            ]);
        }
    }
}

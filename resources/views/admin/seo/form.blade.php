@extends('admin.layout')
@section('admin_title', $seo->exists ? 'Edit ' . $seo->page_key : 'Add page')

@php
    $limits = config('seo.limits');
    $oldFaqs = old('faqs', $faqs ?: [['question' => '', 'answer' => '']]);
    $input = fn (string $field, $fallback = '') => old($field, $seo->{$field} ?? $fallback);
@endphp

@section('admin_content')
<form method="POST"
      action="{{ $seo->exists ? route('admin.seo.update', $seo) : route('admin.seo.store') }}"
      x-data="seoForm({{ Js::from(['faqs' => array_values($oldFaqs), 'limits' => $limits]) }})">
    @csrf
    @if($seo->exists) @method('PUT') @endif

    <div class="flex items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold tracking-tight">
                {{ $seo->exists ? ($seo->page_name ?: $seo->page_key) : 'Add page' }}
            </h1>
            @if($seo->exists)
                <code class="text-xs text-stone-400">{{ $seo->page_key }}</code>
            @endif
        </div>
        <div class="flex items-center gap-3 flex-shrink-0">
            <a href="{{ route('admin.seo.index') }}" class="text-xs font-bold text-stone-500 hover:text-stone-900">Cancel</a>
            <button type="submit" class="btn-yellow px-5 py-2.5 rounded-xl text-xs font-bold uppercase tracking-wider">
                Save
            </button>
        </div>
    </div>

    {{-- ── Page identity ─────────────────────────────────────── --}}
    <section class="bg-white border border-stone-200 rounded-2xl p-6 mb-5">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <div>
                <label for="page_key" class="admin-label">Page key</label>
                <input type="text" name="page_key" id="page_key" required
                       value="{{ old('page_key', $seo->page_key ?? request('page_key', '')) }}"
                       placeholder="/services" class="admin-input font-mono">
                <p class="admin-hint">The route path. Must start with <code>/</code>.</p>
            </div>
            <div>
                <label for="page_name" class="admin-label">Page name</label>
                <input type="text" name="page_name" id="page_name" required
                       value="{{ $input('page_name') }}" placeholder="Services" class="admin-input">
                <p class="admin-hint">Internal label, shown in this dashboard only.</p>
            </div>
        </div>
    </section>

    {{-- ── Search engine listing ─────────────────────────────── --}}
    <section class="bg-white border border-stone-200 rounded-2xl p-6 mb-5">
        <h2 class="text-sm font-bold uppercase tracking-wider text-stone-500 mb-5">Search engine listing</h2>

        <div class="space-y-5">
            <div>
                <div class="flex items-baseline justify-between gap-3">
                    <label for="meta_title" class="admin-label">Title</label>
                    <span class="text-[11px] font-mono" :class="counterClass(title.length, {{ $limits['title_warn'] }}, {{ $limits['title'] }})"
                          x-text="`${title.length} / {{ $limits['title_warn'] }}`"></span>
                </div>
                <input type="text" name="meta_title" id="meta_title" x-model="title"
                       maxlength="{{ $limits['title'] }}"
                       value="{{ $input('meta_title') }}" class="admin-input">
                <p class="admin-hint">
                    Google truncates past ~{{ $limits['title_warn'] }} characters. Leave blank to use the hardcoded default.
                </p>
            </div>

            <div>
                <div class="flex items-baseline justify-between gap-3">
                    <label for="meta_description" class="admin-label">Meta description</label>
                    <span class="text-[11px] font-mono" :class="counterClass(description.length, {{ $limits['description_warn'] }}, {{ $limits['description'] }})"
                          x-text="`${description.length} / {{ $limits['description_warn'] }}`"></span>
                </div>
                <textarea name="meta_description" id="meta_description" rows="3" x-model="description"
                          maxlength="{{ $limits['description'] }}" class="admin-input">{{ $input('meta_description') }}</textarea>
                <p class="admin-hint">Truncated past ~{{ $limits['description_warn'] }} characters.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label for="meta_keywords" class="admin-label">Keywords</label>
                    <input type="text" name="meta_keywords" id="meta_keywords"
                           value="{{ $input('meta_keywords') }}" class="admin-input">
                </div>
                <div>
                    <label for="robots" class="admin-label">Robots</label>
                    <select name="robots" id="robots" class="admin-input">
                        @foreach(['index, follow', 'noindex, follow', 'index, nofollow', 'noindex, nofollow'] as $option)
                            <option value="{{ $option }}" @selected($input('robots', 'index, follow') === $option)>{{ $option }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label for="canonical_url" class="admin-label">Canonical URL</label>
                <input type="url" name="canonical_url" id="canonical_url"
                       value="{{ $input('canonical_url') }}" placeholder="{{ url('/') }}/services" class="admin-input">
                <p class="admin-hint">Leave blank to use this page's own URL.</p>
            </div>
        </div>
    </section>

    {{-- ── Social sharing (collapsible) ──────────────────────── --}}
    <section class="bg-white border border-stone-200 rounded-2xl mb-5 overflow-hidden" x-data="{ open: false }">
        <button type="button" @click="open = !open"
                class="w-full flex items-center justify-between gap-4 px-6 py-4 text-left">
            <span class="text-sm font-bold uppercase tracking-wider text-stone-500">Social sharing</span>
            <i class="fa-solid fa-chevron-down text-xs text-stone-400 transition-transform" :class="open && 'rotate-180'"></i>
        </button>

        <div x-show="open" x-cloak class="px-6 pb-6 space-y-5 border-t border-stone-100 pt-5">
            <p class="text-xs text-stone-500">Leave any field blank to inherit from the search listing above.</p>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label for="og_title" class="admin-label">OG title</label>
                    <input type="text" name="og_title" id="og_title" value="{{ $input('og_title') }}" class="admin-input">
                </div>
                <div>
                    <label for="og_type" class="admin-label">OG type</label>
                    <input type="text" name="og_type" id="og_type" value="{{ $input('og_type', 'website') }}" class="admin-input">
                </div>
            </div>

            <div>
                <label for="og_description" class="admin-label">OG description</label>
                <textarea name="og_description" id="og_description" rows="2" class="admin-input">{{ $input('og_description') }}</textarea>
            </div>

            <div>
                <label for="og_image" class="admin-label">OG image URL</label>
                <input type="text" name="og_image" id="og_image" value="{{ $input('og_image') }}" class="admin-input">
                <p class="admin-hint">A real image file — not the homepage URL. 1200×630 works best.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 pt-2 border-t border-stone-100">
                <div>
                    <label for="twitter_title" class="admin-label">Twitter title</label>
                    <input type="text" name="twitter_title" id="twitter_title" value="{{ $input('twitter_title') }}" class="admin-input">
                </div>
                <div>
                    <label for="twitter_card" class="admin-label">Twitter card</label>
                    <select name="twitter_card" id="twitter_card" class="admin-input">
                        @foreach(['summary_large_image', 'summary'] as $option)
                            <option value="{{ $option }}" @selected($input('twitter_card', 'summary_large_image') === $option)>{{ $option }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label for="twitter_description" class="admin-label">Twitter description</label>
                <textarea name="twitter_description" id="twitter_description" rows="2" class="admin-input">{{ $input('twitter_description') }}</textarea>
            </div>

            <div>
                <label for="twitter_image" class="admin-label">Twitter image URL</label>
                <input type="text" name="twitter_image" id="twitter_image" value="{{ $input('twitter_image') }}" class="admin-input">
            </div>
        </div>
    </section>

    {{-- ── FAQs ──────────────────────────────────────────────── --}}
    <section class="bg-white border border-stone-200 rounded-2xl p-6 mb-5">
        <h2 class="text-sm font-bold uppercase tracking-wider text-stone-500 mb-2">FAQs</h2>
        <p class="text-xs text-stone-500 mb-5">
            These render visibly on the page <strong>and</strong> generate the FAQPage structured data.
            Google only credits FAQ markup whose answers are visible, so both come from these rows.
            Empty rows are ignored.
        </p>

        <div class="space-y-4">
            <template x-for="(faq, index) in faqs" :key="index">
                <div class="border border-stone-200 rounded-xl p-4 bg-stone-50/50">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-bold text-stone-400" x-text="`FAQ #${index + 1}`"></span>
                        <button type="button" @click="removeFaq(index)"
                                class="text-xs font-bold text-rose-600 hover:text-rose-800"
                                x-show="faqs.length > 1">
                            Remove
                        </button>
                    </div>
                    <input type="text" :name="`faqs[${index}][question]`" x-model="faq.question"
                           placeholder="Question" maxlength="{{ $limits['faq_question'] }}"
                           class="admin-input mb-3">
                    <textarea :name="`faqs[${index}][answer]`" x-model="faq.answer" rows="3"
                              placeholder="Answer" maxlength="{{ $limits['faq_answer'] }}"
                              class="admin-input"></textarea>
                </div>
            </template>
        </div>

        <button type="button" @click="addFaq()"
                class="mt-4 text-xs font-bold text-[#B4530A] hover:underline">
            <i class="fa-solid fa-plus mr-1"></i> Add FAQ
        </button>

        <div class="mt-6 pt-5 border-t border-stone-100">
            <label for="faq_schema" class="admin-label">FAQ JSON-LD (optional override)</label>
            <textarea name="faq_schema" id="faq_schema" rows="5" class="admin-input font-mono text-xs"
                      placeholder='{"@@context":"https://schema.org","@@type":"FAQPage",...}'>{{ $input('faq_schema') }}</textarea>
            <p class="admin-hint">
                Fill this in only to replace the generated FAQ markup. When set, the generated block is
                <strong>not</strong> emitted — a page must never declare its FAQs twice.
                A <code>&lt;script&gt;</code> wrapper and multi-line answers are handled automatically.
            </p>
        </div>
    </section>

    {{-- ── Structured data (collapsible) ─────────────────────── --}}
    <section class="bg-white border border-stone-200 rounded-2xl mb-5 overflow-hidden" x-data="{ open: false }">
        <button type="button" @click="open = !open"
                class="w-full flex items-center justify-between gap-4 px-6 py-4 text-left">
            <span class="text-sm font-bold uppercase tracking-wider text-stone-500">Structured data</span>
            <i class="fa-solid fa-chevron-down text-xs text-stone-400 transition-transform" :class="open && 'rotate-180'"></i>
        </button>

        <div x-show="open" x-cloak class="px-6 pb-6 border-t border-stone-100 pt-5">
            <label for="schema_markup" class="admin-label">Additional JSON-LD</label>
            <textarea name="schema_markup" id="schema_markup" rows="10" class="admin-input font-mono text-xs"
                      placeholder='Paste from any schema generator — with or without the <script> wrapper.'>{{ $input('schema_markup') }}</textarea>
            <p class="admin-hint">
                Added to this page's <code>@@graph</code>. Do <strong>not</strong> include a
                <code>sameAs</code> list here — the site-wide one already covers it, and two
                competing lists confuse Google.
            </p>
        </div>
    </section>

    <div class="flex items-center justify-between gap-4">
        @if($seo->exists)
            <button type="submit" form="delete-seo"
                    class="text-xs font-bold text-rose-600 hover:text-rose-800"
                    onclick="return confirm('Revert {{ $seo->page_key }} to its hardcoded defaults? The page keeps working.')">
                Delete and use defaults
            </button>
        @else
            <span></span>
        @endif

        <button type="submit" class="btn-yellow px-5 py-2.5 rounded-xl text-xs font-bold uppercase tracking-wider">
            Save
        </button>
    </div>
</form>

@if($seo->exists)
    <form id="delete-seo" method="POST" action="{{ route('admin.seo.destroy', $seo) }}" class="hidden">
        @csrf @method('DELETE')
    </form>
@endif

<script>
    function seoForm(config) {
        return {
            faqs: config.faqs.length ? config.faqs : [{ question: '', answer: '' }],
            title: @js($input('meta_title')),
            description: @js($input('meta_description')),

            addFaq() {
                this.faqs.push({ question: '', answer: '' });
            },

            removeFaq(index) {
                this.faqs.splice(index, 1);
                if (!this.faqs.length) this.addFaq();
            },

            // Advisory only — the hard limit is enforced server-side.
            counterClass(length, warn, max) {
                if (length > max - 1) return 'text-rose-600 font-bold';
                if (length > warn) return 'text-amber-600 font-bold';
                return 'text-stone-400';
            },
        };
    }
</script>
@endsection

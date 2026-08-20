@extends('admin.layout')

@section('title', $seo->exists ? 'Edit ' . $seo->page_key : 'Add page')
@section('page_title', $seo->exists ? ($seo->page_name ?: $seo->page_key) : 'Add page')
@section('page_sub', $seo->exists ? $seo->page_key : 'Configure search and social metadata')

@php
    $limits = config('seo.limits');
    $oldFaqs = array_values(old('faqs', $faqs ?: [['question' => '', 'answer' => '']]));
    $val = fn (string $f, $d = '') => old($f, $seo->{$f} ?? $d);

    // Resolve per-row FAQ errors here so the template stays declarative.
    // Indexes match the rows the editor sees, because validation runs before
    // blank rows are filtered out.
    $faqErrors = [];
    foreach ($errors->getMessages() as $key => $messages) {
        if (preg_match('/^faqs\.(\d+)\.(question|answer)$/', $key, $m)) {
            $faqErrors[(int) $m[1]][$m[2]] = $messages[0];
        }
    }
@endphp

@section('page_actions')
    <a href="{{ route('admin.seo.index') }}" class="btn btn--ghost">Cancel</a>
    <button type="submit" form="seo-form" class="btn btn--primary">Save</button>
@endsection

@section('content')
<form id="seo-form" method="POST"
      action="{{ $seo->exists ? route('admin.seo.update', $seo) : route('admin.seo.store') }}"
      x-data="{ faqs: {{ Js::from($oldFaqs) }},
                faqErrors: {{ Js::from($faqErrors) }},
                addFaq() { this.faqs.push({ question: '', answer: '' }) },
                removeFaq(i) { this.faqs.splice(i, 1); this.faqErrors = {}; if (!this.faqs.length) this.addFaq() },
                errFor(i, f) { return (this.faqErrors[i] || {})[f] || '' } }">
    @csrf
    @if($seo->exists) @method('PUT') @endif

    {{-- ── Page identity ───────────────────────────────────── --}}
    <div class="card">
        <div class="card__head">
            <div><div class="card__title">Page</div></div>
        </div>
        <div class="card__body">
            <div class="field-row">
                <x-admin.field name="page_key" label="Page key" required
                               hint="The route path. Must start with <code>/</code>.">
                    <input type="text" name="page_key" id="page-key" class="input mono"
                           value="{{ old('page_key', $seo->page_key ?? request('page_key', '')) }}"
                           placeholder="/services" required>
                </x-admin.field>

                <x-admin.field name="page_name" label="Page name" required
                               hint="Internal label, shown in this dashboard only.">
                    <input type="text" name="page_name" id="page-name" class="input"
                           value="{{ $val('page_name') }}" placeholder="Services" required>
                </x-admin.field>
            </div>
        </div>
    </div>

    {{-- ── Search engine listing ───────────────────────────── --}}
    <div class="card" style="margin-top:14px;">
        <div class="card__head">
            <div>
                <div class="card__title">Search engine listing</div>
                <div class="card__sub">How this page appears in results</div>
            </div>
        </div>
        <div class="card__body">
            <x-admin.field name="meta_title" label="Title"
                           :counter="['warn' => $limits['title_warn'], 'max' => $limits['title']]"
                           :value="$seo->meta_title"
                           hint="Google truncates past ~{{ $limits['title_warn'] }} characters. Leave blank to use the hardcoded default.">
                <input type="text" name="meta_title" id="meta-title" class="input"
                       x-model="value" maxlength="{{ $limits['title'] }}">
            </x-admin.field>

            <x-admin.field name="meta_description" label="Meta description"
                           :counter="['warn' => $limits['description_warn'], 'max' => $limits['description']]"
                           :value="$seo->meta_description"
                           hint="Truncated past ~{{ $limits['description_warn'] }} characters.">
                <textarea name="meta_description" id="meta-description" class="textarea" rows="3"
                          x-model="value" maxlength="{{ $limits['description'] }}"></textarea>
            </x-admin.field>

            <div class="field-row">
                <x-admin.field name="meta_keywords" label="Keywords">
                    <input type="text" name="meta_keywords" id="meta-keywords" class="input"
                           value="{{ $val('meta_keywords') }}">
                </x-admin.field>

                <x-admin.field name="robots" label="Robots">
                    <select name="robots" id="robots" class="select">
                        @foreach(['index, follow', 'noindex, follow', 'index, nofollow', 'noindex, nofollow'] as $o)
                            <option value="{{ $o }}" @selected($val('robots', 'index, follow') === $o)>{{ $o }}</option>
                        @endforeach
                    </select>
                </x-admin.field>
            </div>

            <x-admin.field name="canonical_url" label="Canonical URL"
                           hint="Leave blank to use this page's own URL.">
                <input type="url" name="canonical_url" id="canonical-url" class="input"
                       value="{{ $val('canonical_url') }}" placeholder="{{ url('/') }}/services">
            </x-admin.field>
        </div>
    </div>

    {{-- ── Social sharing ──────────────────────────────────── --}}
    <div class="collapse" style="margin-top:14px;" x-data="{ open: {{ $errors->hasAny(['og_title','og_description','og_image','og_type','twitter_title','twitter_description','twitter_image','twitter_card']) ? 'true' : 'false' }} }">
        <button type="button" class="collapse__trigger" @click="open = !open" :aria-expanded="open">
            <span>Social sharing
                <span class="collapse__meta">— blank fields fall back to the title and description above</span>
            </span>
            <i class="collapse__chevron fa-solid fa-chevron-down"></i>
        </button>

        <div class="collapse__body" x-show="open" x-cloak>
            <div class="field-row">
                <x-admin.field name="og_title" label="OG title" hint="Falls back to the page title.">
                    <input type="text" name="og_title" id="og-title" class="input" value="{{ $val('og_title') }}">
                </x-admin.field>

                <x-admin.field name="og_type" label="OG type">
                    <input type="text" name="og_type" id="og-type" class="input" value="{{ $val('og_type', 'website') }}">
                </x-admin.field>
            </div>

            <x-admin.field name="og_description" label="OG description" hint="Falls back to the meta description.">
                <textarea name="og_description" id="og-description" class="textarea" rows="2">{{ $val('og_description') }}</textarea>
            </x-admin.field>

            <x-admin.field name="og_image" label="OG image URL"
                           hint="A real image file, not the homepage URL. 1200×630 works best.">
                <input type="text" name="og_image" id="og-image" class="input" value="{{ $val('og_image') }}">
            </x-admin.field>

            <hr style="border:none; border-top:1px solid var(--line); margin:18px 0;">

            <div class="field-row">
                <x-admin.field name="twitter_title" label="Twitter title" hint="Falls back to the OG title, then the page title.">
                    <input type="text" name="twitter_title" id="twitter-title" class="input" value="{{ $val('twitter_title') }}">
                </x-admin.field>

                <x-admin.field name="twitter_card" label="Twitter card">
                    <select name="twitter_card" id="twitter-card" class="select">
                        @foreach(['summary_large_image', 'summary'] as $o)
                            <option value="{{ $o }}" @selected($val('twitter_card', 'summary_large_image') === $o)>{{ $o }}</option>
                        @endforeach
                    </select>
                </x-admin.field>
            </div>

            <x-admin.field name="twitter_description" label="Twitter description" hint="Falls back to the OG description.">
                <textarea name="twitter_description" id="twitter-description" class="textarea" rows="2">{{ $val('twitter_description') }}</textarea>
            </x-admin.field>

            <x-admin.field name="twitter_image" label="Twitter image URL" hint="Falls back to the OG image.">
                <input type="text" name="twitter_image" id="twitter-image" class="input" value="{{ $val('twitter_image') }}">
            </x-admin.field>
        </div>
    </div>

    {{-- ── FAQs ────────────────────────────────────────────── --}}
    <div class="card" style="margin-top:14px;">
        <div class="card__head">
            <div>
                <div class="card__title">FAQs</div>
                <div class="card__sub">
                    Rendered visibly on the page <strong>and</strong> used to generate FAQPage structured data.
                    Google only credits markup whose answers are visible. Empty rows are ignored.
                </div>
            </div>
        </div>

        <div class="card__body">
            <template x-for="(faq, i) in faqs" :key="i">
                <div style="border:1px solid var(--line); border-radius:var(--radius); padding:14px; margin-bottom:10px; background:var(--surface-2);">
                    <div class="row-between" style="margin-bottom:10px;">
                        <span class="small muted" style="font-weight:600;" x-text="`FAQ #${i + 1}`"></span>
                        <button type="button" class="btn btn--link" style="color:var(--danger);"
                                @click="removeFaq(i)" x-show="faqs.length > 1">Remove</button>
                    </div>

                    {{-- Errors are keyed by the row index the editor sees --}}
                    <div class="field" style="margin-bottom:10px;" :class="errFor(i, 'question') && 'has-error'">
                        <input type="text" :name="`faqs[${i}][question]`" x-model="faq.question"
                               class="input" placeholder="Question"
                               maxlength="{{ $limits['faq_question'] }}">
                        <div class="field__error" x-show="errFor(i, 'question')" x-cloak>
                            <i class="fa-solid fa-circle-exclamation" style="margin-top:1px;"></i>
                            <span x-text="errFor(i, 'question')"></span>
                        </div>
                    </div>

                    <div class="field" style="margin-bottom:0;" :class="errFor(i, 'answer') && 'has-error'">
                        <textarea :name="`faqs[${i}][answer]`" x-model="faq.answer" class="textarea" rows="3"
                                  placeholder="Answer" maxlength="{{ $limits['faq_answer'] }}"></textarea>
                        <div class="field__error" x-show="errFor(i, 'answer')" x-cloak>
                            <i class="fa-solid fa-circle-exclamation" style="margin-top:1px;"></i>
                            <span x-text="errFor(i, 'answer')"></span>
                        </div>
                    </div>
                </div>
            </template>

            <button type="button" class="btn btn--ghost btn--sm" @click="addFaq()">
                <i class="fa-solid fa-plus" style="font-size:10px;"></i> Add FAQ
            </button>

            <hr style="border:none; border-top:1px solid var(--line); margin:18px 0;">

            <x-admin.field name="faq_schema" label="FAQ JSON-LD override"
                           hint="Fill this in only to <strong>replace</strong> the generated FAQ markup. When set, the generated block is not emitted — a page must never declare its FAQs twice. A <code>&lt;script&gt;</code> wrapper and multi-line answers are handled automatically.">
                <textarea name="faq_schema" id="faq-schema" class="textarea textarea--mono" rows="5"
                          placeholder='{"@@context":"https://schema.org","@@type":"FAQPage", ...}'>{{ $val('faq_schema') }}</textarea>
            </x-admin.field>
        </div>
    </div>

    {{-- ── Structured data ─────────────────────────────────── --}}
    <div class="collapse" style="margin-top:14px;" x-data="{ open: {{ $errors->has('schema_markup') ? 'true' : 'false' }} }">
        <button type="button" class="collapse__trigger" @click="open = !open" :aria-expanded="open">
            <span>Structured data <span class="collapse__meta">— additional JSON-LD for this page</span></span>
            <i class="collapse__chevron fa-solid fa-chevron-down"></i>
        </button>

        <div class="collapse__body" x-show="open" x-cloak>
            <x-admin.field name="schema_markup" label="Additional JSON-LD"
                           hint="Added to this page's <code>@@graph</code>. Do <strong>not</strong> include a <code>sameAs</code> list — the site-wide one already covers it, and two competing lists confuse Google.">
                <textarea name="schema_markup" id="schema-markup" class="textarea textarea--mono" rows="10"
                          placeholder="Paste from any schema generator — with or without the script wrapper.">{{ $val('schema_markup') }}</textarea>
            </x-admin.field>
        </div>
    </div>

    {{-- ── Footer actions ──────────────────────────────────── --}}
    <div class="row-between" style="margin-top:20px;">
        @if($seo->exists)
            <button type="submit" form="seo-delete" class="btn btn--danger-ghost btn--sm"
                    onclick="return confirm('Revert {{ $seo->page_key }} to its hardcoded defaults? The page keeps working.')">
                Delete and use defaults
            </button>
        @else
            <span></span>
        @endif

        <button type="submit" class="btn btn--primary">Save</button>
    </div>
</form>

@if($seo->exists)
    <form id="seo-delete" method="POST" action="{{ route('admin.seo.destroy', $seo) }}" style="display:none;">
        @csrf @method('DELETE')
    </form>
@endif
@endsection

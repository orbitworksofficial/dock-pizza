{{-- Per-post SEO. Same field set and same fallback rules as page SEO. --}}

<div class="collapse" x-data="{ open: {{ $errors->hasAny(['seo_title','seo_description','canonical_url','robots']) ? 'true' : 'false' }} }">
    <button type="button" class="collapse__trigger" @click="open = !open" :aria-expanded="open">
        <span>Search engine listing
            <span class="collapse__meta">— blank fields fall back to the post title and excerpt</span>
        </span>
        <i class="collapse__chevron fa-solid fa-chevron-down"></i>
    </button>

    <div class="collapse__body" x-show="open" x-cloak>
        <x-admin.field name="seo_title" label="SEO title"
                       :counter="['warn' => $limits['title_warn'], 'max' => $limits['title']]"
                       :value="$post->seo_title"
                       hint="Falls back to the post title.">
            <input type="text" name="seo_title" id="seo-title" class="input"
                   x-model="value" maxlength="{{ $limits['title'] }}">
        </x-admin.field>

        <x-admin.field name="seo_description" label="Meta description"
                       :counter="['warn' => $limits['description_warn'], 'max' => $limits['description']]"
                       :value="$post->seo_description"
                       hint="Falls back to the excerpt, then to the opening of the content.">
            <textarea name="seo_description" id="seo-description" class="textarea" rows="3"
                      x-model="value" maxlength="{{ $limits['description'] }}"></textarea>
        </x-admin.field>

        <div class="field-row">
            <x-admin.field name="seo_keywords" label="Keywords" hint="Falls back to this post's tags.">
                <input type="text" name="seo_keywords" id="seo-keywords" class="input" value="{{ $val('seo_keywords') }}">
            </x-admin.field>

            <x-admin.field name="robots" label="Robots"
                           hint="Unpublished posts are always noindex, whatever this says.">
                <select name="robots" id="robots" class="select">
                    @foreach(['index, follow', 'noindex, follow', 'index, nofollow', 'noindex, nofollow'] as $o)
                        <option value="{{ $o }}" @selected($val('robots', 'index, follow') === $o)>{{ $o }}</option>
                    @endforeach
                </select>
            </x-admin.field>
        </div>

        <x-admin.field name="canonical_url" label="Canonical URL" hint="Leave blank to use this post's own URL.">
            <input type="url" name="canonical_url" id="canonical-url" class="input" value="{{ $val('canonical_url') }}">
        </x-admin.field>
    </div>
</div>

<div class="collapse" style="margin-top:12px;"
     x-data="{ open: {{ $errors->hasAny(['og_title','og_description','og_image','og_type','twitter_title','twitter_description','twitter_image','twitter_card']) ? 'true' : 'false' }} }">
    <button type="button" class="collapse__trigger" @click="open = !open" :aria-expanded="open">
        <span>Social sharing <span class="collapse__meta">— blank falls back to the SEO title and description</span></span>
        <i class="collapse__chevron fa-solid fa-chevron-down"></i>
    </button>

    <div class="collapse__body" x-show="open" x-cloak>
        <div class="field-row">
            <x-admin.field name="og_title" label="OG title">
                <input type="text" name="og_title" id="og-title" class="input" value="{{ $val('og_title') }}">
            </x-admin.field>
            <x-admin.field name="og_type" label="OG type" hint="Posts should normally be <code>article</code>.">
                <input type="text" name="og_type" id="og-type" class="input" value="{{ $val('og_type', 'article') }}">
            </x-admin.field>
        </div>

        <x-admin.field name="og_description" label="OG description">
            <textarea name="og_description" id="og-description" class="textarea" rows="2">{{ $val('og_description') }}</textarea>
        </x-admin.field>

        <x-admin.field name="og_image" label="OG image URL" hint="Falls back to the featured image.">
            <input type="text" name="og_image" id="og-image" class="input" value="{{ $val('og_image') }}">
        </x-admin.field>

        <hr style="border:none; border-top:1px solid var(--line); margin:16px 0;">

        <div class="field-row">
            <x-admin.field name="twitter_title" label="Twitter title">
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

        <x-admin.field name="twitter_description" label="Twitter description">
            <textarea name="twitter_description" id="twitter-description" class="textarea" rows="2">{{ $val('twitter_description') }}</textarea>
        </x-admin.field>

        <x-admin.field name="twitter_image" label="Twitter image URL">
            <input type="text" name="twitter_image" id="twitter-image" class="input" value="{{ $val('twitter_image') }}">
        </x-admin.field>
    </div>
</div>

{{-- FAQs --}}
<div class="card" style="margin-top:12px;"
     x-data="{ faqs: {{ Js::from($oldFaqs) }},
               faqErrors: {{ Js::from($faqErrors) }},
               addFaq() { this.faqs.push({ question: '', answer: '' }) },
               removeFaq(i) { this.faqs.splice(i, 1); this.faqErrors = {}; if (!this.faqs.length) this.addFaq() },
               errFor(i, f) { return (this.faqErrors[i] || {})[f] || '' } }">
    <div class="card__head">
        <div>
            <div class="card__title">FAQs</div>
            <div class="card__sub">
                Shown on the post <strong>and</strong> used to generate FAQPage structured data —
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

                <div class="field" style="margin-bottom:10px;" :class="errFor(i, 'question') && 'has-error'">
                    <input type="text" :name="`faqs[${i}][question]`" x-model="faq.question"
                           class="input" placeholder="Question" maxlength="{{ $limits['faq_question'] }}">
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

        <hr style="border:none; border-top:1px solid var(--line); margin:16px 0;">

        <x-admin.field name="faq_schema" label="FAQ JSON-LD override"
                       hint="Fill in only to <strong>replace</strong> the generated FAQ markup. When set, the generated block is not emitted — a page must never declare its FAQs twice.">
            <textarea name="faq_schema" id="faq-schema" class="textarea textarea--mono" rows="4">{{ $val('faq_schema') }}</textarea>
        </x-admin.field>
    </div>
</div>

{{-- Structured data --}}
<div class="collapse" style="margin-top:12px;" x-data="{ open: {{ $errors->has('schema_markup') ? 'true' : 'false' }} }">
    <button type="button" class="collapse__trigger" @click="open = !open" :aria-expanded="open">
        <span>Structured data <span class="collapse__meta">— additional JSON-LD for this post</span></span>
        <i class="collapse__chevron fa-solid fa-chevron-down"></i>
    </button>

    <div class="collapse__body" x-show="open" x-cloak>
        <x-admin.field name="schema_markup" label="Additional JSON-LD"
                       hint="Added alongside the automatic BlogPosting node. A <code>&lt;script&gt;</code> wrapper and multi-line values are handled for you. Do <strong>not</strong> include a <code>sameAs</code> list — the site-wide one already covers it.">
            <textarea name="schema_markup" id="schema-markup" class="textarea textarea--mono" rows="8">{{ $val('schema_markup') }}</textarea>
        </x-admin.field>
    </div>
</div>

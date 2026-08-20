{{--
    Visible FAQ rendering. Google only credits FAQ markup whose answers are
    visible on the page, so these are the same rows that generate the
    FAQPage JSON-LD in the head — one editor writes both.
--}}
@isset($seo)
    @if(!empty($seo['faqs']))
        <section class="max-w-3xl mx-auto px-4 py-12 sm:py-16" aria-labelledby="faq-heading">
            <h2 id="faq-heading" class="text-2xl sm:text-3xl font-bold text-[#1E1E1E] font-serif text-center mb-8">
                Frequently Asked Questions
            </h2>

            <div class="space-y-3" x-data="{ open: null }">
                @foreach($seo['faqs'] as $index => $faq)
                    <div class="bg-white border border-stone-200 rounded-2xl overflow-hidden">
                        <h3>
                            <button type="button"
                                    class="w-full flex items-center justify-between gap-4 px-5 py-4 text-left"
                                    @click="open = open === {{ $index }} ? null : {{ $index }}"
                                    :aria-expanded="open === {{ $index }} ? 'true' : 'false'"
                                    aria-controls="faq-answer-{{ $index }}">
                                <span class="text-sm font-bold text-[#1E1E1E]">{{ $faq['question'] }}</span>
                                <i class="fa-solid fa-chevron-down text-xs text-stone-400 transition-transform flex-shrink-0"
                                   :class="open === {{ $index }} && 'rotate-180'"></i>
                            </button>
                        </h3>
                        <div id="faq-answer-{{ $index }}" x-show="open === {{ $index }}" x-cloak
                             x-transition.duration.200ms>
                            <div class="px-5 pb-4 text-sm text-stone-600 leading-relaxed whitespace-pre-line">{{ $faq['answer'] }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif
@endisset

@extends('layouts.app')

@section('content')
<div class="bg-[#F9F9FB] min-h-screen">
    <article class="max-w-3xl mx-auto px-4 sm:px-6 py-10 sm:py-14">

        {{-- Breadcrumb --}}
        <nav class="text-xs text-stone-400 mb-6" aria-label="Breadcrumb">
            <a href="{{ route('home') }}" class="hover:text-stone-700">Home</a>
            <span class="mx-1.5">/</span>
            <a href="{{ route('blog.index') }}" class="hover:text-stone-700">Blog</a>
            @if($post->category)
                <span class="mx-1.5">/</span>
                <a href="{{ route('blog.index', ['category' => $post->category->slug]) }}" class="hover:text-stone-700">
                    {{ $post->category->name }}
                </a>
            @endif
        </nav>

        <header class="mb-8">
            @if($post->category)
                <a href="{{ route('blog.index', ['category' => $post->category->slug]) }}"
                   class="text-[11px] font-bold uppercase tracking-wider text-[#E07B2D]">
                    {{ $post->category->name }}
                </a>
            @endif

            <h1 class="text-3xl sm:text-4xl font-bold text-[#1E1E1E] font-serif leading-tight mt-2 mb-4">
                {{ $post->title }}
            </h1>

            @if($post->excerpt)
                <p class="text-lg text-stone-500 leading-relaxed">{{ $post->excerpt }}</p>
            @endif

            <div class="flex flex-wrap items-center gap-3 text-xs text-stone-400 mt-5 pt-5 border-t border-stone-200">
                @if($post->author)
                    <span>By <strong class="text-stone-600">{{ $post->author->name }}</strong></span>
                    <span>·</span>
                @endif
                <time datetime="{{ $post->published_at?->toDateString() }}">
                    {{ $post->published_at?->format('F j, Y') }}
                </time>
                <span>·</span>
                <span>{{ $post->reading_minutes }} min read</span>
            </div>
        </header>

        @if($post->featured_image)
            <img src="{{ $post->featured_image }}"
                 alt="{{ $post->featured_image_alt ?: $post->title }}"
                 class="w-full rounded-3xl mb-8" width="768" height="432">
        @endif

        {{-- Content is trusted editor HTML from the rich text editor --}}
        <div class="prose-dock">
            {!! $post->content !!}
        </div>

        @if($post->tags->isNotEmpty())
            <div class="flex flex-wrap gap-2 mt-8 pt-6 border-t border-stone-200">
                @foreach($post->tags as $tag)
                    <a href="{{ route('blog.index', ['tag' => $tag->slug]) }}"
                       class="text-xs bg-white border border-stone-200 rounded-full px-3 py-1.5 text-stone-600 hover:border-[#FDB813] transition-colors">
                        #{{ $tag->name }}
                    </a>
                @endforeach
            </div>
        @endif

        {{-- FAQs: the same rows that generate the FAQPage JSON-LD --}}
        @include('partials.faqs')

        @if($related->isNotEmpty())
            <section class="mt-12 pt-8 border-t border-stone-200">
                <h2 class="text-lg font-bold text-[#1E1E1E] font-serif mb-5">More from the blog</h2>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                    @foreach($related as $r)
                        <a href="{{ route('blog.show', $r->slug) }}"
                           class="block bg-white border border-stone-200 rounded-2xl overflow-hidden hover:shadow-md transition-shadow">
                            @if($r->featured_image)
                                <div class="aspect-[16/10] bg-stone-100">
                                    <img src="{{ $r->featured_image }}" alt="{{ $r->featured_image_alt ?: $r->title }}"
                                         class="w-full h-full object-cover" loading="lazy" width="240" height="150">
                                </div>
                            @endif
                            <div class="p-4">
                                <h3 class="text-sm font-bold text-[#1E1E1E] leading-snug">{{ Str::limit($r->title, 60) }}</h3>
                                <p class="text-[11px] text-stone-400 mt-2">{{ $r->published_at?->format('M j, Y') }}</p>
                            </div>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif
    </article>
</div>
@endsection

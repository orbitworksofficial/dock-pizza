@extends('layouts.app')

@section('content')
<div class="bg-[#F9F9FB] min-h-screen">

    {{-- Header --}}
    <div class="bg-white border-b border-stone-100">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 py-12 sm:py-16 text-center">
            <h1 class="text-3xl sm:text-4xl font-bold text-[#1E1E1E] font-serif">
                {{ $seo['title'] ? Str::before($seo['title'], ' —') : 'Blog' }}
            </h1>
            <p class="text-stone-500 text-sm sm:text-base mt-3 max-w-xl mx-auto">
                {{ $seo['description'] }}
            </p>
        </div>
    </div>

    <div class="max-w-5xl mx-auto px-4 sm:px-6 py-10">

        {{-- Filters --}}
        <form method="GET" class="flex flex-col sm:flex-row gap-3 mb-8">
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Search posts…"
                   class="flex-grow bg-white border border-stone-200 rounded-2xl py-3 px-4 text-sm focus:outline-none focus:border-[#FDB813]">
            <select name="category"
                    class="bg-white border border-stone-200 rounded-2xl py-3 px-4 text-sm focus:outline-none focus:border-[#FDB813]">
                <option value="">All categories</option>
                @foreach($categories as $c)
                    <option value="{{ $c->slug }}" @selected(request('category') === $c->slug)>{{ $c->name }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn-yellow px-6 py-3 rounded-2xl text-xs font-bold uppercase tracking-wider">
                Search
            </button>
        </form>

        @if($posts->isEmpty())
            <div class="bg-white border border-stone-200 rounded-3xl p-14 text-center">
                <i class="fa-solid fa-newspaper text-stone-300 text-3xl mb-3"></i>
                <p class="text-stone-500 text-sm">
                    @if(request()->hasAny(['q', 'category', 'tag']))
                        No posts match that search. <a href="{{ route('blog.index') }}" class="text-[#E07B2D] font-bold">Clear filters</a>
                    @else
                        No posts published yet. Check back soon.
                    @endif
                </p>
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($posts as $post)
                    <article class="bg-white border border-stone-200 rounded-3xl overflow-hidden flex flex-col hover:shadow-lg transition-shadow">
                        <a href="{{ route('blog.show', $post->slug) }}" class="block aspect-[16/10] bg-stone-100 overflow-hidden">
                            @if($post->featured_image)
                                <img src="{{ $post->featured_image }}"
                                     alt="{{ $post->featured_image_alt ?: $post->title }}"
                                     class="w-full h-full object-cover hover:scale-105 transition-transform duration-500"
                                     loading="lazy" width="480" height="300">
                            @else
                                <div class="w-full h-full grid place-items-center text-stone-300">
                                    <i class="fa-solid fa-image text-2xl"></i>
                                </div>
                            @endif
                        </a>

                        <div class="p-5 flex flex-col flex-grow">
                            @if($post->category)
                                <a href="{{ route('blog.index', ['category' => $post->category->slug]) }}"
                                   class="text-[11px] font-bold uppercase tracking-wider text-[#E07B2D] mb-2">
                                    {{ $post->category->name }}
                                </a>
                            @endif

                            <h2 class="font-bold text-[#1E1E1E] leading-snug mb-2">
                                <a href="{{ route('blog.show', $post->slug) }}" class="hover:text-[#E07B2D] transition-colors">
                                    {{ $post->title }}
                                </a>
                            </h2>

                            @if($post->excerpt)
                                <p class="text-sm text-stone-500 leading-relaxed flex-grow">{{ Str::limit($post->excerpt, 110) }}</p>
                            @endif

                            <div class="flex items-center gap-3 text-[11px] text-stone-400 mt-4 pt-4 border-t border-stone-100">
                                <time datetime="{{ $post->published_at?->toDateString() }}">
                                    {{ $post->published_at?->format('M j, Y') }}
                                </time>
                                <span>·</span>
                                <span>{{ $post->reading_minutes }} min read</span>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="mt-10">{{ $posts->links() }}</div>
        @endif
    </div>
</div>
@endsection

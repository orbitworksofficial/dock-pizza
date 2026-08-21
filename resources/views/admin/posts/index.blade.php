@extends('admin.layout')

@section('title', 'Posts')
@section('page_title', 'Posts')
@section('page_sub', $posts->total() . ' ' . Str::plural('post', $posts->total()))
@section('content_class', 'content--wide')

@section('page_actions')
    <a href="{{ route('admin.posts.create') }}" class="btn btn--primary">
        <i class="fa-solid fa-plus" style="font-size:11px;"></i> New post
    </a>
@endsection

@php
    $sortLink = function (string $col) use ($sort, $dir) {
        $next = ($sort === $col && $dir === 'asc') ? 'desc' : 'asc';
        return request()->fullUrlWithQuery(['sort' => $col, 'dir' => $next]);
    };
@endphp

@section('content')

    {{-- Filters --}}
    <form method="GET" class="card" style="margin-bottom:14px;">
        <div class="card__body" style="display:grid; grid-template-columns:repeat(auto-fit,minmax(150px,1fr)); gap:10px; align-items:end;">
            <div class="field" style="margin:0;">
                <label class="field__label" for="f-q">Search</label>
                <input type="search" name="q" id="f-q" class="input" value="{{ request('q') }}" placeholder="Title…">
            </div>

            <div class="field" style="margin:0;">
                <label class="field__label" for="f-status">Status</label>
                <select name="status" id="f-status" class="select">
                    <option value="">All</option>
                    @foreach(\App\Models\BlogPost::STATUSES as $s)
                        <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
            </div>

            <div class="field" style="margin:0;">
                <label class="field__label" for="f-cat">Category</label>
                <select name="category" id="f-cat" class="select">
                    <option value="">All</option>
                    @foreach($categories as $c)
                        <option value="{{ $c->id }}" @selected(request('category') == $c->id)>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>

            @if($authors->isNotEmpty())
                <div class="field" style="margin:0;">
                    <label class="field__label" for="f-author">Author</label>
                    <select name="author" id="f-author" class="select">
                        <option value="">All</option>
                        @foreach($authors as $a)
                            <option value="{{ $a->id }}" @selected(request('author') == $a->id)>{{ $a->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div class="row" style="gap:6px;">
                <button type="submit" class="btn btn--ghost">Filter</button>
                @if(request()->hasAny(['q','status','category','author']))
                    <a href="{{ route('admin.posts.index') }}" class="btn btn--link">Clear</a>
                @endif
            </div>
        </div>
    </form>

    <div class="tablewrap">
        @if($posts->isEmpty())
            <div class="empty">
                <div class="empty__icon"><i class="fa-solid fa-newspaper"></i></div>
                <div class="empty__title">No posts found</div>
                <p class="empty__text">
                    @if(request()->hasAny(['q','status','category','author']))
                        Nothing matches those filters.
                    @else
                        Write your first post to get started.
                    @endif
                </p>
                <a href="{{ route('admin.posts.create') }}" class="btn btn--primary">
                    <i class="fa-solid fa-plus" style="font-size:11px;"></i> New post
                </a>
            </div>
        @else
            <div class="tablescroll">
                <table class="table">
                    <thead>
                        <tr>
                            <th><a href="{{ $sortLink('title') }}" class="table__sort {{ $sort === 'title' ? 'is-active' : '' }}">
                                Title <i class="fa-solid fa-sort"></i></a></th>
                            <th><a href="{{ $sortLink('status') }}" class="table__sort {{ $sort === 'status' ? 'is-active' : '' }}">
                                Status <i class="fa-solid fa-sort"></i></a></th>
                            <th>Category</th>
                            <th>Author</th>
                            <th><a href="{{ $sortLink('published_at') }}" class="table__sort {{ $sort === 'published_at' ? 'is-active' : '' }}">
                                Published <i class="fa-solid fa-sort"></i></a></th>
                            <th><a href="{{ $sortLink('views_count') }}" class="table__sort {{ $sort === 'views_count' ? 'is-active' : '' }}">
                                Views <i class="fa-solid fa-sort"></i></a></th>
                            <th class="table__actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($posts as $post)
                            <tr>
                                <td data-label="Title">
                                    <div class="table__primary">{{ $post->title }}</div>
                                    <div class="table__meta mono">/blog/{{ $post->slug }}</div>
                                </td>
                                <td data-label="Status">
                                    @php
                                        $tone = match($post->status) {
                                            'published' => $post->isLive() ? 'ok' : 'accent',
                                            'draft' => 'warn',
                                            default => 'neutral',
                                        };
                                        $label = $post->status === 'published' && !$post->isLive()
                                            ? 'Scheduled' : ucfirst($post->status);
                                    @endphp
                                    <span class="pill pill--{{ $tone }}"><span class="pill__dot"></span>{{ $label }}</span>
                                </td>
                                <td data-label="Category">{{ $post->category?->name ?? '—' }}</td>
                                <td data-label="Author">{{ $post->author?->name ?? '—' }}</td>
                                <td data-label="Published" class="small muted">
                                    {{ $post->published_at?->format('M j, Y') ?? '—' }}
                                </td>
                                <td data-label="Views" class="small">{{ number_format($post->views_count) }}</td>
                                <td class="table__actions">
                                    <div class="row" style="justify-content:flex-end; gap:5px;">
                                        @if($post->isLive())
                                            <a href="{{ route('blog.show', $post->slug) }}" target="_blank" rel="noopener"
                                               class="btn btn--ghost btn--sm" title="View">
                                                <i class="fa-solid fa-arrow-up-right-from-square" style="font-size:10px;"></i>
                                            </a>
                                        @endif
                                        <a href="{{ route('admin.posts.edit', $post) }}" class="btn btn--ghost btn--sm">Edit</a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    @if($posts->hasPages())
        <div style="margin-top:16px;">{{ $posts->links() }}</div>
    @endif

@endsection

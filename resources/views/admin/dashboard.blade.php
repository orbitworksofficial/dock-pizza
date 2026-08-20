@extends('admin.layout')

@section('title', 'Dashboard')
@section('page_title', 'Dashboard')
@section('page_sub', 'Overview of your content')

@section('page_actions')
    @if(Route::has('admin.posts.create'))
        <a href="{{ route('admin.posts.create') }}" class="btn btn--primary">
            <i class="fa-solid fa-plus" style="font-size:11px;"></i> New post
        </a>
    @endif
@endsection

@section('content')

    {{-- Stats --}}
    <div class="stats">
        <div class="stat">
            <div class="stat__label"><i class="fa-solid fa-newspaper"></i> Total posts</div>
            <div class="stat__value">{{ number_format($stats['posts']) }}</div>
            <div class="stat__foot">{{ $isAdmin ? 'Across all authors' : 'Your posts' }}</div>
        </div>

        <div class="stat">
            <div class="stat__label"><i class="fa-solid fa-circle-check"></i> Published</div>
            <div class="stat__value">{{ number_format($stats['published']) }}</div>
            <div class="stat__foot">Live on the site</div>
        </div>

        <div class="stat">
            <div class="stat__label"><i class="fa-solid fa-pen"></i> Drafts</div>
            <div class="stat__value">{{ number_format($stats['drafts']) }}</div>
            <div class="stat__foot">Not yet published</div>
        </div>

        <div class="stat">
            <div class="stat__label"><i class="fa-solid fa-eye"></i> Total views</div>
            <div class="stat__value">{{ number_format($stats['views']) }}</div>
            <div class="stat__foot">All-time</div>
        </div>

        @if($stats['users'] !== null)
            <div class="stat">
                <div class="stat__label"><i class="fa-solid fa-users"></i> Team</div>
                <div class="stat__value">{{ number_format($stats['users']) }}</div>
                <div class="stat__foot">Admins and authors</div>
            </div>
        @endif
    </div>

    {{-- Quick actions --}}
    <div class="card" style="margin-top:20px;">
        <div class="card__head">
            <div>
                <div class="card__title">Quick actions</div>
                <div class="card__sub">Jump straight to what you need</div>
            </div>
        </div>
        <div class="card__body">
            <div class="row" style="flex-wrap:wrap; gap:8px;">
                @foreach([
                    ['admin.posts.create', 'fa-plus', 'Write a post'],
                    ['admin.media.index', 'fa-image', 'Media library'],
                    ['admin.categories.index', 'fa-folder', 'Categories'],
                    ['admin.seo.index', 'fa-magnifying-glass-chart', 'Page SEO'],
                    ['admin.seo.technical', 'fa-diagram-project', 'Technical SEO'],
                ] as [$route, $icon, $label])
                    @if(Route::has($route))
                        <a href="{{ route($route) }}" class="btn btn--ghost">
                            <i class="fa-solid {{ $icon }}" style="font-size:11px;"></i> {{ $label }}
                        </a>
                    @endif
                @endforeach

                @if($isAdmin && Route::has('admin.users.create'))
                    <a href="{{ route('admin.users.create') }}" class="btn btn--ghost">
                        <i class="fa-solid fa-user-plus" style="font-size:11px;"></i> Add user
                    </a>
                @endif
            </div>
        </div>
    </div>

    {{-- Recent posts --}}
    <div class="tablewrap" style="margin-top:20px;">
        <div class="card__head">
            <div>
                <div class="card__title">Recent posts</div>
                <div class="card__sub">{{ $isAdmin ? 'Most recently updated' : 'Your most recent work' }}</div>
            </div>
            @if(Route::has('admin.posts.index'))
                <a href="{{ route('admin.posts.index') }}" class="btn btn--link">View all</a>
            @endif
        </div>

        @if($recent->isEmpty())
            <div class="empty">
                <div class="empty__icon"><i class="fa-solid fa-newspaper"></i></div>
                <div class="empty__title">No posts yet</div>
                <p class="empty__text">
                    @if(!$hasPosts)
                        The blog tables haven't been migrated yet. Run the CMS migration to get started.
                    @else
                        Posts you write will appear here. Start with your first one.
                    @endif
                </p>
                @if(Route::has('admin.posts.create') && $hasPosts)
                    <a href="{{ route('admin.posts.create') }}" class="btn btn--primary">
                        <i class="fa-solid fa-plus" style="font-size:11px;"></i> Write a post
                    </a>
                @endif
            </div>
        @else
            <div class="tablescroll">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Status</th>
                            @if($isAdmin)<th>Author</th>@endif
                            <th>Updated</th>
                            <th class="table__actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recent as $post)
                            <tr>
                                <td data-label="Title">
                                    <div class="table__primary truncate">{{ $post->title }}</div>
                                    <div class="table__meta mono truncate">/{{ $post->slug }}</div>
                                </td>
                                <td data-label="Status">
                                    @php
                                        $tone = match($post->status) {
                                            'published' => 'ok',
                                            'draft' => 'warn',
                                            'archived' => 'neutral',
                                            default => 'neutral',
                                        };
                                    @endphp
                                    <span class="pill pill--{{ $tone }}">
                                        <span class="pill__dot"></span>{{ ucfirst($post->status) }}
                                    </span>
                                </td>
                                @if($isAdmin)
                                    <td data-label="Author">{{ $post->author?->name ?? '—' }}</td>
                                @endif
                                <td data-label="Updated" class="small muted">
                                    {{ $post->updated_at?->diffForHumans() }}
                                </td>
                                <td class="table__actions">
                                    <a href="{{ route('admin.posts.edit', $post) }}" class="btn btn--ghost btn--sm">Edit</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

@endsection

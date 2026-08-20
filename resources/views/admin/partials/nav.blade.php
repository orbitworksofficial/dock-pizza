@php
    // Role checks here only decide what to *show*. Every route is
    // independently guarded server-side — hiding a link protects nothing.
    $isAdmin = auth()->user()->isAdmin();

    $groups = [
        [
            'label' => null,
            'items' => [
                ['route' => 'admin.dashboard', 'icon' => 'fa-gauge', 'label' => 'Dashboard', 'active' => 'admin.dashboard'],
            ],
        ],
        [
            'label' => 'Content',
            'items' => [
                ['route' => 'admin.posts.index', 'icon' => 'fa-newspaper', 'label' => 'Posts', 'active' => 'admin.posts.*'],
                ['route' => 'admin.categories.index', 'icon' => 'fa-folder', 'label' => 'Categories', 'active' => 'admin.categories.*'],
                ['route' => 'admin.tags.index', 'icon' => 'fa-tag', 'label' => 'Tags', 'active' => 'admin.tags.*'],
                ['route' => 'admin.media.index', 'icon' => 'fa-image', 'label' => 'Media', 'active' => 'admin.media.*'],
            ],
        ],
    ];

    if ($isAdmin) {
        $groups[] = [
            'label' => 'SEO',
            'items' => [
                ['route' => 'admin.seo.index', 'icon' => 'fa-magnifying-glass-chart', 'label' => 'Page SEO', 'active' => 'admin.seo.index|admin.seo.edit|admin.seo.create'],
                ['route' => 'admin.seo.technical', 'icon' => 'fa-diagram-project', 'label' => 'Technical SEO', 'active' => 'admin.seo.technical'],
            ],
        ];

        $groups[] = [
            'label' => 'Administration',
            'items' => [
                ['route' => 'admin.users.index', 'icon' => 'fa-users', 'label' => 'Users', 'active' => 'admin.users.*'],
            ],
        ];
    }
@endphp

@foreach($groups as $group)
    <div class="nav-group">
        @if($group['label'])
            <div class="nav-group__label">{{ $group['label'] }}</div>
        @endif

        @foreach($group['items'] as $item)
            @continue(!Route::has($item['route']))
            @php $isCurrent = request()->routeIs(explode('|', $item['active'])); @endphp
            <a href="{{ route($item['route']) }}" class="nav-item"
               @if($isCurrent) aria-current="page" @endif>
                <i class="nav-item__icon fa-solid {{ $item['icon'] }}"></i>
                <span>{{ $item['label'] }}</span>
                @isset($item['badge'])
                    <span class="nav-item__badge">{{ $item['badge'] }}</span>
                @endisset
            </a>
        @endforeach
    </div>
@endforeach

<div class="nav-group">
    <div class="nav-group__label">Site</div>
    <a href="{{ route('home') }}" class="nav-item" target="_blank" rel="noopener">
        <i class="nav-item__icon fa-solid fa-arrow-up-right-from-square"></i>
        <span>View site</span>
    </a>
</div>

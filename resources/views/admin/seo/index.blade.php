@extends('admin.layout')

@section('title', 'Page SEO')
@section('page_title', 'Page SEO')
@section('page_sub', 'Saved changes are live on the next request')

@section('page_actions')
    <a href="{{ route('admin.seo.create') }}" class="btn btn--primary">
        <i class="fa-solid fa-plus" style="font-size:11px;"></i> Add page
    </a>
@endsection

@section('content')

    <div class="tablewrap">
        @if($rows->isEmpty())
            <div class="empty">
                <div class="empty__icon"><i class="fa-solid fa-magnifying-glass-chart"></i></div>
                <div class="empty__title">No pages configured</div>
                <p class="empty__text">
                    Every page is running on its hardcoded defaults from <code class="mono">config/seo.php</code>.
                    Add a page to override them.
                </p>
                <a href="{{ route('admin.seo.create') }}" class="btn btn--primary">
                    <i class="fa-solid fa-plus" style="font-size:11px;"></i> Add page
                </a>
            </div>
        @else
            <div class="tablescroll">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Page</th>
                            <th>Title</th>
                            <th>Robots</th>
                            <th>FAQs</th>
                            <th class="table__actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rows as $row)
                            <tr>
                                <td data-label="Page">
                                    <div class="table__primary">{{ $row->page_name ?: $row->page_key }}</div>
                                    <div class="table__meta mono">{{ $row->page_key }}</div>
                                </td>
                                <td data-label="Title">
                                    @if(trim((string) $row->meta_title) !== '')
                                        <span class="truncate" style="display:block; max-width:320px;">{{ $row->meta_title }}</span>
                                    @else
                                        <span class="muted small"><em>using default</em></span>
                                    @endif
                                </td>
                                <td data-label="Robots">
                                    @php $robots = $row->robots ?: config('seo.defaults.robots'); @endphp
                                    <span class="pill {{ str_contains($robots, 'noindex') ? 'pill--danger' : 'pill--ok' }}">
                                        <span class="pill__dot"></span>{{ $robots }}
                                    </span>
                                </td>
                                <td data-label="FAQs">
                                    @if($row->faqs_count)
                                        <span class="pill pill--neutral">{{ $row->faqs_count }}</span>
                                    @else
                                        <span class="muted">—</span>
                                    @endif
                                </td>
                                <td class="table__actions">
                                    <a href="{{ route('admin.seo.edit', $row) }}" class="btn btn--ghost btn--sm">Edit</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    @if($unconfigured->isNotEmpty())
        <div class="card" style="margin-top:20px;">
            <div class="card__head">
                <div>
                    <div class="card__title">Running on hardcoded defaults</div>
                    <div class="card__sub">
                        These routes render correctly from config. Add a row only to override them.
                    </div>
                </div>
            </div>
            <div class="card__body">
                <div class="row" style="flex-wrap:wrap; gap:7px;">
                    @foreach($unconfigured as $key)
                        <a href="{{ route('admin.seo.create') }}?page_key={{ urlencode($key) }}"
                           class="btn btn--ghost btn--sm mono">{{ $key }}</a>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

@endsection

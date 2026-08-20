@extends('admin.layout')
@section('admin_title', 'Page SEO')

@section('admin_content')
    <div class="flex items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold tracking-tight">Page SEO</h1>
            <p class="text-sm text-stone-500 mt-1">
                Saved changes are live immediately — no deploy or cache clear needed.
            </p>
        </div>
        <a href="{{ route('admin.seo.create') }}"
           class="btn-yellow px-4 py-2.5 rounded-xl text-xs font-bold uppercase tracking-wider flex-shrink-0">
            <i class="fa-solid fa-plus mr-1.5"></i> Add page
        </a>
    </div>

    @if($rows->isEmpty())
        <div class="bg-white border border-stone-200 rounded-2xl p-10 text-center">
            <p class="text-sm text-stone-500">
                No pages configured yet. Every page is currently using its hardcoded defaults from
                <code class="text-xs bg-stone-100 px-1.5 py-0.5 rounded">config/seo.php</code>.
            </p>
        </div>
    @else
        <div class="bg-white border border-stone-200 rounded-2xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-stone-50 text-xs uppercase tracking-wider text-stone-500">
                        <tr>
                            <th class="text-left font-bold px-5 py-3">Page</th>
                            <th class="text-left font-bold px-5 py-3">Title</th>
                            <th class="text-left font-bold px-5 py-3 whitespace-nowrap">Robots</th>
                            <th class="text-left font-bold px-5 py-3 whitespace-nowrap">FAQs</th>
                            <th class="px-5 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        @foreach($rows as $row)
                            <tr class="hover:bg-stone-50/60">
                                <td class="px-5 py-3.5">
                                    <div class="font-bold">{{ $row->page_name ?: $row->page_key }}</div>
                                    <code class="text-xs text-stone-400">{{ $row->page_key }}</code>
                                </td>
                                <td class="px-5 py-3.5 text-stone-600">
                                    @if(trim((string) $row->meta_title) !== '')
                                        {{ Str::limit($row->meta_title, 55) }}
                                    @else
                                        <span class="text-stone-400 italic text-xs">using default</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    @php $robots = $row->robots ?: config('seo.defaults.robots'); @endphp
                                    <span class="text-xs px-2 py-1 rounded-lg {{ str_contains($robots, 'noindex') ? 'bg-rose-50 text-rose-700' : 'bg-emerald-50 text-emerald-700' }}">
                                        {{ $robots }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 text-stone-600">{{ $row->faqs_count }}</td>
                                <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                    <a href="{{ route('admin.seo.edit', $row) }}"
                                       class="text-xs font-bold text-[#B4530A] hover:underline">Edit</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if($unconfigured->isNotEmpty())
        <div class="mt-6 p-5 bg-white border border-stone-200 rounded-2xl">
            <h2 class="text-sm font-bold mb-1">Running on hardcoded defaults</h2>
            <p class="text-xs text-stone-500 mb-3">
                These routes have fallbacks in config but no database row yet. They render correctly —
                add a row only when you want to override the defaults.
            </p>
            <div class="flex flex-wrap gap-2">
                @foreach($unconfigured as $key)
                    <a href="{{ route('admin.seo.create') }}?page_key={{ urlencode($key) }}"
                       class="text-xs font-mono px-2.5 py-1.5 bg-stone-100 hover:bg-stone-200 rounded-lg transition-colors">
                        {{ $key }}
                    </a>
                @endforeach
            </div>
        </div>
    @endif
@endsection

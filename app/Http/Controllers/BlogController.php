<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\BlogTag;
use App\Services\Seo\PostSeoResolver;
use App\Services\Seo\SchemaGraphBuilder;
use App\Services\Seo\SeoManager;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BlogController extends Controller
{
    public function index(Request $request, SeoManager $seoManager, SchemaGraphBuilder $graph): View
    {
        $posts = BlogPost::published()
            ->with(['author:id,name', 'category:id,name,slug'])
            ->when($request->filled('category'), function ($q) use ($request) {
                $q->whereHas('category', fn ($c) => $c->where('slug', $request->string('category')));
            })
            ->when($request->filled('tag'), function ($q) use ($request) {
                $q->whereHas('tags', fn ($t) => $t->where('slug', $request->string('tag')));
            })
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = '%' . $request->string('q') . '%';
                $q->where(fn ($w) => $w->where('title', 'like', $term)->orWhere('excerpt', 'like', $term));
            })
            ->orderByDesc('published_at')
            ->paginate(9)
            ->withQueryString();

        $seo = $seoManager->forPage('/blog');

        return view('blog.index', [
            'posts' => $posts,
            'categories' => BlogCategory::where('is_active', true)->orderBy('sort_order')->orderBy('name')->get(),
            'seo' => $seo,
            'seoGraph' => $graph->build($seo, $seo['faqs']),
        ]);
    }

    public function show(string $slug, PostSeoResolver $resolver, SchemaGraphBuilder $graph): View
    {
        $post = BlogPost::published()
            ->with(['author:id,name', 'category:id,name,slug', 'tags:id,name,slug', 'faqs'])
            ->where('slug', $slug)
            ->firstOrFail();

        // Cheap, non-blocking view count. Not a metric anyone audits, so an
        // occasional lost increment under concurrency is acceptable.
        BlogPost::whereKey($post->id)->increment('views_count');

        $seo = $resolver->resolve($post);

        // The article node joins the site-wide graph rather than forming a
        // second <script>, so the page declares one graph only.
        $seo['schema_markup'] = array_merge(
            $resolver->articleSchema($post, $seo) ? [$resolver->articleSchema($post, $seo)] : [],
            $seo['schema_markup'] ? (array_is_list($seo['schema_markup']) ? $seo['schema_markup'] : [$seo['schema_markup']]) : []
        );

        $related = BlogPost::published()
            ->where('id', '!=', $post->id)
            ->when($post->blog_category_id, fn ($q) => $q->where('blog_category_id', $post->blog_category_id))
            ->latest('published_at')
            ->limit(3)
            ->get();

        return view('blog.show', [
            'post' => $post,
            'related' => $related,
            'seo' => $seo,
            'seoGraph' => $graph->build($seo, $seo['faqs']),
        ]);
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\PostRequest;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\BlogTag;
use App\Services\Seo\JsonLdNormalizer;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PostController extends Controller
{
    public function __construct(private readonly JsonLdNormalizer $jsonLd)
    {
    }

    public function index(Request $request): View
    {
        $user = $request->user();

        $sort = in_array($request->get('sort'), ['title', 'status', 'published_at', 'views_count'], true)
            ? $request->get('sort')
            : 'updated_at';
        $dir = $request->get('dir') === 'asc' ? 'asc' : 'desc';

        $posts = BlogPost::query()
            // Authors see only their own posts. Enforced in the query, not
            // by hiding rows in the view.
            ->visibleTo($user)
            ->with(['author:id,name', 'category:id,name'])
            ->when($request->filled('q'), function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->string('q') . '%');
            })
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('category'), fn ($q) => $q->where('blog_category_id', $request->integer('category')))
            ->when($request->filled('author') && $user->role->isAdmin(),
                fn ($q) => $q->where('author_id', $request->integer('author')))
            ->orderBy($sort, $dir)
            ->paginate(20)
            ->withQueryString();

        return view('admin.posts.index', [
            'posts' => $posts,
            'categories' => BlogCategory::orderBy('name')->get(),
            'authors' => $user->role->isAdmin()
                ? \App\Models\User::whereIn('role', ['super_admin', 'admin', 'author'])->orderBy('name')->get(['id', 'name'])
                : collect(),
            'sort' => $sort,
            'dir' => $dir,
        ]);
    }

    public function create(): View
    {
        return view('admin.posts.form', [
            'post' => new BlogPost(['status' => 'draft', 'allow_comments' => true, 'og_type' => 'article']),
            'categories' => BlogCategory::orderBy('name')->get(),
            'allTags' => BlogTag::orderBy('name')->get(),
            'postTags' => [],
            'faqs' => [],
        ]);
    }

    public function edit(BlogPost $post): View
    {
        $this->authorizePost($post);

        return view('admin.posts.form', [
            'post' => $post,
            'categories' => BlogCategory::orderBy('name')->get(),
            'allTags' => BlogTag::orderBy('name')->get(),
            'postTags' => $post->tags->pluck('name')->all(),
            'faqs' => $post->faqs->map(fn ($f) => [
                'question' => $f->question,
                'answer' => $f->answer,
            ])->all(),
        ]);
    }

    public function store(PostRequest $request): RedirectResponse
    {
        $post = DB::transaction(function () use ($request) {
            $data = $this->payload($request);
            $data['author_id'] = $request->user()->id;
            $data['slug'] = BlogPost::uniqueSlug(
                $request->filled('slug') ? $request->string('slug')->toString() : $request->string('title')->toString()
            );

            $post = BlogPost::create($data);
            $this->syncRelations($post, $request);

            return $post;
        });

        return redirect()->route('admin.posts.edit', $post)
            ->with('success', 'Post created.');
    }

    public function update(PostRequest $request, BlogPost $post): RedirectResponse
    {
        $this->authorizePost($post);

        DB::transaction(function () use ($request, $post) {
            $data = $this->payload($request);

            // The slug only follows the title while the URL is still private.
            // Once published, or once edited by hand, changing it would break
            // inbound links — so an explicit submitted value is required.
            if ($request->filled('slug')) {
                $data['slug'] = BlogPost::uniqueSlug($request->string('slug')->toString(), $post->id);
            }

            $post->update($data);
            $this->syncRelations($post, $request);
        });

        return redirect()->route('admin.posts.edit', $post)
            ->with('success', 'Post saved.');
    }

    public function destroy(BlogPost $post): RedirectResponse
    {
        $this->authorizePost($post);
        $post->delete();

        return redirect()->route('admin.posts.index')->with('success', 'Post moved to trash.');
    }

    /**
     * Authors may only touch their own posts. This runs on every action
     * because passing the route middleware does not mean the user may act
     * on *this* record.
     */
    private function authorizePost(BlogPost $post): void
    {
        $user = request()->user();

        if (!$user->role->isAdmin() && $post->author_id !== $user->id) {
            abort(403, 'You can only manage your own posts.');
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(PostRequest $request): array
    {
        $data = $request->safe()->except(['tags', 'faqs', 'slug', 'schema_markup', 'faq_schema']);

        foreach (['schema_markup', 'faq_schema'] as $field) {
            $result = $this->jsonLd->normalize($request->input($field));
            $data[$field] = $result['ok'] && $result['json'] !== '' ? $result['json'] : null;
        }

        $data['is_featured'] = $request->boolean('is_featured');
        $data['allow_comments'] = $request->boolean('allow_comments');

        // Publishing without an explicit date means "now".
        if ($data['status'] === 'published' && empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        return $data;
    }

    private function syncRelations(BlogPost $post, PostRequest $request): void
    {
        $tagIds = collect(explode(',', (string) $request->input('tags', '')))
            ->map(fn ($n) => trim($n))
            ->filter()
            ->unique()
            ->map(fn ($name) => BlogTag::fromName($name)->id)
            ->all();

        $post->tags()->sync($tagIds);

        // Replaced wholesale so the editor's row order stays authoritative.
        $post->faqs()->delete();

        foreach ($request->cleanFaqs() as $i => $faq) {
            $post->faqs()->create([
                'question' => $faq['question'],
                'answer' => $faq['answer'],
                'sort_order' => $i,
                'is_active' => true,
            ]);
        }
    }
}

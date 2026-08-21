<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use App\Models\BlogTag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TaxonomyController extends Controller
{
    // ── Categories ───────────────────────────────────────────

    public function categories(): View
    {
        return view('admin.taxonomy.categories', [
            'categories' => BlogCategory::withCount('posts')->orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    public function storeCategory(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        BlogCategory::create($data + ['slug' => BlogCategory::uniqueSlug($data['name'])]);

        return back()->with('success', 'Category created.');
    }

    public function updateCategory(Request $request, BlogCategory $category): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('blog_categories', 'slug')->ignore($category->id)],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $category->update([
            'name' => $data['name'],
            'slug' => $data['slug'] ?: BlogCategory::uniqueSlug($data['name'], $category->id),
            'description' => $data['description'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ]);

        return back()->with('success', 'Category updated.');
    }

    public function destroyCategory(BlogCategory $category): RedirectResponse
    {
        // Posts outlive their category: detach rather than delete, so removing
        // a category never destroys content.
        $count = $category->posts()->count();
        $category->posts()->update(['blog_category_id' => null]);
        $category->delete();

        return back()->with('success', $count
            ? "Category deleted. {$count} post(s) kept and left uncategorised."
            : 'Category deleted.');
    }

    // ── Tags ─────────────────────────────────────────────────

    public function tags(): View
    {
        return view('admin.taxonomy.tags', [
            'tags' => BlogTag::withCount('posts')->orderBy('name')->get(),
        ]);
    }

    public function storeTag(Request $request): RedirectResponse
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:255']]);
        BlogTag::fromName($data['name']);

        return back()->with('success', 'Tag created.');
    }

    public function updateTag(Request $request, BlogTag $tag): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('blog_tags', 'slug')->ignore($tag->id)],
        ]);

        $tag->update([
            'name' => $data['name'],
            'slug' => $data['slug'] ?: Str::slug($data['name']),
        ]);

        return back()->with('success', 'Tag updated.');
    }

    public function destroyTag(BlogTag $tag): RedirectResponse
    {
        // The pivot cascades; the posts themselves are untouched.
        $tag->delete();

        return back()->with('success', 'Tag deleted.');
    }
}

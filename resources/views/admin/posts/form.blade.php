@extends('admin.layout')

@section('title', $post->exists ? 'Edit post' : 'New post')
@section('page_title', $post->exists ? 'Edit post' : 'New post')
@section('page_sub', $post->exists ? '/blog/' . $post->slug : 'Draft')
@section('content_class', 'content--wide')

@php
    $limits = config('seo.limits');
    $val = fn (string $f, $d = '') => old($f, $post->{$f} ?? $d);
    $oldFaqs = array_values(old('faqs', $faqs ?: [['question' => '', 'answer' => '']]));

    $faqErrors = [];
    foreach ($errors->getMessages() as $key => $messages) {
        if (preg_match('/^faqs\.(\d+)\.(question|answer)$/', $key, $m)) {
            $faqErrors[(int) $m[1]][$m[2]] = $messages[0];
        }
    }
@endphp

@section('page_actions')
    @if($post->exists && $post->isLive())
        <a href="{{ route('blog.show', $post->slug) }}" target="_blank" rel="noopener" class="btn btn--ghost">
            View <i class="fa-solid fa-arrow-up-right-from-square" style="font-size:9px;"></i>
        </a>
    @endif
    <a href="{{ route('admin.posts.index') }}" class="btn btn--ghost">Cancel</a>
    <button type="submit" form="post-form" class="btn btn--primary">Save</button>
@endsection

@section('content')
<form id="post-form" method="POST"
      action="{{ $post->exists ? route('admin.posts.update', $post) : route('admin.posts.store') }}"
      x-data="postEditor({{ Js::from([
          'postId' => $post->id,
          'title' => old('title', $post->title ?? ''),
          'slug' => old('slug', $post->slug ?? ''),
          'slugLocked' => $post->exists && $post->slugIsLocked(),
      ]) }})">
    @csrf
    @if($post->exists) @method('PUT') @endif

    {{-- Autosave restore banner --}}
    <div class="banner banner--accent" x-show="draftFound" x-cloak style="margin-bottom:14px;">
        <i class="fa-solid fa-clock-rotate-left" style="margin-top:2px;"></i>
        <div class="banner__body">
            <strong>Unsaved changes found.</strong>
            <div class="small" style="opacity:.8;">Autosaved <span x-text="draftAt"></span>.</div>
        </div>
        <div class="banner__actions">
            <button type="button" class="btn btn--primary btn--sm" @click="restoreDraft()">Restore draft</button>
            <button type="button" class="btn btn--ghost btn--sm" @click="clearDraft()">Discard</button>
        </div>
    </div>

    <div style="display:grid; grid-template-columns:minmax(0,1fr) 320px; gap:16px; align-items:start;">

        {{-- ── Main column ─────────────────────────────────── --}}
        <div>
            <div class="card" style="margin-bottom:14px;">
                <div class="card__body">
                    <x-admin.field name="title" label="Title" required>
                        <input type="text" name="title" id="title" class="input"
                               x-model="title" @input="onTitleInput()"
                               style="font-size:16px; font-weight:600;"
                               value="{{ $val('title') }}" required>
                    </x-admin.field>

                    <x-admin.field name="slug" label="Slug"
                                   hint="The public URL. Once the post is published this stops following the title, because changing a live URL breaks inbound links.">
                        <div class="row" style="gap:6px;">
                            <span class="mono muted small" style="flex-shrink:0;">/blog/</span>
                            <input type="text" name="slug" id="slug" class="input mono"
                                   x-model="slug" @input="onSlugInput()" value="{{ $val('slug') }}">
                            <button type="button" class="btn btn--ghost btn--sm" x-show="slugLocked"
                                    @click="slugLocked = false; slug = slugify(title)"
                                    title="Resume following the title">
                                <i class="fa-solid fa-link-slash" style="font-size:10px;"></i>
                            </button>
                        </div>
                    </x-admin.field>

                    <x-admin.field name="excerpt" label="Excerpt"
                                   hint="Shown on the blog index and used as the meta description when the SEO field is blank.">
                        <textarea name="excerpt" id="excerpt" class="textarea" rows="2"
                                  maxlength="500">{{ $val('excerpt') }}</textarea>
                    </x-admin.field>
                </div>
            </div>

            <div class="card" style="margin-bottom:14px;">
                <div class="card__head">
                    <div class="card__title">Content</div>
                </div>
                <div class="card__body">
                    @include('admin.posts.partials.editor')
                </div>
            </div>

            {{-- SEO --}}
            @include('admin.posts.partials.seo-fields')
        </div>

        {{-- ── Sidebar ─────────────────────────────────────── --}}
        <div style="position:sticky; top:calc(var(--header-h) + 16px);">

            <div class="card" style="margin-bottom:14px;">
                <div class="card__head"><div class="card__title">Publish</div></div>
                <div class="card__body">
                    <x-admin.field name="status" label="Status">
                        <select name="status" id="status" class="select">
                            @foreach(\App\Models\BlogPost::STATUSES as $s)
                                <option value="{{ $s }}" @selected($val('status', 'draft') === $s)>{{ ucfirst($s) }}</option>
                            @endforeach
                        </select>
                    </x-admin.field>

                    <x-admin.field name="published_at" label="Publish date"
                                   hint="Leave blank to publish immediately. A future date schedules the post.">
                        <input type="datetime-local" name="published_at" id="published-at" class="input"
                               value="{{ old('published_at', $post->published_at?->format('Y-m-d\TH:i')) }}">
                    </x-admin.field>

                    <label class="checkline" style="margin-bottom:10px;">
                        <input type="checkbox" name="is_featured" value="1" @checked($val('is_featured'))>
                        <span>
                            <span class="checkline__text">Featured post</span>
                        </span>
                    </label>

                    <label class="checkline">
                        <input type="checkbox" name="allow_comments" value="1" @checked($val('allow_comments', true))>
                        <span>
                            <span class="checkline__text">Allow comments</span>
                        </span>
                    </label>

                    @if($post->exists)
                        <div class="small muted" style="margin-top:14px; padding-top:12px; border-top:1px solid var(--line);">
                            <div>Reading time: <strong>{{ $post->reading_minutes }} min</strong> <span style="opacity:.7;">(from word count)</span></div>
                            <div style="margin-top:3px;">Views: <strong>{{ number_format($post->views_count) }}</strong></div>
                        </div>
                    @endif
                </div>
            </div>

            <div class="card" style="margin-bottom:14px;">
                <div class="card__head"><div class="card__title">Organisation</div></div>
                <div class="card__body">
                    <x-admin.field name="blog_category_id" label="Category">
                        <select name="blog_category_id" id="blog-category-id" class="select">
                            <option value="">— None —</option>
                            @foreach($categories as $c)
                                <option value="{{ $c->id }}" @selected($val('blog_category_id') == $c->id)>{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </x-admin.field>

                    <x-admin.field name="tags" label="Tags"
                                   hint="Type and press Enter. New tags are created automatically.">
                        <div x-data="{
                                tags: {{ Js::from(old('tags') ? array_filter(array_map('trim', explode(',', old('tags')))) : $postTags) }},
                                input: '',
                                add() {
                                    const v = this.input.trim().replace(/,$/, '');
                                    if (v && !this.tags.includes(v)) this.tags.push(v);
                                    this.input = '';
                                },
                                remove(i) { this.tags.splice(i, 1); }
                             }">
                            <div class="taginput" @click="$refs.tagIn.focus()">
                                <template x-for="(t, i) in tags" :key="i">
                                    <span class="tagchip">
                                        <span x-text="t"></span>
                                        <button type="button" @click.stop="remove(i)" aria-label="Remove tag">
                                            <i class="fa-solid fa-xmark"></i>
                                        </button>
                                    </span>
                                </template>
                                <input type="text" x-ref="tagIn" x-model="input"
                                       @keydown.enter.prevent="add()"
                                       @keydown.comma.prevent="add()"
                                       @blur="add()"
                                       placeholder="Add a tag…">
                            </div>
                            <input type="hidden" name="tags" :value="tags.join(',')">
                        </div>
                    </x-admin.field>
                </div>
            </div>

            <div class="card" style="margin-bottom:14px;">
                <div class="card__head"><div class="card__title">Featured image</div></div>
                <div class="card__body" x-data="{ url: @js($val('featured_image')) }">
                    <template x-if="url">
                        <div style="margin-bottom:10px;">
                            <img :src="url" alt="" style="width:100%; border-radius:var(--radius); border:1px solid var(--line);">
                        </div>
                    </template>

                    <x-admin.field name="featured_image" label="Image URL">
                        <input type="text" name="featured_image" id="featured-image" class="input"
                               x-model="url" placeholder="/images/post.webp">
                    </x-admin.field>

                    <x-admin.field name="featured_image_alt" label="Alt text"
                                   hint="Describes the image for screen readers and search engines.">
                        <input type="text" name="featured_image_alt" id="featured-image-alt" class="input"
                               value="{{ $val('featured_image_alt') }}">
                    </x-admin.field>
                </div>
            </div>

            @if($post->exists)
                <button type="submit" form="post-delete" class="btn btn--danger-ghost btn--sm btn--block"
                        onclick="return confirm('Move this post to trash?')">
                    Move to trash
                </button>
            @endif
        </div>
    </div>
</form>

@if($post->exists)
    <form id="post-delete" method="POST" action="{{ route('admin.posts.destroy', $post) }}" style="display:none;">
        @csrf @method('DELETE')
    </form>
@endif
@endsection

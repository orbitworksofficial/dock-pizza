@extends('admin.layout')

@section('title', 'Tags')
@section('page_title', 'Tags')
@section('page_sub', $tags->count() . ' ' . Str::plural('tag', $tags->count()))

@section('content')
<div style="display:grid; grid-template-columns:minmax(0,1fr) 300px; gap:16px; align-items:start;">

    <div class="tablewrap">
        @if($tags->isEmpty())
            <div class="empty">
                <div class="empty__icon"><i class="fa-solid fa-tag"></i></div>
                <div class="empty__title">No tags yet</div>
                <p class="empty__text">Tags are also created automatically as you type them while writing a post.</p>
            </div>
        @else
            <div class="tablescroll">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Slug</th>
                            <th>Posts</th>
                            <th class="table__actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tags as $t)
                            <tr x-data="{ editing: false }">
                                <td data-label="Name">
                                    <div x-show="!editing" class="table__primary">{{ $t->name }}</div>
                                    <form x-show="editing" x-cloak method="POST"
                                          action="{{ route('admin.tags.update', $t) }}" id="tag-{{ $t->id }}">
                                        @csrf @method('PUT')
                                        <input type="text" name="name" class="input" value="{{ $t->name }}" required>
                                    </form>
                                </td>
                                <td data-label="Slug">
                                    <span x-show="!editing" class="mono small muted">{{ $t->slug }}</span>
                                    <input x-show="editing" x-cloak type="text" name="slug" form="tag-{{ $t->id }}"
                                           class="input mono" value="{{ $t->slug }}">
                                </td>
                                <td data-label="Posts">
                                    <span class="pill pill--neutral">{{ $t->posts_count }}</span>
                                </td>
                                <td class="table__actions">
                                    <div class="row" style="justify-content:flex-end; gap:5px;">
                                        <template x-if="!editing">
                                            <button type="button" class="btn btn--ghost btn--sm" @click="editing = true">Edit</button>
                                        </template>
                                        <template x-if="editing">
                                            <span class="row" style="gap:5px;">
                                                <button type="submit" form="tag-{{ $t->id }}" class="btn btn--primary btn--sm">Save</button>
                                                <button type="button" class="btn btn--ghost btn--sm" @click="editing = false">Cancel</button>
                                            </span>
                                        </template>
                                        <button type="submit" form="tag-del-{{ $t->id }}" class="btn btn--danger-ghost btn--sm"
                                                onclick="return confirm('Delete “{{ $t->name }}”? Posts keep their content; only the tag is removed.')">
                                            <i class="fa-solid fa-trash" style="font-size:10px;"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <div class="card">
        <div class="card__head"><div class="card__title">Add tag</div></div>
        <form method="POST" action="{{ route('admin.tags.store') }}">
            @csrf
            <div class="card__body">
                <x-admin.field name="name" label="Name" required hint="The slug is generated automatically.">
                    <input type="text" name="name" id="name" class="input" value="{{ old('name') }}" required>
                </x-admin.field>
            </div>
            <div class="card__foot">
                <button type="submit" class="btn btn--primary btn--sm">Add tag</button>
            </div>
        </form>
    </div>
</div>

@foreach($tags as $t)
    <form id="tag-del-{{ $t->id }}" method="POST" action="{{ route('admin.tags.destroy', $t) }}" style="display:none;">
        @csrf @method('DELETE')
    </form>
@endforeach
@endsection

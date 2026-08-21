@extends('admin.layout')

@section('title', 'Categories')
@section('page_title', 'Categories')
@section('page_sub', $categories->count() . ' ' . Str::plural('category', $categories->count()))

@section('content')
<div style="display:grid; grid-template-columns:minmax(0,1fr) 300px; gap:16px; align-items:start;">

    <div class="tablewrap">
        @if($categories->isEmpty())
            <div class="empty">
                <div class="empty__icon"><i class="fa-solid fa-folder"></i></div>
                <div class="empty__title">No categories yet</div>
                <p class="empty__text">Add one using the form beside this list.</p>
            </div>
        @else
            <div class="tablescroll">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Slug</th>
                            <th>Posts</th>
                            <th>Status</th>
                            <th class="table__actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($categories as $c)
                            <tr x-data="{ editing: false }">
                                <td data-label="Name">
                                    <div x-show="!editing" class="table__primary">{{ $c->name }}</div>
                                    <form x-show="editing" x-cloak method="POST"
                                          action="{{ route('admin.categories.update', $c) }}" id="cat-{{ $c->id }}">
                                        @csrf @method('PUT')
                                        <input type="text" name="name" class="input" value="{{ $c->name }}" required>
                                    </form>
                                </td>
                                <td data-label="Slug">
                                    <span x-show="!editing" class="mono small muted">{{ $c->slug }}</span>
                                    <input x-show="editing" x-cloak type="text" name="slug" form="cat-{{ $c->id }}"
                                           class="input mono" value="{{ $c->slug }}">
                                </td>
                                <td data-label="Posts">
                                    <span class="pill pill--neutral">{{ $c->posts_count }}</span>
                                </td>
                                <td data-label="Status">
                                    <span x-show="!editing" class="pill {{ $c->is_active ? 'pill--ok' : 'pill--neutral' }}">
                                        <span class="pill__dot"></span>{{ $c->is_active ? 'Active' : 'Hidden' }}
                                    </span>
                                    <label x-show="editing" x-cloak class="checkline">
                                        <input type="checkbox" name="is_active" value="1" form="cat-{{ $c->id }}" @checked($c->is_active)>
                                        <span class="checkline__text">Active</span>
                                    </label>
                                </td>
                                <td class="table__actions">
                                    <div class="row" style="justify-content:flex-end; gap:5px;">
                                        <template x-if="!editing">
                                            <button type="button" class="btn btn--ghost btn--sm" @click="editing = true">Edit</button>
                                        </template>
                                        <template x-if="editing">
                                            <span class="row" style="gap:5px;">
                                                <button type="submit" form="cat-{{ $c->id }}" class="btn btn--primary btn--sm">Save</button>
                                                <button type="button" class="btn btn--ghost btn--sm" @click="editing = false">Cancel</button>
                                            </span>
                                        </template>
                                        <button type="submit" form="cat-del-{{ $c->id }}" class="btn btn--danger-ghost btn--sm"
                                                onclick="return confirm('Delete “{{ $c->name }}”? Its {{ $c->posts_count }} post(s) will be kept and left uncategorised.')">
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
        <div class="card__head"><div class="card__title">Add category</div></div>
        <form method="POST" action="{{ route('admin.categories.store') }}">
            @csrf
            <div class="card__body">
                <x-admin.field name="name" label="Name" required>
                    <input type="text" name="name" id="name" class="input" value="{{ old('name') }}" required>
                </x-admin.field>

                <x-admin.field name="description" label="Description" hint="Optional. The slug is generated automatically.">
                    <textarea name="description" id="description" class="textarea" rows="2">{{ old('description') }}</textarea>
                </x-admin.field>
            </div>
            <div class="card__foot">
                <button type="submit" class="btn btn--primary btn--sm">Add category</button>
            </div>
        </form>
    </div>
</div>

@foreach($categories as $c)
    <form id="cat-del-{{ $c->id }}" method="POST" action="{{ route('admin.categories.destroy', $c) }}" style="display:none;">
        @csrf @method('DELETE')
    </form>
@endforeach
@endsection

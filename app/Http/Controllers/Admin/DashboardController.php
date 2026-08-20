<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        $isAdmin = $user->role->isAdmin();

        // The blog tables arrive with the CMS migration; until then the
        // dashboard still renders rather than throwing.
        $hasPosts = Schema::hasTable('blog_posts');

        $stats = [
            'posts' => 0,
            'published' => 0,
            'drafts' => 0,
            'views' => 0,
            'users' => $isAdmin ? User::whereIn('role', ['super_admin', 'admin', 'author'])->count() : null,
        ];

        $recent = collect();

        if ($hasPosts && class_exists(\App\Models\BlogPost::class)) {
            $base = \App\Models\BlogPost::query();

            // Authors only ever see their own work, enforced here rather than
            // by hiding it in the view.
            if (!$isAdmin) {
                $base->where('author_id', $user->id);
            }

            $stats['posts'] = (clone $base)->count();
            $stats['published'] = (clone $base)->where('status', 'published')->count();
            $stats['drafts'] = (clone $base)->where('status', 'draft')->count();
            $stats['views'] = (int) (clone $base)->sum('views_count');

            $recent = (clone $base)
                ->with('author:id,name')
                ->latest('updated_at')
                ->limit(8)
                ->get();
        }

        return view('admin.dashboard', compact('stats', 'recent', 'isAdmin', 'hasPosts'));
    }
}

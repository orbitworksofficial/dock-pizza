<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\CateringPackage;
use App\Models\CateringRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CateringController extends Controller
{
    public function index(): View
    {
        $packages = CateringPackage::where('is_active', true)->orderBy('sort_order')->get();
        return view('catering', compact('packages'));
    }

    public function submit(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'event_date' => ['required', 'date', 'after_or_equal:today'],
            'guest_count' => ['required', 'integer', 'min:5'],
            'special_requests' => ['nullable', 'string'],
        ]);

        CateringRequest::create([
            'user_id' => auth()->id(),
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'company' => $request->company,
            'event_date' => $request->event_date,
            'guest_count' => (int) $request->guest_count,
            'special_requests' => $request->special_requests,
            'status' => 'pending',
        ]);

        return back()->with('success', 'Your catering request has been submitted successfully! We will contact you shortly.');
    }
}

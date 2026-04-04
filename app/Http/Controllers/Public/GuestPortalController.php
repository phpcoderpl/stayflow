<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Guest;
use Inertia\Inertia;

class GuestPortalController extends Controller
{
    public function show(string $token)
    {
        $guest = Guest::where('access_token', $token)
            ->firstOrFail();

        $guest->load(['bookings' => fn($q) => $q->with('property')->orderByDesc('check_in')]);

        return Inertia::render('Public/GuestPortal', [
            'guest' => $guest,
        ]);
    }
}

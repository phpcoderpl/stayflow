<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Guest;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GuestController extends Controller
{
    public function index(Request $request)
    {
        $guests = Guest::orderByDesc('updated_at')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Guests/Index', [
            'guests' => $guests,
        ]);
    }

    public function show(Guest $guest)
    {
        $guest->load(['bookings' => fn($q) => $q->with('property')->orderByDesc('check_in')]);

        return Inertia::render('Guests/Show', [
            'guest' => $guest,
        ]);
    }
}

<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Guest;
use App\Models\Property;
use App\Models\BlockedDate;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $query = Booking::with(['property', 'guest'])
            ->orderByDesc('created_at');

        if ($request->status) {
            $query->where('status', $request->status);
        }
        if ($request->property_id) {
            $query->where('property_id', $request->property_id);
        }

        return Inertia::render('Bookings/Index', [
            'bookings' => $query->paginate(20)->withQueryString(),
            'properties' => Property::orderBy('name')->get(['id', 'name']),
            'filters' => $request->only(['status', 'property_id']),
        ]);
    }

    public function calendar(Request $request)
    {
        $propertyId = $request->property_id;
        $properties = Property::orderBy('name')->get(['id', 'name']);

        $bookings = Booking::with('guest')
            ->when($propertyId, fn($q) => $q->where('property_id', $propertyId))
            ->whereIn('status', ['pending', 'confirmed', 'checked_in'])
            ->get();

        $blockedDates = BlockedDate::when($propertyId, fn($q) => $q->where('property_id', $propertyId))->get();

        return Inertia::render('Bookings/Calendar', [
            'bookings' => $bookings,
            'blockedDates' => $blockedDates,
            'properties' => $properties,
            'selectedPropertyId' => $propertyId,
        ]);
    }

    public function create()
    {
        return Inertia::render('Bookings/Create', [
            'properties' => Property::where('is_published', true)->orderBy('name')->get(['id', 'name', 'base_price_per_night', 'cleaning_fee']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'property_id' => ['required', 'exists:properties,id'],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email'],
            'phone' => ['nullable', 'string', 'max:30'],
            'check_in' => ['required', 'date', 'after_or_equal:today'],
            'check_out' => ['required', 'date', 'after:check_in'],
            'guests_count' => ['required', 'integer', 'min:1'],
            'special_requests' => ['nullable', 'string'],
            'admin_notes' => ['nullable', 'string'],
            'status' => ['required', 'string', 'in:pending,confirmed'],
        ]);

        // Create or find guest
        $guest = Guest::firstOrCreate(
            ['email' => $validated['email']],
            [
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'phone' => $validated['phone'],
            ]
        );

        $property = Property::findOrFail($validated['property_id']);
        $checkIn = \Carbon\Carbon::parse($validated['check_in']);
        $checkOut = \Carbon\Carbon::parse($validated['check_out']);
        $nights = $checkIn->diffInDays($checkOut);

        $baseTotal = $property->base_price_per_night * $nights;
        $totalPrice = $baseTotal + $property->cleaning_fee;

        $booking = Booking::create([
            'property_id' => $property->id,
            'guest_id' => $guest->id,
            'check_in' => $validated['check_in'],
            'check_out' => $validated['check_out'],
            'nights' => $nights,
            'guests_count' => $validated['guests_count'],
            'status' => $validated['status'],
            'source' => 'admin',
            'base_total' => $baseTotal,
            'cleaning_fee' => $property->cleaning_fee,
            'total_price' => $totalPrice,
            'deposit_amount' => (int) round($totalPrice * (int) \App\Models\Setting::get('deposit_percent', 30) / 100),
            'special_requests' => $validated['special_requests'],
            'admin_notes' => $validated['admin_notes'],
        ]);

        $guest->increment('booking_count');

        return redirect('/admin/bookings')->with('success', 'Rezerwacja utworzona: ' . $booking->confirmation_code);
    }

    public function show(Booking $booking)
    {
        $booking->load(['property', 'guest', 'payments']);

        return Inertia::render('Bookings/Show', [
            'booking' => $booking,
        ]);
    }

    public function updateStatus(Request $request, Booking $booking)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:pending,confirmed,checked_in,checked_out,cancelled,no_show'],
            'cancellation_reason' => ['nullable', 'string'],
        ]);

        $booking->update([
            'status' => $validated['status'],
            'cancelled_at' => $validated['status'] === 'cancelled' ? now() : $booking->cancelled_at,
            'cancellation_reason' => $validated['cancellation_reason'] ?? $booking->cancellation_reason,
        ]);

        return back()->with('success', 'Status zaktualizowany.');
    }
}

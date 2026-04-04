<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Guest;
use App\Models\Property;
use App\Models\Setting;
use App\Services\AvailabilityService;
use App\Services\PricingService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PublicBookingController extends Controller
{
    public function checkAvailability(Request $request)
    {
        $validated = $request->validate([
            'property_id' => ['required', 'exists:properties,id'],
            'check_in' => ['required', 'date', 'after_or_equal:today'],
            'check_out' => ['required', 'date', 'after:check_in'],
        ]);

        $property = Property::findOrFail($validated['property_id']);
        $available = AvailabilityService::isAvailable($property, $validated['check_in'], $validated['check_out']);

        if (!$available) {
            return response()->json(['available' => false]);
        }

        $pricing = PricingService::calculate($property, $validated['check_in'], $validated['check_out']);
        $minNights = PricingService::getMinNightsForDate($property, Carbon::parse($validated['check_in']));

        if ($pricing['nights'] < $minNights) {
            return response()->json(['available' => false, 'min_nights' => $minNights]);
        }

        $depositPercent = (int) Setting::get('deposit_percent', 30);

        return response()->json([
            'available' => true,
            'pricing' => $pricing,
            'deposit_percent' => $depositPercent,
            'deposit_amount' => (int) round($pricing['total_price'] * $depositPercent / 100),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'property_id' => ['required', 'exists:properties,id'],
            'check_in' => ['required', 'date', 'after_or_equal:today'],
            'check_out' => ['required', 'date', 'after:check_in'],
            'guests_count' => ['required', 'integer', 'min:1'],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email'],
            'phone' => ['nullable', 'string', 'max:30'],
            'special_requests' => ['nullable', 'string', 'max:1000'],
        ]);

        $property = Property::findOrFail($validated['property_id']);

        if (!$property->reservations_enabled) {
            return back()->withErrors(['property_id' => 'Rezerwacje są wyłączone dla tego obiektu.']);
        }

        if (!AvailabilityService::isAvailable($property, $validated['check_in'], $validated['check_out'])) {
            return back()->withErrors(['check_in' => 'Wybrany termin jest niedostępny.']);
        }

        $pricing = PricingService::calculate($property, $validated['check_in'], $validated['check_out']);
        $depositPercent = (int) Setting::get('deposit_percent', 30);
        $depositAmount = (int) round($pricing['total_price'] * $depositPercent / 100);

        $guest = Guest::firstOrCreate(
            ['email' => $validated['email']],
            [
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'phone' => $validated['phone'],
            ]
        );

        $booking = Booking::create([
            'property_id' => $property->id,
            'guest_id' => $guest->id,
            'check_in' => $validated['check_in'],
            'check_out' => $validated['check_out'],
            'nights' => $pricing['nights'],
            'guests_count' => $validated['guests_count'],
            'status' => 'pending',
            'source' => 'website',
            'base_total' => $pricing['base_total'],
            'cleaning_fee' => $pricing['cleaning_fee'],
            'total_price' => $pricing['total_price'],
            'deposit_amount' => $depositAmount,
            'special_requests' => $validated['special_requests'],
        ]);

        $guest->increment('booking_count');

        // Redirect to payment
        return redirect("/booking/{$booking->confirmation_code}/pay");
    }

    public function pay(string $confirmationCode)
    {
        $booking = Booking::where('confirmation_code', $confirmationCode)
            ->with(['property', 'guest'])
            ->where('status', 'pending')
            ->firstOrFail();

        return Inertia::render('Public/BookingPayment', [
            'booking' => $booking,
            'depositPercent' => (int) Setting::get('deposit_percent', 30),
        ]);
    }

    public function confirmation(string $confirmationCode)
    {
        $booking = Booking::where('confirmation_code', $confirmationCode)
            ->with(['property', 'guest'])
            ->firstOrFail();

        return Inertia::render('Public/BookingConfirmation', [
            'booking' => $booking,
        ]);
    }
}

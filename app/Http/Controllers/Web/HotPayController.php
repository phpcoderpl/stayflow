<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Setting;
use App\Services\EmailService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class HotPayController extends Controller
{
    /**
     * HotPay payment flow:
     * 1. User clicks "Pay" -> we redirect to HotPay payment page
     * 2. User pays via BLIK/transfer on HotPay page
     * 3. HotPay sends notification to our /hotpay/notify endpoint
     * 4. User is redirected back to our confirmation page
     *
     * HotPay docs: https://hotpay.pl/dokumentacja/
     */
    public function checkout(Request $request)
    {
        $validated = $request->validate([
            'booking_id' => ['required', 'exists:bookings,id'],
            'payment_type' => ['required', 'in:deposit,full'],
        ]);

        $booking = Booking::with(['guest', 'property'])->findOrFail($validated['booking_id']);
        $amount = $validated['payment_type'] === 'deposit' ? $booking->deposit_amount : $booking->total_price;
        $amountPln = number_format($amount / 100, 2, '.', '');
        $orderId = 'SF-' . Str::random(12);

        $payment = Payment::create([
            'booking_id' => $booking->id,
            'amount' => $amount,
            'method' => 'hotpay',
            'type' => $validated['payment_type'],
            'status' => 'pending',
            'ext_order_id' => $orderId,
        ]);

        $secret = Setting::get('hotpay_secret', config('stayflow.hotpay.secret'));
        $notificationPassword = Setting::get('hotpay_notification_password', config('stayflow.hotpay.notification_password'));

        // HotPay payment link generation
        // https://platnosc.hotpay.pl/ — redirect-based payment
        $params = [
            'SEKRET' => $secret,
            'KWOTA' => $amountPln,
            'NAZWA_USLUGI' => 'Rezerwacja ' . $booking->confirmation_code,
            'ADRES_WWW' => url("/booking/{$booking->confirmation_code}/confirmation"),
            'ID_ZAMOWIENIA' => $orderId,
            'EMAIL' => $booking->guest->email,
            'SEKRET_ZWROTNY' => $notificationPassword,
        ];

        // Build HotPay redirect URL
        $hotpayUrl = 'https://platnosc.hotpay.pl/?' . http_build_query($params);

        return Inertia::location($hotpayUrl);
    }

    /**
     * HotPay notification (server-to-server)
     * HotPay sends POST with payment status
     */
    public function notify(Request $request)
    {
        $notificationPassword = Setting::get('hotpay_notification_password', config('stayflow.hotpay.notification_password'));

        // Verify notification
        $receivedPassword = $request->input('SEKRET');
        if ($receivedPassword !== $notificationPassword) {
            return response('Invalid password', 401);
        }

        $orderId = $request->input('ID_ZAMOWIENIA');
        $status = $request->input('STATUS');
        $amount = $request->input('KWOTA');

        $payment = Payment::where('ext_order_id', $orderId)->first();
        if (!$payment) {
            return response('OK');
        }

        // HotPay statuses: SUCCESS, PENDING, FAILURE
        $newStatus = match ($status) {
            'SUCCESS' => 'completed',
            'FAILURE' => 'failed',
            default => $payment->status,
        };

        $payment->update([
            'status' => $newStatus,
            'paid_at' => $newStatus === 'completed' ? now() : null,
        ]);

        if ($newStatus === 'completed') {
            $booking = $payment->booking;
            $booking->update([
                'status' => 'confirmed',
                'deposit_paid' => $payment->type === 'deposit' || $payment->type === 'full',
            ]);

            // Send confirmation email
            try {
                EmailService::sendBookingConfirmation($booking);
            } catch (\Exception $e) {
                \Log::warning('Failed to send confirmation email: ' . $e->getMessage());
            }
        }

        return response('OK');
    }
}

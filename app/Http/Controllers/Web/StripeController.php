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
use Stripe\Stripe;
use Stripe\Checkout\Session as StripeSession;
use Stripe\Webhook;

class StripeController extends Controller
{
    public function checkout(Request $request)
    {
        $validated = $request->validate([
            'booking_id' => ['required', 'exists:bookings,id'],
            'payment_type' => ['required', 'in:deposit,full'],
        ]);

        $booking = Booking::with(['guest', 'property'])->findOrFail($validated['booking_id']);
        $amount = $validated['payment_type'] === 'deposit' ? $booking->deposit_amount : $booking->total_price;
        $orderId = 'SF-' . Str::random(12);

        $payment = Payment::create([
            'booking_id' => $booking->id,
            'amount' => $amount,
            'method' => 'stripe',
            'type' => $validated['payment_type'],
            'status' => 'pending',
            'ext_order_id' => $orderId,
        ]);

        Stripe::setApiKey($this->secretKey());

        $session = StripeSession::create([
            'payment_method_types' => ['blik', 'p24', 'card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'pln',
                    'product_data' => [
                        'name' => $booking->property->name . ' (' . $booking->check_in->format('d.m') . '-' . $booking->check_out->format('d.m') . ')',
                    ],
                    'unit_amount' => $amount, // already in grosze = cents
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'customer_email' => $booking->guest->email,
            'success_url' => url("/booking/{$booking->confirmation_code}/confirmation"),
            'cancel_url' => url("/booking/{$booking->confirmation_code}/pay"),
            'metadata' => [
                'order_id' => $orderId,
                'booking_id' => $booking->id,
                'payment_id' => $payment->id,
            ],
        ]);

        $payment->update(['stripe_session_id' => $session->id]);

        return Inertia::location($session->url);
    }

    public function webhook(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $webhookSecret = Setting::get('stripe_webhook_secret', config('stayflow.stripe.webhook_secret'));

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $webhookSecret);
        } catch (\Exception $e) {
            return response('Invalid signature', 400);
        }

        if ($event->type === 'checkout.session.completed') {
            $session = $event->data->object;
            $orderId = $session->metadata->order_id ?? null;

            $payment = Payment::where('ext_order_id', $orderId)->first();
            if ($payment && $payment->status !== 'completed') {
                $payment->update([
                    'status' => 'completed',
                    'paid_at' => now(),
                    'stripe_session_id' => $session->id,
                ]);

                $booking = $payment->booking;
                $booking->update([
                    'status' => 'confirmed',
                    'deposit_paid' => $payment->type === 'deposit' || $payment->type === 'full',
                ]);

                try {
                    EmailService::sendBookingConfirmation($booking);
                } catch (\Exception $e) {
                    \Log::warning('Failed to send confirmation email: ' . $e->getMessage());
                }
            }
        }

        return response('OK');
    }

    private function secretKey(): string
    {
        return Setting::get('stripe_secret_key', config('stayflow.stripe.secret_key', ''));
    }
}

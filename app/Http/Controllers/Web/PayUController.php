<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Inertia\Inertia;

class PayUController extends Controller
{
    public function checkout(Request $request)
    {
        $validated = $request->validate([
            'booking_id' => ['required', 'exists:bookings,id'],
            'payment_type' => ['required', 'in:deposit,full'],
        ]);

        $booking = Booking::with(['guest', 'property'])->findOrFail($validated['booking_id']);
        $amount = $validated['payment_type'] === 'deposit' ? $booking->deposit_amount : $booking->total_price;
        $extOrderId = 'SF-' . Str::uuid();

        $payment = Payment::create([
            'booking_id' => $booking->id,
            'amount' => $amount,
            'method' => 'payu',
            'type' => $validated['payment_type'],
            'status' => 'pending',
            'ext_order_id' => $extOrderId,
        ]);

        $token = $this->getAuthToken();
        if (!$token) {
            return back()->withErrors(['payment' => 'Błąd połączenia z systemem płatności.']);
        }

        $response = Http::withToken($token)->post($this->apiUrl() . '/orders', [
            'notifyUrl' => url('/payu/notify'),
            'continueUrl' => url("/booking/{$booking->confirmation_code}/confirmation"),
            'customerIp' => $request->ip(),
            'merchantPosId' => config('stayflow.payu.pos_id'),
            'description' => "Rezerwacja {$booking->confirmation_code}",
            'currencyCode' => 'PLN',
            'totalAmount' => (string) $amount,
            'extOrderId' => $extOrderId,
            'buyer' => [
                'email' => $booking->guest->email,
                'firstName' => $booking->guest->first_name,
                'lastName' => $booking->guest->last_name,
                'phone' => $booking->guest->phone,
            ],
            'products' => [[
                'name' => $booking->property->name . ' (' . $booking->check_in->format('d.m') . '-' . $booking->check_out->format('d.m') . ')',
                'unitPrice' => (string) $amount,
                'quantity' => '1',
            ]],
        ]);

        if ($response->status() === 302 || $response->successful()) {
            $data = $response->json();
            if (isset($data['redirectUri'])) {
                $payment->update(['payu_order_id' => $data['orderId'] ?? null]);
                return Inertia::location($data['redirectUri']);
            }
        }

        $payment->update(['status' => 'failed']);
        return back()->withErrors(['payment' => 'Nie udało się utworzyć płatności.']);
    }

    public function notify(Request $request)
    {
        $body = $request->getContent();
        $signature = $request->header('OpenPayu-Signature');

        if (!$this->verifySignature($body, $signature)) {
            return response('Invalid signature', 401);
        }

        $data = json_decode($body, true);
        $order = $data['order'] ?? null;
        if (!$order) return response('OK');

        $payment = Payment::where('ext_order_id', $order['extOrderId'])->first();
        if (!$payment) return response('OK');

        $status = match ($order['status']) {
            'COMPLETED' => 'completed',
            'CANCELED' => 'failed',
            'REJECTED' => 'failed',
            default => $payment->status,
        };

        $payment->update([
            'status' => $status,
            'payu_order_id' => $order['orderId'],
            'paid_at' => $status === 'completed' ? now() : null,
        ]);

        if ($status === 'completed') {
            $booking = $payment->booking;
            $booking->update([
                'status' => 'confirmed',
                'deposit_paid' => $payment->type === 'deposit' || $payment->type === 'full',
            ]);
        }

        return response('OK');
    }

    private function getAuthToken(): ?string
    {
        $response = Http::asForm()->post($this->apiUrl() . '/pl/standard/user/oauth/authorize', [
            'grant_type' => 'client_credentials',
            'client_id' => config('stayflow.payu.pos_id'),
            'client_secret' => config('stayflow.payu.client_secret'),
        ]);

        return $response->successful() ? $response->json('access_token') : null;
    }

    private function verifySignature(string $body, ?string $header): bool
    {
        if (!$header) return false;
        $parts = [];
        foreach (explode(';', $header) as $part) {
            [$key, $val] = explode('=', $part, 2);
            $parts[trim($key)] = trim($val);
        }
        $expected = md5($body . config('stayflow.payu.second_key'));
        return isset($parts['signature']) && hash_equals($expected, $parts['signature']);
    }

    private function apiUrl(): string
    {
        return config('stayflow.payu.sandbox')
            ? 'https://secure.snd.payu.com/api/v2_1'
            : 'https://secure.payu.com/api/v2_1';
    }
}

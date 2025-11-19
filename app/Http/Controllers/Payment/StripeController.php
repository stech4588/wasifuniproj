<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Services\Payment\StripePaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class StripeController extends Controller
{
    protected StripePaymentService $stripePaymentService;

    public function __construct()
    {
        $this->stripePaymentService = new StripePaymentService();
    }

    public function createPaymentIntent(Request $request)
    {
        $request->validate([
            'amount' => ['required', 'numeric', 'gt:0'],
            'currency' => ['nullable', 'string'],
        ]);

        $amount = (float) $request->input('amount');
        $currency = $request->input('currency');

        $metadata = [];
        if (Auth::check()) {
            $metadata['user_id'] = Auth::id();
            $metadata['user_email'] = Auth::user()->email;
        }

        $result = $this->stripePaymentService->createPaymentIntent($amount, $currency, $metadata);

        if (isset($result['error'])) {
            return $this->ApiResponse($result['error'], 500);
        }

        $intent = $result['intent'];
        return $this->ApiResponse('success', 200, [
            'client_secret' => $intent->client_secret,
            'payment_intent_id' => $intent->id,
        ]);
    }

    public function config()
    {
        return $this->ApiResponse('success', 200, [
            'public_key' => config('services.stripe.public'),
            'currency' => config('services.stripe.currency', 'usd'),
        ]);
    }
}

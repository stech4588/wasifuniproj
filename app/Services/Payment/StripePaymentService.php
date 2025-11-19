<?php

namespace App\Services\Payment;

use Illuminate\Support\Facades\Log;
use Stripe\StripeClient;

class StripePaymentService
{
    protected StripeClient $stripeClient;
    protected array $zeroDecimalCurrencies = ['bif', 'clp', 'djf', 'gnf', 'jpy', 'kmf', 'krw', 'mga', 'pyg', 'rwf', 'ugx', 'vnd', 'vuv', 'xaf', 'xof', 'xpf'];

    public function __construct()
    {
        $secret = config('services.stripe.secret');
        $this->stripeClient = new StripeClient($secret);
    }

    public function createPaymentIntent(float $amount, string $currency = null, array $metadata = [])
    {
        try {
            $currency = $currency ?? config('services.stripe.currency', 'usd');
            $intent = $this->stripeClient->paymentIntents->create([
                'amount' => $this->toMinorUnit($amount, $currency),
                'currency' => $currency,
                'automatic_payment_methods' => ['enabled' => true],
                'metadata' => $metadata,
            ]);

            return ['intent' => $intent];
        } catch (\Exception $e) {
            Log::error('StripePaymentService:createPaymentIntent error', [
                'message' => $e->getMessage(),
            ]);
            return ['error' => $e->getMessage()];
        }
    }

    public function retrievePaymentIntent(string $paymentIntentId)
    {
        try {
            $intent = $this->stripeClient->paymentIntents->retrieve($paymentIntentId);
            return ['intent' => $intent];
        } catch (\Exception $e) {
            Log::error('StripePaymentService:retrievePaymentIntent error', [
                'message' => $e->getMessage(),
                'payment_intent_id' => $paymentIntentId,
            ]);
            return ['error' => $e->getMessage()];
        }
    }

    public function toMinorUnit(float $amount, string $currency): int
    {
        if (in_array(strtolower($currency), $this->zeroDecimalCurrencies, true)) {
            return (int) round($amount);
        }

        return (int) round($amount * 100);
    }

    protected function convertToMinorUnit(float $amount, string $currency): int
    {
        return $this->toMinorUnit($amount, $currency);
    }
}

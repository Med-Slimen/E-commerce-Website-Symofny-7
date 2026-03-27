<?php
namespace App\Service;

use Stripe\Checkout\Session;
use Stripe\Stripe;

class StripePayment{
    private $redirectUrl;
    public function __construct()
    {
        Stripe::setApiKey($_SERVER['STRIPE_SECRET']);
        Stripe::setApiVersion('2026-03-25');
    }
    public function startPayment($cart){
        $session=Session::create([
            'line_items' => [
                array_map(fn(array $product)=>[
                    'price_data' => [
                        'currency' => 'usd',
                        'product_data' => [
                            'name' => $product['product']->getName(),
                        ],
                        'unit_amount' => $product['product']->getPrice() * 100,
                    ],
                    'quantity' => $product['quantity'],
                ], $cart['cart'])
            ],
            'mode' => 'payment',
            'success_url' => 'http://localhost:8000/pay/success',
            'cancel_url' => 'http://localhost:8000/pay/cancel',
            'billing_address_collection' => 'required',
            'shipping_address_collection' => [
                'allowed_countries' => ['US', 'FR'],
            ],
            'metadata' => [
                'cart' => json_encode($cart),
            ],
        ]);
        $this->redirectUrl = $session->url;

    }
    public function getStripeRedirectUrl(){
        return $this->redirectUrl;
    }
}
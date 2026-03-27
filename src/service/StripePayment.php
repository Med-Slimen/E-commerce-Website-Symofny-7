<?php
namespace App\Service;

use Stripe\Checkout\Session;
use Stripe\Stripe;

class StripePayment{
    private $redirectUrl;
    public function __construct()
    {
        Stripe::setApiKey($_SERVER['STRIPE_SECRET']);
        Stripe::setApiVersion('2026-03-25.dahlia');
    }
    public function startPayment($cart, $shippingCost,$orderId): string
{
    $cartProducts = $cart['cart'] ?? [];

    $products = [
        [
            'qte' => 1,
            'price' => $shippingCost,
            'name' => 'Shipping Cost',
        ]
    ];

    foreach ($cartProducts as $value) {
        $products[] = [
            'qte' => $value['quantity'],
            'price' => $value['product']->getPrice(),
            'name' => $value['product']->getName(),
        ];
    }

    $session = Session::create([
        'line_items' => array_map(fn(array $product) => [
            'price_data' => [
                'currency' => 'usd',
                'product_data' => [
                    'name' => $product['name'],
                ],
                'unit_amount' => (int) ($product['price'] * 100),
            ],
            'quantity' => $product['qte'],
        ], $products),
        'mode' => 'payment',
        'success_url' => 'http://127.0.0.1:8080/pay/success',
        'cancel_url' => 'http://127.0.0.1:8080/pay/cancel',
        'billing_address_collection' => 'required',
        'shipping_address_collection' => [
            'allowed_countries' => ['US', 'FR'],
        ],
        'payment_intent_data' => [
            'metadata' => [
                'orderId' => $orderId,
            ],
        ],
    ]);

    return $session->url;
}   public function getStripeRedirectUrl(){
        return $this->redirectUrl;
    }
}
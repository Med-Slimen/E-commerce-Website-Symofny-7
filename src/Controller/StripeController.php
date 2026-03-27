<?php

namespace App\Controller;

use App\Repository\OrderRepository;
use App\Service\Cart;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Stripe;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;

final class StripeController extends AbstractController
{
    #[Route('/pay/success', name: 'app_stripe_success')]
    public function success(Cart $cart,Request $request,SessionInterface $session): Response
    {
        $session->set('cart', []);
        $request->getSession()->remove('cart');
        return $this->render('stripe/success.html.twig');
    }

 #[Route('/stripe/reject', name: 'app_stripe_reject')]
    public function reject(): Response
    {
        return $this->render('stripe/reject.html.twig');
    }
    #[Route('/pay/cancel', name: 'app_stripe_cancel')]
    public function cancel(): Response
    {
        return $this->render('stripe/cancel.html.twig');
    }
    #[Route('/stripe/notify', name: 'app_stripe_notify')]
    public function stripeNotify(Request $request,OrderRepository $orderRepository,EntityManagerInterface $entityManager): Response
    {
        Stripe::setApiKey($_SERVER['STRIPE_SECRET']);
        $payload = $request->getContent();
        $sig_header = $request->headers->get('Stripe-Signature');
        $endpoint_secret='whsec_d08915158f19f9064c9e37a4a4c0f91be52c7d71cd97a45247f09422df4bf323';
        $event = null;
        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload, $sig_header, $endpoint_secret
            );
        } catch(\UnexpectedValueException $e) {
            // Invalid payload
            return new Response('Invalid payload', 400);
        } catch(\Stripe\Exception\SignatureVerificationException $e) {
            // Invalid signature
            return new Response('Invalid signature', 400);
        }
        switch ($event->type) {
            case 'payment_intent.succeeded':
               $paymentIntent = $event->data->object;
               $fileName='stripe-details-'.uniqid().'.txt';
               $orderId=$paymentIntent->metadata->orderId ?? 'N/A';
               $order=$orderRepository->find($orderId);
               
               $cartPrice=$order->getTotalPrice();
               $stripeTotalAmount=$paymentIntent->amount_received / 100;
                if($cartPrice == $stripeTotalAmount){
                    $order->setIsPaymentCompleted(true);
                    $entityManager->persist($order);
                    $entityManager->flush();

                }
               
               //file_put_contents($fileName, $orderId);
                break;
            case 'payment_method.attached':
                $paymentMethod = $event->data->object;
                break;
            default:
            break;
        }
        return new Response('Webhook received', 200);
    }
}

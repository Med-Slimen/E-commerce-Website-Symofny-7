<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use App\Service\Cart;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;

final class CartController extends AbstractController

{
    private $pr;
    public function __construct(private readonly ProductRepository $productRepository)
    {
        $this->pr = $productRepository;
    }
    #[Route('/cart', name: 'app_cart', methods: ['GET'])]
    public function index(SessionInterface $session,Cart $cart): Response
    {
        $cartData = $cart->getCart($session);
        $cartProducts = $cartData['cart'] ?? [];
        $products=[];
        
        return $this->render('cart/index.html.twig', [
            'items' => $cartData['cart'],
            'total' => $cartData['total'],
        ]);
    }
     #[Route('/cart/add/{id}', name: 'app_cart_new', methods: ['GET'])]
    public function addToCart($id, SessionInterface $session): Response
    {
        $cart = $session->get('cart', []);
        if (!empty($cart[$id])) {
            $cart[$id]++;
        } else {
            $cart[$id] = 1;
        }
        $session->set('cart', $cart);
        return $this->redirectToRoute('app_cart');
    }
     #[Route('/cart/remove/{id}', name: 'app_cart_product_remove', methods: ['GET'])]
    public function removeFromCart($id, SessionInterface $session): Response
    {
        $cart = $session->get('cart', []);
        if (!empty($cart[$id])) {
            unset($cart[$id]);
        }
        $session->set('cart', $cart);
        return $this->redirectToRoute('app_cart');
    }
     #[Route('/cart/remove', name: 'app_cart_remove', methods: ['GET'])]
    public function removeCart(SessionInterface $session): Response
    {
        $session->set('cart', []);
        return $this->redirectToRoute('app_cart');
    }
}

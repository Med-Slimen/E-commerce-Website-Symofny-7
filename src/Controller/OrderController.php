<?php

namespace App\Controller;

use App\Entity\City;
use App\Entity\Order;
use App\Entity\OrderProducts;
use App\Form\OrderType;
use App\Repository\ProductRepository;
use App\Service\Cart;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;

final class OrderController extends AbstractController
{
    #[Route('/order', name: 'app_order')]
    public function index(Request $request, EntityManagerInterface $entityManager, 
    SessionInterface $session, ProductRepository $productRepository,
    Cart $cart): Response
    {
        $cartData = $cart->getCart($session);
        $order = new Order();
        $form = $this->createForm(OrderType::class, $order);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if($order->isPayOnDelivery()){
                if(!empty ($cartData['cart'])){
                $order->setTotalPrice($cartData['total']);
                $order->setCreatedAt(new \DateTimeImmutable());
                $entityManager->persist($order);
                $entityManager->flush();
                foreach ($cartData['cart'] as $item) {
                    $orderProducts = new OrderProducts();
                    $orderProducts->setOrder($order);
                    $orderProducts->setProduct($item['product']);
                    $orderProducts->setQte($item['quantity']);
                    $entityManager->persist($orderProducts);
                    $entityManager->flush();
                }
                }
                $session->set('cart', []);
                return $this->redirectToRoute('app_order_ok_message');
            }else{
                
                }
        }
        return $this->render('order/index.html.twig', [
            'form' => $form->createView(),
            'total' => $cartData['total'],
        ]);
    }
      #[Route('/city/{id}/shipping/cost', name: 'app_city_shipping_cost', methods: ['GET'])]
    public function cityShippingCost(City $city): Response{
        $cityShippingPrice=$city->getShippingCost();
        return new Response(json_encode(['status' => 200,'message' => 'on' ,'content' => $cityShippingPrice]));
    }
     #[Route('/order-ok-message', name: 'app_order_ok_message')]
    public function orderMessage(): Response{
        return $this->render('order/order_message.html.twig');
    }
}

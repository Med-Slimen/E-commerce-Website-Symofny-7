<?php

namespace App\Controller;

use App\Entity\City;
use App\Entity\Order;
use App\Entity\OrderProducts;
use App\Form\OrderType;
use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use App\Service\Cart;
use App\Service\StripePayment;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;

final class OrderController extends AbstractController
{
    public function __construct(private MailerInterface $mailer)
    {

    }
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
                $html=$this->renderView('mail/orderConfirm.html.twig', [
                    'order' => $order,
                ]);
                $email=(new Email())
                    ->from('myShop@gmail.com')
                    ->to($order->getEmail())
                    ->subject('Order Confirmation')
                    ->html($html);
                $this->mailer->send($email);
                return $this->redirectToRoute('app_order_ok_message');
            }
            $payment = new StripePayment();
            $payment->startPayment($cartData);
            $stripeRedirectUrl = $payment->getStripeRedirectUrl();
             return $this->redirect($stripeRedirectUrl);
        }
        return $this->render('order/index.html.twig', [
            'form' => $form->createView(),
            'total' => $cartData['total'],
        ]);
    }
    #[Route('/editor/order', name: 'app_order_show')]
    public function getAllOrders(OrderRepository $orderRepository,Request $request,PaginatorInterface $paginatorInterface): Response{
        $data=$orderRepository->findBy([], ['id' => 'DESC']);
         $orders = $paginatorInterface->paginate(
            $data,
            $request->query->getInt('page', 1),
            1
        );
        return $this->render('order/order.html.twig',[
            'orders'=>$orders
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
     #[Route('/editor/order/{id}/is-completed/update', name: 'app_order_update_is_completed')]
    public function isCompletedUpdate($id,OrderRepository $orderRepository,EntityManagerInterface $entityManager): Response{
        $order = $orderRepository->find($id);
        $order->setIsCompleted(true);
        $entityManager->persist($order);
        $entityManager->flush();
        $this->addFlash('success', 'Order marked as delivered successfully!');
        return $this->redirectToRoute('app_order_show');
        }
     #[Route('/editor/order/{id}/remove', name: 'app_order_remove')]
    public function removeOrder(Order $order,OrderRepository $orderRepository,EntityManagerInterface $entityManager): Response{
        $entityManager->remove($order);
        $entityManager->flush();
        $this->addFlash('success', 'Order deleted successfully!');
        return $this->redirectToRoute('app_order_show');
    }
}

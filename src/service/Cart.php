<?php
namespace App\Service;

use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class Cart{
    public function __construct(private readonly ProductRepository $productRepository)
    {
    }
     public function getCart(SessionInterface $session):array{
        $cart = $session->get('cart', []);
        $cartWithData = [];
        foreach ($cart as $id => $quantity) {
            $cartWithData[] = [
                'product' => $this->productRepository->find($id),
                'quantity' => $quantity
            ];
        }
        $total=array_sum(array_map(function($item){
            return $item['product']->getPrice() * $item['quantity'];
        },$cartWithData));
        return ['cart'=>$cartWithData,'total'=>$total];
    }
}
<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use App\Repository\SubCategoryRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home',methods: ['GET'])]
    public function index(ProductRepository $productRepository,CategoryRepository $categoryRepository,Request $request,PaginatorInterface $paginator): Response
    {
        $data = $productRepository->findBy([], ['id' => 'DESC']);
        $products = $paginator->paginate(
            $data,
            $request->query->getInt('page', 1),
            3
        );
        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'products' => $products,
            'categories' => $categoryRepository->findAll(),
        ]);
    }
    
    #[Route('/home/product/{id}/show', name: 'app_home_product_show',methods: ['GET'])]
    public function show(Product $product,ProductRepository $productRepository,CategoryRepository $categoryRepository): Response
    {
        $latestProducts = $productRepository->findBy([], ['id' => 'DESC'], 5);
        return $this->render('home/show.html.twig', [
            'controller_name' => 'HomeController',
            'product' => $product,
            'latestProducts' => $latestProducts,
            'categories' => $categoryRepository->findAll(),
        ]);
    }
     #[Route('/home/product/subcategory/{id}/show', name: 'app_home_product_filter',methods: ['GET'])]
    public function filter(SubCategoryRepository $subCategoryRepository,$id,CategoryRepository $categoryRepository): Response
    {
        $products = $subCategoryRepository->find($id)->getProducts();
        $subCategory=$subCategoryRepository->find($id);

        return $this->render('home/filter.html.twig', [
            'products' => $products,
            'subCategory' => $subCategory,
            'categories' => $categoryRepository->findAll(),

        ]);
    }
}

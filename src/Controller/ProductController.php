<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Product;
use App\Form\ProductType;

class ProductController extends AbstractController
{
    #[Route('/product', name: 'app_product')]
    public function index(): Response
    {
        return $this->render('product/index.html.twig', [
            'controller_name' => 'ProductController',
        ]);
    }

    #[Route('/store/product', name: 'app_product_store')]
    public function store(Request $request): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);

       return $this->render('product/index.html.twig', [
    'form' => $form->createView(),
]);
    }
}
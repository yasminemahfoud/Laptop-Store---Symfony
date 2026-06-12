<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Product;
use App\Form\ProductType;
use Doctrine\ORM\EntityManagerInterface; // ✅ ajouté

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
    public function store(Request $request, EntityManagerInterface $em): Response // ✅ $em ajouté
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);

        $form->handleRequest($request); // ✅ lit les données du formulaire

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($product); // ✅ prépare l'enregistrement
            $em->flush();           // ✅ sauvegarde en base

            return $this->redirectToRoute('app_product_store'); // ✅ redirect après submit
        }

        return $this->render('product/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ProductController extends AbstractController
{
    public function __construct(
        private ProductRepository $productRepository,
        private EntityManagerInterface $entityManager
    ) {
    }

    #[Route('/product', name: 'app_product')]
    public function index(CategoryRepository $categoryRepository): Response
    {
        $products = $this->productRepository->findAll();
        $categories = $categoryRepository->findAll();

        return $this->render('product/index.html.twig', [
            'products' => $products,
            'categories' => $categories,
        ]);
    }

    #[Route('/admin/products', name: 'app_product_admin')]
    #[IsGranted('ROLE_ADMIN', statusCode: 404, message: 'Page not found')]
    public function adminIndex(): Response
    {
        $products = $this->productRepository->findAll();

        return $this->render('product/admin_index.html.twig', [
            'products' => $products,
        ]);
    }

    #[Route('/product/create', name: 'app_product_create')]
    #[IsGranted('ROLE_ADMIN', statusCode: 404, message: 'Page not found')]
    public function create(Request $request): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('image')->getData();

            if ($imageFile) {
                $imageName = time() . '_' . $imageFile->getClientOriginalName();
                $imageFile->move(
                    $this->getParameter('image_directory'),
                    $imageName
                );
                $product->setImage($imageName);
            }

            $this->entityManager->persist($product);
            $this->entityManager->flush();

            $this->addFlash('success', 'Product created successfully');

            return $this->redirectToRoute('app_product');
        }

        return $this->render('product/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/product/edit/{id}', name: 'app_product_edit')]
    #[IsGranted('ROLE_ADMIN', statusCode: 404, message: 'Page not found')]
    public function edit(Request $request, Product $product): Response
    {
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('image')->getData();

            if ($imageFile) {
                $imageName = time() . '_' . $imageFile->getClientOriginalName();
                $imageFile->move(
                    $this->getParameter('image_directory'),
                    $imageName
                );
                $product->setImage($imageName);
            }

            $this->entityManager->flush();

            $this->addFlash('success', 'Product updated successfully');

            return $this->redirectToRoute('app_product');
        }

        return $this->render('product/edit.html.twig', [
            'form' => $form->createView(),
            'product' => $product,
        ]);
    }

    #[Route('/product/delete/{id}', name: 'app_product_delete')]
    #[IsGranted('ROLE_ADMIN', statusCode: 404, message: 'Page not found')]
    public function delete(Product $product): Response
    {
        $this->entityManager->remove($product);
        $this->entityManager->flush();

        $this->addFlash('success', 'Product deleted successfully');

        return $this->redirectToRoute('app_product');
    }

    #[Route('/product/show/{id}', name: 'app_product_show')]
    #[IsGranted('ROLE_ADMIN', statusCode: 404, message: 'Page not found')]
    public function show(Product $product): Response
    {
        return $this->render('product/show.html.twig', [
            'product' => $product,
        ]);
    }

    #[Route('/product/user/{id}', name: 'app_product_user')]
    public function userShow(Product $product): Response
    {
        return $this->render('product/user.html.twig', [
            'product' => $product,
        ]);
    }

    #[Route('/product/filter/{id}', name: 'app_product_filter')]
    public function filter(int $id, CategoryRepository $categoryRepository): Response
    {
        $products = $this->productRepository->findBy([
            'category' => $id
        ]);

        $categories = $categoryRepository->findAll();

        return $this->render('product/index.html.twig', [
            'products' => $products,
            'categories' => $categories,
        ]);
    }
}
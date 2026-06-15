<?php

namespace App\Controller;

use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class OrderController extends AbstractController
{
    public function __construct(
        private ProductRepository $productRepository,
        private OrderRepository $orderRepository,
        private EntityManagerInterface $entityManager
    ) {}

    #[Route('/orders', name: 'order_list')]
    public function index(): Response
    {
        $orders = $this->orderRepository->findAll();

        return $this->render('order/index.html.twig', [
            'orders' => $orders
        ]);
    }

    #[Route('/user/orders', name: 'app_user_orders')]
    #[IsGranted('ROLE_USER')]
    public function userOrders(): Response
    {
        $user = $this->getUser();

        return $this->render('order/user_orders.html.twig', [
            'orders' => $user->getOrders(),
        ]);
    }

    #[Route('/order/status/{id}/{status}', name: 'order_status_update')]
    public function updateOrderStatus(int $id, string $status): Response
    {
        $order = $this->orderRepository->find($id);

        $order->setStatus($status);
        $this->entityManager->flush();

        $this->addFlash('success', 'Order status updated');

        return $this->redirectToRoute('order_list');
    }
}
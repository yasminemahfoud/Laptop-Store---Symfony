<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\Product;
use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
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
    #[IsGranted('ROLE_ADMIN', statusCode: 404, message: 'Page not found')]
    public function index(): Response
    {
        $orders = $this->orderRepository->findAll();

        return $this->render('order/index.html.twig', [
            'orders' => $orders,
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
    #[IsGranted('ROLE_ADMIN', statusCode: 404, message: 'Page not found')]
    public function updateOrderStatus(int $id, string $status): Response
    {
        $order = $this->orderRepository->find($id);

        if (!$order) {
            throw $this->createNotFoundException('Order not found');
        }

        $order->setStatus($status);

        $this->entityManager->flush();

        $this->addFlash('success', 'Order status updated');

        return $this->redirectToRoute('order_list');
    }

    #[Route('/order/delete/{id}', name: 'order_delete')]
    #[IsGranted('ROLE_ADMIN', statusCode: 404, message: 'Page not found')]
    public function deleteOrder(Order $order): Response
    {
        $this->entityManager->remove($order);
        $this->entityManager->flush();

        $this->addFlash('success', 'Order deleted successfully.');

        return $this->redirectToRoute('order_list');
    }

    #[Route('/order/place/{id}', name: 'app_order_place')]
    #[IsGranted('ROLE_USER')]
    public function placeOrder(Product $product): Response
    {
        $order = new Order();

        $order->setPname($product->getName());
        $order->setPrice($product->getPrice());
        $order->setStatus('processing...');
        $order->setUser($this->getUser());

        $this->entityManager->persist($order);
        $this->entityManager->flush();

        // ==========================
        // Send Email to Admin
        // ==========================

        $mail = new PHPMailer(true);

        try {

            $mail->isSMTP();
            $mail->Host = 'sandbox.smtp.mailtrap.io';
            $mail->SMTPAuth = true;
            $mail->Username = '59ed97a334375b';
            $mail->Password = '9221a2e3558218';
            $mail->Port = 2525;

            $mail->setFrom('store@laptop.com', 'Laptop Store');
            $mail->addAddress('mahfoudyasmine@gmail.com');

            $mail->isHTML(true);
            $mail->Subject = 'New Order Received';

            $mail->Body = '
                <h2>New Order</h2>

                <table border="1" cellpadding="10" cellspacing="0">
                    <tr>
                        <th>Customer</th>
                        <td>'.$this->getUser()->getUsername().'</td>
                    </tr>

                    <tr>
                        <th>Product</th>
                        <td>'.$product->getName().'</td>
                    </tr>

                    <tr>
                        <th>Price</th>
                        <td>'.$product->getPrice().' DH</td>
                    </tr>

                    <tr>
                        <th>Status</th>
                        <td>Processing...</td>
                    </tr>
                </table>

                <br>

                <p>A new customer has placed an order on the Laptop Store website.</p>
            ';

            $mail->send();

        } catch (Exception $e) {
            // Ignore email errors
        }

        $this->addFlash('success', 'Your order was placed successfully.');

        return $this->redirectToRoute('app_user_orders');
    }
}

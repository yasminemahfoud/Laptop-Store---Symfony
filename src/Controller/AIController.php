<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[Route('/ai')]
class AIController extends AbstractController
{
    private HttpClientInterface $client;
    private ProductRepository $productRepository;

    public function __construct(
        HttpClientInterface $client,
        ProductRepository $productRepository
    ) {
        $this->client = $client;
        $this->productRepository = $productRepository;
    }

    #[Route('', name: 'app_ai', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function index(): Response
    {
        return $this->render('ai/chat.html.twig');
    }

    #[Route('/chat', name: 'app_ai_chat', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function chat(Request $request): JsonResponse
    {
        $message = trim($request->request->get('message', ''));

        if ($message === '') {
            return new JsonResponse([
                'reply' => 'Please enter your question.'
            ]);
        }

        // Récupérer les produits disponibles
        $products = $this->productRepository->findAll();

        $catalog = "";

        foreach ($products as $product) {

            if ($product->getQuantity() <= 0) {
                continue;
            }

            $catalog .=
                "Name: " . $product->getName() .
                "\nCategory: " . $product->getCategory() .
                "\nPrice: " . $product->getPrice() . " DH" .
                "\nQuantity: " . $product->getQuantity() .
                "\nDescription: " . $product->getDescription() .
                "\n-----------------------------\n";
        }

        $prompt = "
You are an AI sales assistant for a Laptop Store.

You ONLY recommend laptops موجودة في هذا الكاتالوج.

Rules:
- Never invent products.
- Never recommend products not موجودة في catalog.
- If user asks about gaming, programming, student, business...
recommend the best matching laptop.
- Mention the price.
- Answer in the same language used by the customer.

Available products:

$catalog

Customer question:

$message
";
    $apiKey = $_ENV['GEMINI_API_KEY'];

try {

    $response = $this->client->request(
        'POST',
        'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=' . $apiKey,
        [
            'headers' => [
                'Content-Type' => 'application/json'
            ],
            'json' => [
                'contents' => [
                    [
                        'parts' => [
                            [
                                'text' => $prompt
                            ]
                        ]
                    ]
                ]
            ]
        ]
    );

    $data = $response->toArray(false);

    if (isset($data['error'])) {
        return new JsonResponse([
            'reply' => 'Gemini Error: ' . $data['error']['message']
        ]);
    }

    if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {

        return new JsonResponse([
            'reply' => $data['candidates'][0]['content']['parts'][0]['text']
        ]);

    }

    return new JsonResponse([
        'reply' => 'No response returned by Gemini.',
        'debug' => $data
    ]);

} catch (\Exception $e) {

    return new JsonResponse([
        'reply' => 'Exception: ' . $e->getMessage()
    ]);

}
    }
}
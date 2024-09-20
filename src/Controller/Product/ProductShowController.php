<?php

declare(strict_types=1);

namespace App\Controller\Product;

use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api')]
class ProductShowController extends AbstractController
{
    #[Route('/product/{id<\d+>}', name: 'api_product', methods: [Request::METHOD_GET])]
    public function show(ProductRepository $productRepository, int $id): JsonResponse
    {
        $product = $productRepository->find($id);

        if (empty($product)) {
            return new JsonResponse(
                ['status' =>
                Response::HTTP_NOT_FOUND, 'message' =>
                'Aucun produit n\'a été trouvé.'],
                Response::HTTP_NOT_FOUND
            );
        }

        $data = [
            'id' => $product->getId(),
            'name' => $product->getName(),
            'description' => $product->getDescription(),
            'creation_date' => $product->getCreationDate()->format('Y-m-d H:i:s'),
            'image' => $product->getImage(),
            'price' => $product->getPrice(),
            'brand' => $product->getBrand(),
            'reference' => $product->getReference(),
        ];

        return new JsonResponse([
            'status' => Response::HTTP_OK,
            'data' => $data
        ], Response::HTTP_OK);
    }
}
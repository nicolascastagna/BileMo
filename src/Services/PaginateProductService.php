<?php

declare(strict_types=1);

namespace App\Services;

use App\Interfaces\ProductListServiceInterface;
use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PaginateProductService implements ProductListServiceInterface
{
    public function __construct(
        private readonly ProductRepository $productRepository,
        private readonly UrlGeneratorInterface $urlGenerator
    ) {}

    public function getPaginatedProducts(Request $request): array
    {
        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 10);

        $paginator = $this->productRepository->findPaginatedProducts($page, $limit);

        $products = $paginator->getIterator();
        $totalPages = ceil($paginator->count() / $limit);

        $data = [];
        foreach ($products as $product) {
            $data[] = [
                'id' => $product->getId(),
                'name' => $product->getName(),
                'description' => $product->getDescription(),
                'creation_date' => $product->getCreationDate()->format('Y-m-d H:i:s'),
                'image' => $product->getImage(),
                'price' => $product->getPrice(),
                'brand' => $product->getBrand(),
                'reference' => $product->getReference(),
            ];
        }

        $nextPageUrl = ($page < $totalPages) ? $this->urlGenerator->generate('api_products', ['page' => $page + 1, 'limit' => $limit]) : null;
        $prevPageUrl = ($page > 1) ? $this->urlGenerator->generate('api_products', ['page' => $page - 1, 'limit' => $limit]) : null;

        return [
            'status' => Response::HTTP_OK,
            'data' => $data,
            'pagination' => [
                'currentPage' => $page,
                'totalPages' => $totalPages,
                'totalItems' => $paginator->count(),
                'nextPageUrl' => $nextPageUrl,
                'prevPageUrl' => $prevPageUrl
            ]
        ];
    }
}

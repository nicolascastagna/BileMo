<?php

declare(strict_types=1);

namespace App\Controller\Product;

use App\Interfaces\ProductListServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class ProductListController extends AbstractController
{
    #[Route('/products', name: 'api_products', methods: [Request::METHOD_GET])]
    public function list(
        Request $request,
        ProductListServiceInterface $productListService,
        TagAwareCacheInterface $cache
    ): JsonResponse {
        $cacheKey = 'product_list';
        $cacheTag = 'product_data';

        $response = $cache->get($cacheKey, function (ItemInterface $item) use ($request, $productListService, $cacheTag) {
            $item->expiresAfter(3600);
            $item->tag($cacheTag);

            $result = $productListService->getPaginatedProducts($request);

            return new JsonResponse([
                'status' => Response::HTTP_OK,
                'data' => $result['data'],
                'pagination' => $result['pagination']
            ], Response::HTTP_OK);
        });

        return $response;
    }
}

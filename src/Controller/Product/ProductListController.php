<?php

declare(strict_types=1);

namespace App\Controller\Product;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OpenAttribute;
use Symfony\Component\Serializer\SerializerInterface;

class ProductListController extends AbstractController
{
    #[
        OpenAttribute\Tag(name: 'Products'),
        OpenAttribute\Response(
            response: Response::HTTP_OK,
            description: 'Returns all products',
            content: new OpenAttribute\JsonContent(
                type: 'array',
                items: new OpenAttribute\Items(ref: new Model(type: Product::class))
            ),
        )
    ]
    #[Route('/products', name: 'api_products', methods: [Request::METHOD_GET])]
    public function list(
        Request $request,
        ProductRepository $productRepository,
        TagAwareCacheInterface $cache,
        SerializerInterface $serializer
    ): JsonResponse {
        $cacheKey = 'product_list' . '_page_' . $request->query->getInt('page', 1);
        $cacheTag = 'product_data';

        $response = $cache->get($cacheKey, function (ItemInterface $item) use ($request, $productRepository, $serializer, $cacheTag) {
            $item->expiresAfter(3600);
            $item->tag($cacheTag);

            $page = $request->query->getInt('page', 1);
            $limit = $request->query->getInt('limit', 10);

            $paginator = $productRepository->findPaginatedProducts($page, $limit);
            $products = $paginator->getIterator();

            $jsonData = $serializer->serialize($products, 'json', ['groups' => ['product']]);

            return new JsonResponse([
                'status' => Response::HTTP_OK,
                'data' => json_decode($jsonData, true),
            ], Response::HTTP_OK);
        });
        $response->headers->set('Cache-Control', 'public, max-age=3600');

        return $response;
    }
}

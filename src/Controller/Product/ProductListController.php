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
        TagAwareCacheInterface $cache
    ): JsonResponse {
        $cacheKey = 'product_list' . '_page_' . $request->query->getInt('page', 1);
        $cacheTag = 'product_data';

        $response = $cache->get($cacheKey, function (ItemInterface $item) use ($request, $productRepository, $cacheTag) {
            $item->expiresAfter(3600);
            $item->tag($cacheTag);

            $page = $request->query->getInt('page', 1);
            $limit = $request->query->getInt('limit', 10);

            $paginator = $productRepository->findPaginatedProducts($page, $limit);
            $products = $paginator->getIterator();

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

            return new JsonResponse([
                'status' => Response::HTTP_OK,
                'data' => $data,
            ], Response::HTTP_OK);
        });

        $response->headers->set('Cache-Control', 'public, max-age=3600');
        return $response;
    }
}

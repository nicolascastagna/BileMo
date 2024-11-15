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
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OpenAttribute;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class ProductShowController extends AbstractController
{
    #[
        OpenAttribute\Tag(name: 'Products'),
        OpenAttribute\Parameter(
            name: 'id',
            in: 'path',
            required: true,
            description: 'ID of the product',
            schema: new OpenAttribute\Schema(type: 'integer', example: 1)
        ),
        OpenAttribute\Response(
            response: Response::HTTP_OK,
            description: 'Return product',
            content: new OpenAttribute\JsonContent(
                type: 'array',
                items: new OpenAttribute\Items(ref: new Model(type: Product::class))
            ),
        ),
        OpenAttribute\Response(
            response: Response::HTTP_NOT_FOUND,
            description: 'Product not found',
            content: new OpenAttribute\JsonContent(
                type: 'object',
                properties: [
                    new OpenAttribute\Property(property: 'status', type: 'integer', example: 404),
                    new OpenAttribute\Property(property: 'message', type: 'string', example: 'Aucun produit n\'a été trouvé.')
                ]
            )
        )
    ]
    #[Route('/product/{id<\d+>}', name: 'api_product', methods: [Request::METHOD_GET])]
    public function show(
        ProductRepository $productRepository,
        int $id,
        SerializerInterface $serializer,
        TagAwareCacheInterface $cache
    ): JsonResponse {
        $cacheKey = 'product_' . $id;
        $cacheTag = 'product_data';

        $product = $productRepository->find($id);

        if (empty($product)) {
            return new JsonResponse(
                [
                    'status' =>
                    Response::HTTP_NOT_FOUND,
                    'message' => 'Aucun produit n\'a été trouvé.'
                ],
                Response::HTTP_NOT_FOUND
            );
        }

        $response = $cache->get($cacheKey, function (ItemInterface $item) use ($product, $serializer, $cacheTag) {
            $item->expiresAfter(3600);
            $item->tag($cacheTag);

            $jsonData = $serializer->serialize($product, 'json', ['groups' => ['product']]);

            return new JsonResponse([
                'status' => Response::HTTP_OK,
                'data' => json_decode($jsonData, true),
            ], Response::HTTP_OK);
        });
        $response->headers->set('Cache-Control', 'public, max-age=3600');

        return $response;
    }
}

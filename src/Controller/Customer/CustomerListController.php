<?php

declare(strict_types=1);

namespace App\Controller\Customer;

use App\Entity\User;
use App\Repository\CustomerRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\SecurityBundle\Security as SecurityBundleSecurity;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OpenAttribute;

class CustomerListController extends AbstractController
{
    #[
        OpenAttribute\Tag(name: 'Customers'),
        OpenAttribute\Parameter(
            name: 'id',
            in: 'path',
            required: true,
            description: 'ID of the customer',
            schema: new OpenAttribute\Schema(type: 'integer', example: 1)
        ),
        OpenAttribute\Parameter(
            name: 'page',
            in: 'query',
            description: 'Page number for pagination',
            required: false,
            schema: new OpenAttribute\Schema(type: 'integer', default: 1)
        ),
        OpenAttribute\Parameter(
            name: 'limit',
            in: 'query',
            description: 'Number of users per page',
            required: false,
            schema: new OpenAttribute\Schema(type: 'integer', default: 10)
        ),
        OpenAttribute\Response(
            response: Response::HTTP_OK,
            description: 'Returns a paginated list of users associated with the customer',
            content: new OpenAttribute\JsonContent(
                type: 'array',
                items: new OpenAttribute\Items(
                    ref: new Model(type: User::class, groups: ['user'])
                )
            )
        ),
        OpenAttribute\Response(
            response: Response::HTTP_NOT_FOUND,
            description: 'Customer not found',
            content: new OpenAttribute\JsonContent(
                type: 'object',
                properties: [
                    new OpenAttribute\Property(property: 'status', type: 'integer', example: 404),
                    new OpenAttribute\Property(property: 'message', type: 'string', example: 'Aucun client n\'a été trouvé.')
                ]
            )
        ),
    ]
    #[Route('/customer/{id<\d+>}/user', name: 'api_customer_users', methods: [Request::METHOD_GET])]
    public function list(
        int $id,
        Request $request,
        CustomerRepository $customerRepository,
        UserRepository $userRepository,
        SerializerInterface $serializer,
        TagAwareCacheInterface $cache,
        SecurityBundleSecurity $security
    ): JsonResponse {
        $currentCustomer = $security->getUser();

        if ($currentCustomer && $currentCustomer->getId() !== $id) {
            return new JsonResponse(
                [
                    'status' => Response::HTTP_FORBIDDEN,
                    'message' => 'Vous n\'êtes pas autorisé à accéder à cette ressource.'
                ],
                Response::HTTP_FORBIDDEN
            );
        }

        $customer = $customerRepository->find($id);
        if (empty($customer)) {
            return new JsonResponse(
                [
                    'status' =>
                    Response::HTTP_NOT_FOUND,
                    'message' => 'Aucun client n\'a été trouvé.'
                ],
                Response::HTTP_NOT_FOUND
            );
        }

        $cacheKey = 'customer_users_' . $id . '_page_' . $request->query->getInt('page', 1);
        $cacheTag = 'customer_data';

        $response = $cache->get($cacheKey, function (ItemInterface $item) use ($id, $request, $userRepository, $serializer, $cacheTag) {
            $item->expiresAfter(3600);
            $item->tag($cacheTag);

            $page = $request->query->getInt('page', 1);
            $limit = $request->query->getInt('limit', 10);

            $paginator = $userRepository->findPaginatedUsersByCustomer($id, $page, $limit);
            $paginatedUsers = $paginator->getIterator();

            $newData = $serializer->serialize($paginatedUsers, 'json', ['groups' => 'user']);

            return new JsonResponse([
                'status' => Response::HTTP_OK,
                'data' => json_decode($newData),
            ], Response::HTTP_OK);
        });
        $response->headers->set('Cache-Control', 'public, max-age=3600');

        return $response;
    }
}

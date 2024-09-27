<?php

declare(strict_types=1);

namespace App\Controller\Customer;

use App\Repository\CustomerRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class CustomerListController extends AbstractController
{
    #[Route('/customer/{id<\d+>}/user', name: 'api_customer_users', methods: [Request::METHOD_GET])]
    public function list(
        int $id,
        Request $request,
        CustomerRepository $customerRepository,
        UserRepository $userRepository,
        SerializerInterface $serializer,
        TagAwareCacheInterface $cache
    ): JsonResponse {
        $cacheKey = 'customer_users_' . $id . '_page_' . $request->query->getInt('page', 1);
        $cacheTag = 'customer_data';

        $response = $cache->get($cacheKey, function (ItemInterface $item) use ($id, $request, $customerRepository, $userRepository, $serializer, $cacheTag) {
            $item->expiresAfter(3600);
            $item->tag($cacheTag);

            $customer = $customerRepository->find($id);

            if (empty($customer)) {
                return new JsonResponse(
                    ['status' =>
                    Response::HTTP_NOT_FOUND, 'message' =>
                    'Aucun client n\'a été trouvé.'],
                    Response::HTTP_NOT_FOUND
                );
            }

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

        return $response;
    }
}

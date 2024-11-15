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
use OpenApi\Attributes as OpenAttribute;
use Symfony\Bundle\SecurityBundle\Security as SecurityBundleSecurity;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class CustomerShowController extends AbstractController
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
            name: 'userId',
            in: 'path',
            required: true,
            description: 'ID of the user',
            schema: new OpenAttribute\Schema(type: 'integer', example: 5)
        ),
        OpenAttribute\Response(
            response: Response::HTTP_OK,
            description: 'Returns information user linked to the customer',
            content: new OpenAttribute\JsonContent(
                type: 'object',
                properties: [
                    new OpenAttribute\Property(property: 'status', type: 'integer', example: 200),
                    new OpenAttribute\Property(property: 'data', type: 'object', properties: [
                        new OpenAttribute\Property(property: 'id', type: 'integer', example: 1),
                        new OpenAttribute\Property(property: 'lastname', type: 'string', example: 'Dupont'),
                        new OpenAttribute\Property(property: 'firstname', type: 'string', example: 'Jean'),
                        new OpenAttribute\Property(property: 'email', type: 'string', example: 'jean.dupont@example.com'),
                        new OpenAttribute\Property(property: 'creation_date', type: 'string', format: 'date-time', example: '2024-10-17 20:30:00'),
                        new OpenAttribute\Property(property: 'billing_address', type: 'string', example: '5 rue de la République, 75001 Paris, France'),
                        new OpenAttribute\Property(property: 'phone_number', type: 'string', example: '+33612345678')
                    ])
                ]
            )
        ),
        OpenAttribute\Response(
            response: Response::HTTP_NOT_FOUND,
            description: <<<'EOD'
                <ul>
                    <li>When the customer is not found.</li>
                    <li>When the user is not found.</li>
                </ul>
                EOD,
            content: new OpenAttribute\JsonContent(
                type: 'object',
                properties: [
                    new OpenAttribute\Property(property: 'status', type: 'integer', example: 404),
                    new OpenAttribute\Property(property: 'message', type: 'string', example: 'Aucun client n\'a été trouvé.')
                ]
            )
        )
    ]
    #[Route('/customer/{id<\d+>}/user/{userId<\d+>}', name: 'api_customer_user', methods: [Request::METHOD_GET])]
    public function show(
        int $id,
        int $userId,
        UserRepository $userRepository,
        CustomerRepository $customerRepository,
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

        $user = $userRepository->findOneBy([
            'id' => $userId,
            'customer' => $customer
        ]);
        if (empty($user)) {
            return new JsonResponse(
                [
                    'status' =>
                    Response::HTTP_NOT_FOUND,
                    'message' => 'Aucun utilisateur n\'a été trouvé.'
                ],
                Response::HTTP_NOT_FOUND
            );
        }

        $cacheKey = 'customer_' . $id . '_user_' . $userId;
        $cacheTag = 'customer_data';

        $response = $cache->get($cacheKey, function (ItemInterface $item) use ($user, $serializer, $cacheTag) {
            $item->expiresAfter(3600);
            $item->tag($cacheTag);

            $jsonData = $serializer->serialize($user, 'json', ['groups' => ['user']]);

            return new JsonResponse([
                'status' => Response::HTTP_OK,
                'data' => json_decode($jsonData, true),
            ], Response::HTTP_OK);
        });
        $response->headers->set('Cache-Control', 'public, max-age=3600');

        return $response;
    }
}

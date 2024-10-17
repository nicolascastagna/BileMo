<?php

declare(strict_types=1);

namespace App\Controller\Customer;

use App\Repository\CustomerRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OpenAttribute;

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
    public function show(int $id, int $userId, CustomerRepository $customerRepository): JsonResponse
    {
        $customer = $customerRepository->find($id);

        if (empty($customer)) {
            return new JsonResponse(
                ['status' =>
                Response::HTTP_NOT_FOUND, 'message' =>
                'Aucun client n\'a été trouvé.'],
                Response::HTTP_NOT_FOUND
            );
        }

        $user = $customer->getUser()->get($userId);

        if (empty($user)) {
            return new JsonResponse(
                ['status' =>
                Response::HTTP_NOT_FOUND, 'message' =>
                'Aucun utilisateur n\'a été trouvé.'],
                Response::HTTP_NOT_FOUND
            );
        }

        $data = [
            'id' => $user->getId(),
            'lastname' => $user->getLastname(),
            'firstname' => $user->getFirstname(),
            'email' => $user->getEmail(),
            'creation_date' => $user->getCreationDate()->format('Y-m-d H:i:s'),
            'billing_address' => $user->getBillingAddress(),
            'phone_number' => $user->getPhoneNumber(),
        ];

        return new JsonResponse([
            'status' => Response::HTTP_OK,
            'data' => $data
        ], Response::HTTP_OK);
    }
}

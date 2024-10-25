<?php

declare(strict_types=1);

namespace App\Controller\Customer;

use App\Entity\User;
use App\Repository\CustomerRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use OpenApi\Attributes as OpenAttribute;

class CustomerAddController extends AbstractController
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
        OpenAttribute\RequestBody(
            description: 'User data for creating a new user linked to a customer',
            required: true,
            content: new OpenAttribute\JsonContent(
                type: 'object',
                required: ['lastname', 'firstname', 'email', 'password', 'billing_address', 'phone_number'],
                properties: [
                    new OpenAttribute\Property(property: 'lastname', type: 'string', example: 'Dupont'),
                    new OpenAttribute\Property(property: 'firstname', type: 'string', example: 'Jean'),
                    new OpenAttribute\Property(property: 'email', type: 'string', example: 'jean.dupont@example.com'),
                    new OpenAttribute\Property(property: 'password', type: 'string', format: 'password', example: 'motdepassesecuriser123'),
                    new OpenAttribute\Property(property: 'billing_address', type: 'string', example: '5 rue de la République, 75001 Paris, France'),
                    new OpenAttribute\Property(property: 'phone_number', type: 'string', example: '+33612345678')
                ]
            )
        ),
        OpenAttribute\Response(
            response: Response::HTTP_CREATED,
            description: 'User successfully created',
            content: new OpenAttribute\JsonContent(
                type: 'object',
                properties: [
                    new OpenAttribute\Property(property: 'status', type: 'integer', example: 201),
                    new OpenAttribute\Property(property: 'data', type: 'object', properties: [
                        new OpenAttribute\Property(property: 'id', type: 'integer', example: 42),
                        new OpenAttribute\Property(property: 'lastname', type: 'string', example: 'Dupont'),
                        new OpenAttribute\Property(property: 'firstname', type: 'string', example: 'Jean'),
                        new OpenAttribute\Property(property: 'email', type: 'string', example: 'jean.dupont@example.com'),
                        new OpenAttribute\Property(property: 'creation_date', type: 'string', format: 'date-time', example: '2024-10-17 14:30:00'),
                        new OpenAttribute\Property(property: 'billing_address', type: 'string', example: '5 rue de la République, 75001 Paris, France'),
                        new OpenAttribute\Property(property: 'phone_number', type: 'string', example: '+33612345678')
                    ])
                ]
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
        OpenAttribute\Response(
            response: Response::HTTP_CONFLICT,
            description: 'Email already used',
            content: new OpenAttribute\JsonContent(
                type: 'object',
                properties: [
                    new OpenAttribute\Property(property: 'status', type: 'integer', example: 409),
                    new OpenAttribute\Property(property: 'message', type: 'string', example: 'Cette email est déjà utilisé.')
                ]
            )
        ),
        OpenAttribute\Response(
            response: Response::HTTP_INTERNAL_SERVER_ERROR,
            description: 'Internal server error',
            content: new OpenAttribute\JsonContent(
                type: 'object',
                properties: [
                    new OpenAttribute\Property(property: 'status', type: 'integer', example: 500),
                    new OpenAttribute\Property(property: 'error', type: 'string', example: 'Internal server error')
                ]
            )
        )
    ]
    #[Route('/customer/{id<\d+>}/user/new', name: 'api_customer_user_new', methods: [Request::METHOD_POST])]
    public function add(
        int $id,
        Request $request,
        CustomerRepository $customerRepository,
        UserRepository $userRepository,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager,
        TagAwareCacheInterface $cache
    ): JsonResponse {
        $customer = $customerRepository->find($id);

        if (empty($customer)) {
            return new JsonResponse(
                [
                    'status' => Response::HTTP_NOT_FOUND,
                    'message' => 'Aucun client n\'a été trouvé.'
                ],
                Response::HTTP_NOT_FOUND
            );
        }

        $data = json_decode($request->getContent(), true);

        $findEmailUser = $userRepository->findOneBy([
            'email' => $data['email'],
        ]);
        if (!empty($findEmailUser)) {
            throw new ConflictHttpException('Cette email est déjà utilisé.');
        }

        $user = new User();
        $user->setLastname($data['lastname']);
        $user->setFirstname($data['firstname']);
        $user->setPassword($passwordHasher->hashPassword($user, $data['password']));
        $user->setEmail($data['email']);
        $user->setCreationDate(new \DateTime());
        $user->setBillingAddress($data['billing_address']);
        $user->setPhoneNumber($data['phone_number']);

        $customer->addUser($user);

        try {
            $entityManager->persist($user);
            $entityManager->flush();

            $cacheTag = 'customer_data';
            $cache->invalidateTags([$cacheTag]);
        } catch (Exception $exception) {
            throw new HttpException(500, json_encode([
                'error' => $exception->getMessage()
            ]), $exception);
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
            'status' => Response::HTTP_CREATED,
            'data' => $data
        ], Response::HTTP_CREATED);
    }
}

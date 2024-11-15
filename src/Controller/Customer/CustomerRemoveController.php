<?php

declare(strict_types=1);

namespace App\Controller\Customer;

use App\Repository\CustomerRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\SecurityBundle\Security as SecurityBundleSecurity;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use OpenApi\Attributes as OpenAttribute;

class CustomerRemoveController extends AbstractController
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
            description: 'User successfully removed',
            content: new OpenAttribute\JsonContent(
                type: 'object',
                properties: [
                    new OpenAttribute\Property(property: 'status', type: 'integer', example: 200),
                    new OpenAttribute\Property(property: 'message', type: 'string', example: 'Utilisateur supprimé avec succès.')
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
    #[Route('/customer/{id<\d+>}/user/{userId<\d+>}', name: 'api_customer_user_remove', methods: [Request::METHOD_DELETE])]
    public function remove(
        int $id,
        int $userId,
        CustomerRepository $customerRepository,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager,
        TagAwareCacheInterface $cache,
        SecurityBundleSecurity $security
    ): JsonResponse {
        $currentCustomer = $security->getUser();

        if ($currentCustomer && $currentCustomer->getId() !== $id) {
            return new JsonResponse(
                [
                    'status' => Response::HTTP_FORBIDDEN,
                    'message' => "Vous n'êtes pas autorisé à effectuer cette action."
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
                    'status' => Response::HTTP_NOT_FOUND,
                    'message' => 'Aucun utilisateur n\'a été trouvé.'
                ],
                Response::HTTP_NOT_FOUND
            );
        }

        $customer->removeUser($user);

        try {
            $entityManager->remove($user);
            $entityManager->flush();

            $cacheTag = 'customer_data';
            $cache->invalidateTags([$cacheTag]);

            return new JsonResponse(null, Response::HTTP_NO_CONTENT);
        } catch (Exception $exception) {
            throw new HttpException(500, json_encode(['error' => $exception->getMessage()]), $exception);
        }
    }
}

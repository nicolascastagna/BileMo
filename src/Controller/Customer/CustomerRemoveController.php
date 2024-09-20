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

#[Route('/api')]
class CustomerRemoveController extends AbstractController
{
    #[Route('/customer/{id<\d+>}/user/{userId<\d+>}', name: 'api_customer_user_remove', methods: [Request::METHOD_DELETE])]
    public function remove(
        int $id,
        int $userId,
        CustomerRepository $customerRepository,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager,
    ): JsonResponse {
        $customer = $customerRepository->find($id);

        if (empty($customer)) {
            return new JsonResponse(
                ['status' =>
                Response::HTTP_NOT_FOUND, 'message' =>
                'Aucun client n\'a été trouvé.'],
                Response::HTTP_NOT_FOUND
            );
        }

        $user = $userRepository->findOneBy([
            'id' => $userId,
            'customer' => $customer
        ]);;

        if (empty($user)) {
            return new JsonResponse(
                ['status' =>
                Response::HTTP_NOT_FOUND, 'message' =>
                'Aucun utilisateur n\'a été trouvé.'],
                Response::HTTP_NOT_FOUND
            );
        }

        $customer->removeUser($user);

        try {
            $entityManager->remove($user);
            $entityManager->flush();

            return new JsonResponse(['status' => Response::HTTP_OK, 'message' => 'Utilisateur supprimé avec succès.'], Response::HTTP_OK);
        } catch (Exception $exception) {
            throw new HttpException(500, json_encode(['error' => $exception->getMessage()]), $exception);
        }
    }
}

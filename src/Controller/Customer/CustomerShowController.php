<?php

declare(strict_types=1);

namespace App\Controller\Customer;

use App\Repository\CustomerRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CustomerShowController extends AbstractController
{
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

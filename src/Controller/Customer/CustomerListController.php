<?php

declare(strict_types=1);

namespace App\Controller\Customer;

use App\Repository\CustomerRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class CustomerListController extends AbstractController
{
    #[Route('/customer/{id<\d+>}/user', name: 'api_customer_users', methods: [Request::METHOD_GET])]
    public function list(int $id, CustomerRepository $customerRepository, SerializerInterface $serializer): JsonResponse
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

        $data = [
            'id' => $customer->getId(),
            'company' => $customer->getCompany(),
            'siret' => $customer->getSiret(),
            'email' => $customer->getEmail(),
            'head_office' => $customer->getHeadOffice(),
            'users' => $customer->getUser()
        ];

        $newData = $serializer->serialize($data, 'json', ['groups' => 'user']);

        return new JsonResponse([
            'status' => Response::HTTP_OK,
            'data' => json_decode($newData)
        ], Response::HTTP_OK);
    }
}

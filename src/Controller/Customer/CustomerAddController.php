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

class CustomerAddController extends AbstractController
{
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
                ['status' =>
                Response::HTTP_NOT_FOUND, 'message' =>
                'Aucun client n\'a été trouvé.'],
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
            throw new HttpException(500, json_encode(['error' => $exception->getMessage()]), $exception);
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

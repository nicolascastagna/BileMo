<?php

namespace App\Controller;

use App\Entity\Customer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;

class AuthController extends AbstractController
{
    /**
     * Login
     *
     * @OA\Response(
     *     response=200,
     *     description="Returns user information",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=Customer::class))
     *     )
     * )
     * @OA\RequestBody(
     *     required=true,
     *     @OA\MediaType(
     *       mediaType="application/json",
     *       @OA\Schema(
     *         @OA\Property(
     *           property="username",
     *           description="Email address of the user.",
     *           type="string",
     *         ),
     *         @OA\Property(
     *           property="password",
     *           description="Password of the user.",
     *           type="string",
     *         ),
     *       ),
     *     ),
     * ),
     * @OA\Tag(name="Authentication")
     */
    #[Route('/login', name: 'fake_login', methods: [Request::METHOD_POST])]
    public function login(Request $request): void {}
}

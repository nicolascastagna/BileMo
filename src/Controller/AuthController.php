<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OpenAttribute;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends AbstractController
{
    #[
        OpenAttribute\Tag(name: 'Auth'),
        OpenAttribute\Response(
            response: Response::HTTP_OK,
            description: 'Login successful, JWT token returned',
            content: new OpenAttribute\JsonContent(
                type: 'object',
                properties: [
                    new OpenAttribute\Property(property: 'token', type: 'string')
                ]
            )
        ),
        OpenAttribute\Response(
            response: Response::HTTP_UNAUTHORIZED,
            description: 'Invalid credentials',
            content: new OpenAttribute\JsonContent(
                type: 'object',
                properties: [
                    new OpenAttribute\Property(property: 'status', type: 'integer', example: 401),
                    new OpenAttribute\Property(property: 'message', type: 'string', example: 'Invalid credentials')
                ]
            )
        ),
        OpenAttribute\RequestBody(
            description: 'Login credentials',
            required: true,
            content: new OpenAttribute\JsonContent(
                type: 'object',
                required: ['username', 'password'],
                properties: [
                    new OpenAttribute\Property(property: 'username', type: 'string'),
                    new OpenAttribute\Property(property: 'password', type: 'string', format: 'password')
                ]
            )
        )
    ]
    #[Route('/login', name: 'login', methods: [Request::METHOD_POST])]
    public function login(Request $request): void {}
}

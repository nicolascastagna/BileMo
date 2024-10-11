<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use OpenApi\Annotations\Attribute;

class AuthController extends AbstractController
{
    #[OA\Post(
        path: '/api/login',
        summary: 'Login and obtain JWT token',
        description: 'Logs in a user and returns a JWT token for authentication',
        requestBody: new OA\RequestBody(
            description: 'Login credentials',
            required: true,
            content: new OA\JsonContent(
                type: 'object',
                required: ['username', 'password'],
                properties: [
                    new OA\Property(property: 'username', type: 'string'),
                    new OA\Property(property: 'password', type: 'string', format: 'password')
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Login successful, JWT token returned',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'token', type: 'string')
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Invalid credentials'
            )
        ],
        tags: ['Auth']
    )]
    #[Route('/login', name: 'fake_login', methods: [Request::METHOD_POST])]
    public function login(Request $request): void {}
}

<?php

declare(strict_types=1);

namespace App\Interfaces;

use Symfony\Component\HttpFoundation\Request;

interface ProductListServiceInterface
{
    public function getPaginatedProducts(Request $request): array;
}

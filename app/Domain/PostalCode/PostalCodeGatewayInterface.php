<?php

namespace App\Domain\PostalCode;

interface PostalCodeGatewayInterface
{
    public function lookup(string $postalCode): array;
}

<?php

namespace App\Contracts;

use App\Models\TenantIntegration;

interface IntegrationAdapterInterface
{
    public function provider(): string;

    public function category(): string;

    public function testConnection(TenantIntegration $integration): bool;
}

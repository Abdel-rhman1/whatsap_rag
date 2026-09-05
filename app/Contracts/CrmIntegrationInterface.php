<?php

namespace App\Contracts;

use App\Models\TenantIntegration;

interface CrmIntegrationInterface extends IntegrationAdapterInterface
{
    /**
     * Create or update contact profile in CRM.
     */
    public function createOrUpdateContact(TenantIntegration $integration, array $contactData): array;

    /**
     * Create support ticket or deal in CRM.
     */
    public function createTicket(TenantIntegration $integration, array $ticketData): array;

    /**
     * Get contact details by phone or email.
     */
    public function getContact(TenantIntegration $integration, string $identifier): ?array;
}

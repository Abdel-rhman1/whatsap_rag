<?php

namespace App\Services;

use App\Contracts\WhatsAppProviderInterface;
use App\Models\WhatsAppAccount;
use App\Models\WhatsappInstance;
use Illuminate\Support\Facades\Log;
use Exception;

class WhatsAppService
{
    /**
     * Send a WhatsApp text message through the tenant's configured provider.
     */
    public function sendMessage(string|WhatsAppAccount $instanceOrAccount, string $to, string $message, int $timeout = 35): bool
    {
        $account  = $this->resolveAccount($instanceOrAccount);
        $provider = WhatsAppProviderFactory::make($account);

        return $provider->sendMessage($account, $to, $message, $timeout);
    }

    /**
     * Send a template message.
     */
    public function sendTemplate(string|WhatsAppAccount $instanceOrAccount, string $to, string $templateName, array $parameters = [], string $language = 'en'): bool
    {
        $account  = $this->resolveAccount($instanceOrAccount);
        $provider = WhatsAppProviderFactory::make($account);

        return $provider->sendTemplate($account, $to, $templateName, $parameters, $language);
    }

    /**
     * Send media.
     */
    public function sendMedia(string|WhatsAppAccount $instanceOrAccount, string $to, string $mediaUrl, string $mediaType, ?string $caption = null): bool
    {
        $account  = $this->resolveAccount($instanceOrAccount);
        $provider = WhatsAppProviderFactory::make($account);

        return $provider->sendMedia($account, $to, $mediaUrl, $mediaType, $caption);
    }

    /**
     * Start/initialize instance session.
     */
    public function startInstance(string|WhatsAppAccount $instanceOrAccount): bool
    {
        $account  = $this->resolveAccount($instanceOrAccount);
        $provider = WhatsAppProviderFactory::make($account);

        return $provider->startInstance($account);
    }

    /**
     * Resolve account model from ID, identifier string, or object.
     */
    public function resolveAccount(string|int|WhatsAppAccount $instanceOrAccount): WhatsAppAccount
    {
        if ($instanceOrAccount instanceof WhatsAppAccount) {
            return $instanceOrAccount;
        }

        if (is_numeric($instanceOrAccount)) {
            $account = WhatsAppAccount::withoutGlobalScopes()->find($instanceOrAccount);
            if ($account) return $account;
        }

        // Try looking up by account_identifier or instance_name
        $account = WhatsAppAccount::withoutGlobalScopes()
            ->where('account_identifier', (string)$instanceOrAccount)
            ->first();

        if ($account) {
            return $account;
        }

        // Create on-the-fly fallback account for backward compatibility if an instance exists
        $instance = WhatsappInstance::withoutGlobalScopes()->where('instance_name', (string)$instanceOrAccount)->first();
        $tenantId = $instance->tenant_id ?? TenantContext::id() ?? 1;

        return WhatsAppAccount::withoutGlobalScopes()->firstOrCreate([
            'account_identifier' => (string)$instanceOrAccount,
        ], [
            'tenant_id'    => $tenantId,
            'name'         => 'Account ' . $instanceOrAccount,
            'provider'     => 'baileys',
            'phone_number' => $instance->phone_number ?? null,
            'status'       => 'connected',
            'is_active'    => true,
        ]);
    }
}

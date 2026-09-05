<?php

namespace App\Services;

use App\Contracts\WhatsAppProviderInterface;
use App\Models\WhatsAppAccount;
use App\Services\WhatsApp\BaileysGatewayProvider;
use App\Services\WhatsApp\MetaCloudApiProvider;
use App\Services\WhatsApp\TwilioWhatsAppProvider;
use InvalidArgumentException;

class WhatsAppProviderFactory
{
    /**
     * Create the appropriate WhatsApp provider for a given WhatsApp account.
     */
    public static function make(WhatsAppAccount $account): WhatsAppProviderInterface
    {
        return match ($account->provider) {
            'meta'    => new MetaCloudApiProvider(),
            'twilio'  => new TwilioWhatsAppProvider(),
            'baileys' => new BaileysGatewayProvider(),
            default   => new BaileysGatewayProvider(),
        };
    }

    /**
     * Create provider by provider driver name string.
     */
    public static function makeByDriver(string $driver): WhatsAppProviderInterface
    {
        return match ($driver) {
            'meta'    => new MetaCloudApiProvider(),
            'twilio'  => new TwilioWhatsAppProvider(),
            'baileys' => new BaileysGatewayProvider(),
            default   => new BaileysGatewayProvider(),
        };
    }
}

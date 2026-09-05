<?php

namespace App\Filament\Widgets;

use App\Models\Device;
use App\Models\WhatsappSession;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DeviceStatus extends BaseWidget
{
    protected static ?string $pollingInterval = '10s';

    protected function getStats(): array
    {
        $onlineDevices = Device::where('status', 'online')->count();
        $totalDevices = Device::count();
        
        $connectedSessions = WhatsappSession::where('status', 'connected')->count();
        
        return [
            Stat::make('Devices Online', "{$onlineDevices} / {$totalDevices}")
                ->description('Active hardware gateways')
                ->color($onlineDevices > 0 ? 'success' : 'danger'),
                
            Stat::make('WhatsApp Sessions', $connectedSessions)
                ->description('Active Baileys connections')
                ->color($connectedSessions > 0 ? 'success' : 'danger'),
        ];
    }
}

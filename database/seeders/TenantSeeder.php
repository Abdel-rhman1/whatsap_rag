<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TenantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tenant1 = \App\Models\Tenant::create([
            'name' => 'Demo Tenant 1',
            'widget_key' => 'demo-key-1',
        ]);

        \App\Models\User::create([
            'name' => 'Admin 1',
            'email' => 'admin1@demo.com',
            'password' => bcrypt('password'),
            'tenant_id' => $tenant1->id,
        ]);

        $tenant1->widgetSetting()->create([
            'theme' => 'dark',
            'primary_color' => '#1a73e8',
            'greeting_message' => 'Hello from Tenant 1!',
        ]);

        $tenant2 = \App\Models\Tenant::create([
            'name' => 'Demo Tenant 2',
            'widget_key' => 'demo-key-2',
        ]);

        \App\Models\User::create([
            'name' => 'Admin 2',
            'email' => 'admin2@demo.com',
            'password' => bcrypt('password'),
            'tenant_id' => $tenant2->id,
        ]);

        $tenant2->widgetSetting()->create([
            'theme' => 'light',
            'primary_color' => '#ff5722',
            'greeting_message' => 'Welcome to Tenant 2!',
        ]);
    }
}

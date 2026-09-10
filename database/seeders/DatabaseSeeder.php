<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\AdminUser;
use App\Models\Plan;
use App\Models\Tenant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create Default Plans
        $plans = [
            [
                'name' => 'Free Tier',
                'slug' => 'free',
                'monthly_price' => 0,
                'yearly_price' => 0,
                'limits' => [
                    'api_requests' => 1000,
                    'whatsapp_sessions' => 1,
                    'webhooks' => 1,
                    'knowledge_sources' => 5
                ]
            ],
            [
                'name' => 'Pro Plan',
                'slug' => 'pro',
                'monthly_price' => 49,
                'yearly_price' => 490,
                'limits' => [
                    'api_requests' => 50000,
                    'whatsapp_sessions' => 10,
                    'webhooks' => 20,
                    'knowledge_sources' => 50
                ]
            ],
            [
                'name' => 'Enterprise',
                'slug' => 'enterprise',
                'monthly_price' => 199,
                'yearly_price' => 1990,
                'limits' => [
                    'api_requests' => 1000000,
                    'whatsapp_sessions' => 100,
                    'webhooks' => 500,
                    'knowledge_sources' => 1000
                ]
            ]
        ];

        foreach ($plans as $planData) {
            Plan::firstOrCreate(['slug' => $planData['slug']], $planData);
        }

        $freePlan = Plan::where('slug', 'free')->first();
        $proPlan = Plan::where('slug', 'pro')->first();

        // 2. Create Admin User
        AdminUser::firstOrCreate(
            ['email' => 'admin@raghub.com'],
            [
                'name' => 'System Admin',
                'password' => Hash::make('password123'),
                'role' => 'super_admin',
                'is_active' => true
            ]
        );

        // 3. Create Demo Tenants
        $demoTenant = Tenant::firstOrCreate(
            ['email' => 'demo@raghub.com'],
            [
                'name' => 'Demo Startup',
                'password' => Hash::make('password123'),
                'status' => 'active',
                'plan_id' => $proPlan->id,
                'widget_key' => Str::random(32),
                'qdrant_collection' => 'demo_collection'
            ]
        );

        $testTenant = Tenant::firstOrCreate(
            ['email' => 'test@raghub.com'],
            [
                'name' => 'Test Corp',
                'password' => Hash::make('password123'),
                'status' => 'active',
                'plan_id' => $freePlan->id,
                'widget_key' => Str::random(32),
                'qdrant_collection' => 'test_collection'
            ]
        );

        // 4. Create Demo Team Members for Tenants
        $demoUsers = [
            [
                'name' => 'Sarah Jenkins',
                'email' => 'sarah@demo.com',
                'password' => Hash::make('password123'),
                'tenant_id' => $demoTenant->id,
                'role' => 'agent',
                'is_active' => true,
                'last_login_at' => now()->subHours(2),
            ],
            [
                'name' => 'Michael Chen',
                'email' => 'michael@demo.com',
                'password' => Hash::make('password123'),
                'tenant_id' => $demoTenant->id,
                'role' => 'analyst',
                'is_active' => true,
                'last_login_at' => now()->subDays(1),
            ],
            [
                'name' => 'Alex Rivera',
                'email' => 'alex@demo.com',
                'password' => Hash::make('password123'),
                'tenant_id' => $demoTenant->id,
                'role' => 'billing',
                'is_active' => true,
                'last_login_at' => now()->subDays(3),
            ],
            [
                'name' => 'David Miller',
                'email' => 'david@test.com',
                'password' => Hash::make('password123'),
                'tenant_id' => $testTenant->id,
                'role' => 'agent',
                'is_active' => true,
                'last_login_at' => now()->subHours(5),
            ]
        ];

        foreach ($demoUsers as $userData) {
            User::firstOrCreate(['email' => $userData['email']], $userData);
        }
    }
}

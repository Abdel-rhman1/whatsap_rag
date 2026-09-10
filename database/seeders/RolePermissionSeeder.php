<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // Users module
            [
                'name' => 'users.view',
                'label' => 'View Team Members',
                'module' => 'users',
                'description' => 'Allows viewing all team members and their activity status.',
                'risk_level' => 'low',
            ],
            [
                'name' => 'users.create',
                'label' => 'Invite Members',
                'module' => 'users',
                'description' => 'Allows inviting new team members to the tenant workspace.',
                'risk_level' => 'medium',
            ],
            [
                'name' => 'users.edit',
                'label' => 'Edit Member Roles',
                'module' => 'users',
                'description' => 'Allows changing user roles and access status.',
                'risk_level' => 'high',
            ],
            [
                'name' => 'users.delete',
                'label' => 'Remove Members',
                'module' => 'users',
                'description' => 'Allows removing or deactivating team members.',
                'risk_level' => 'danger',
            ],

            // Conversations module
            [
                'name' => 'conversations.view',
                'label' => 'View Conversations',
                'module' => 'conversations',
                'description' => 'Allows reading customer conversation threads and chat logs.',
                'risk_level' => 'low',
            ],
            [
                'name' => 'conversations.reply',
                'label' => 'Reply to Conversations',
                'module' => 'conversations',
                'description' => 'Allows sending messages directly to customers via chat/WhatsApp.',
                'risk_level' => 'medium',
            ],
            [
                'name' => 'conversations.escalate',
                'label' => 'Manage Escalations',
                'module' => 'conversations',
                'description' => 'Allows taking over human fallback and bot escalation requests.',
                'risk_level' => 'medium',
            ],
            [
                'name' => 'conversations.export',
                'label' => 'Export Conversations',
                'module' => 'conversations',
                'description' => 'Allows exporting customer conversation logs and chat transcripts.',
                'risk_level' => 'high',
            ],

            // WhatsApp module
            [
                'name' => 'whatsapp.view',
                'label' => 'View WhatsApp Sessions',
                'module' => 'whatsapp',
                'description' => 'Allows viewing QR codes, session states, and connected numbers.',
                'risk_level' => 'low',
            ],
            [
                'name' => 'whatsapp.manage',
                'label' => 'Manage WhatsApp Sessions',
                'module' => 'whatsapp',
                'description' => 'Allows creating, restarting, and deleting WhatsApp session instances.',
                'risk_level' => 'high',
            ],
            [
                'name' => 'whatsapp.templates',
                'label' => 'Manage WhatsApp Templates',
                'module' => 'whatsapp',
                'description' => 'Allows creating and editing pre-defined WhatsApp message templates.',
                'risk_level' => 'medium',
            ],

            // Knowledge module
            [
                'name' => 'knowledge.view',
                'label' => 'View Knowledge Base',
                'module' => 'knowledge',
                'description' => 'Allows inspecting ingested documents, vector collections, and chunks.',
                'risk_level' => 'low',
            ],
            [
                'name' => 'knowledge.manage',
                'label' => 'Manage Knowledge Base',
                'module' => 'knowledge',
                'description' => 'Allows uploading documents, training AI embeddings, and deleting sources.',
                'risk_level' => 'high',
            ],

            // SLA module
            [
                'name' => 'sla.view',
                'label' => 'View SLA & Analytics',
                'module' => 'sla',
                'description' => 'Allows reviewing agent performance, response times, and SLA compliance.',
                'risk_level' => 'low',
            ],
            [
                'name' => 'sla.export',
                'label' => 'Export SLA Reports',
                'module' => 'sla',
                'description' => 'Allows downloading SLA compliance and response metrics reports.',
                'risk_level' => 'medium',
            ],
            [
                'name' => 'sla.manage',
                'label' => 'Configure SLA Policies',
                'module' => 'sla',
                'description' => 'Allows setting response time targets, escalation thresholds, and priorities.',
                'risk_level' => 'high',
            ],

            // Campaigns module
            [
                'name' => 'campaigns.view',
                'label' => 'View Campaigns',
                'module' => 'campaigns',
                'description' => 'Allows viewing broadcast campaigns and message delivery status.',
                'risk_level' => 'low',
            ],
            [
                'name' => 'campaigns.manage',
                'label' => 'Manage Campaigns',
                'module' => 'campaigns',
                'description' => 'Allows launching, pausing, and broadcasting marketing campaigns.',
                'risk_level' => 'high',
            ],

            // Widget module
            [
                'name' => 'widget.view',
                'label' => 'View Widget Settings',
                'module' => 'widget',
                'description' => 'Allows viewing chat widget snippet, styling, and configuration.',
                'risk_level' => 'low',
            ],
            [
                'name' => 'widget.manage',
                'label' => 'Manage Widget Settings',
                'module' => 'widget',
                'description' => 'Allows modifying widget themes, greetings, security, and allowed domains.',
                'risk_level' => 'medium',
            ],

            // API Keys & Webhooks
            [
                'name' => 'api_keys.manage',
                'label' => 'Manage API Keys',
                'module' => 'api',
                'description' => 'Allows generating, rotating, and revoking REST API keys.',
                'risk_level' => 'danger',
            ],
            [
                'name' => 'webhooks.manage',
                'label' => 'Manage Webhooks',
                'module' => 'api',
                'description' => 'Allows registering external webhook listener endpoints and secret tokens.',
                'risk_level' => 'high',
            ],

            // Billing module
            [
                'name' => 'billing.view',
                'label' => 'View Billing & Invoices',
                'module' => 'billing',
                'description' => 'Allows checking current plan limits, billing cycles, and invoice history.',
                'risk_level' => 'low',
            ],
            [
                'name' => 'billing.manage',
                'label' => 'Manage Billing & Subscription',
                'module' => 'billing',
                'description' => 'Allows upgrading subscriptions, changing payment methods, and cancelling.',
                'risk_level' => 'high',
            ],

            // Settings module
            [
                'name' => 'settings.view',
                'label' => 'View System Settings',
                'module' => 'settings',
                'description' => 'Allows viewing workspace system parameters and configuration.',
                'risk_level' => 'low',
            ],
            [
                'name' => 'settings.manage',
                'label' => 'Manage Security & Roles',
                'module' => 'settings',
                'description' => 'Allows modifying tenant permission policies, security controls, and roles.',
                'risk_level' => 'danger',
            ],
        ];

        $createdPermissions = [];
        foreach ($permissions as $perm) {
            $createdPermissions[$perm['name']] = Permission::updateOrCreate(
                ['name' => $perm['name']],
                $perm
            );
        }

        // Standard System Roles
        $roles = [
            [
                'name' => 'Tenant Administrator',
                'slug' => 'tenant_admin',
                'description' => 'Full administrative access to all workspace features, settings, and team members.',
                'color' => '#6366f1',
                'is_system' => true,
                'tenant_id' => null,
                'permissions' => array_keys($createdPermissions), // all
            ],
            [
                'name' => 'Support Agent',
                'slug' => 'agent',
                'description' => 'Handles live customer chats, WhatsApp conversations, and views the knowledge base.',
                'color' => '#10b981',
                'is_system' => true,
                'tenant_id' => null,
                'permissions' => [
                    'conversations.view',
                    'conversations.reply',
                    'conversations.escalate',
                    'knowledge.view',
                    'whatsapp.view',
                    'sla.view',
                ],
            ],
            [
                'name' => 'Data Analyst',
                'slug' => 'analyst',
                'description' => 'Analyzes SLA metrics, exports conversation logs, and reviews performance reports.',
                'color' => '#f59e0b',
                'is_system' => true,
                'tenant_id' => null,
                'permissions' => [
                    'users.view',
                    'conversations.view',
                    'conversations.export',
                    'sla.view',
                    'sla.export',
                    'campaigns.view',
                ],
            ],
            [
                'name' => 'Billing Specialist',
                'slug' => 'billing',
                'description' => 'Manages subscription plans, invoices, and billing contact details.',
                'color' => '#ec4899',
                'is_system' => true,
                'tenant_id' => null,
                'permissions' => [
                    'billing.view',
                    'billing.manage',
                    'settings.view',
                ],
            ],
        ];

        foreach ($roles as $roleData) {
            $perms = $roleData['permissions'];
            unset($roleData['permissions']);

            $role = Role::updateOrCreate(
                ['tenant_id' => null, 'slug' => $roleData['slug']],
                $roleData
            );

            $permissionIds = [];
            foreach ($perms as $pName) {
                if (isset($createdPermissions[$pName])) {
                    $permissionIds[] = $createdPermissions[$pName]->id;
                }
            }

            $role->permissions()->sync($permissionIds);
        }
    }
}

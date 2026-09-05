<?php

namespace App\Policies;

use App\Models\KnowledgeSource;
use App\Models\User;

class KnowledgeSourcePolicy
{
    public function viewAny(User $user): bool
    {
        return true; // Filtered by Global Scope
    }

    public function view(User $user, KnowledgeSource $knowledgeSource): bool
    {
        return $user->tenant_id === $knowledgeSource->tenant_id;
    }

    public function create(User $user): bool
    {
        return $user->tenant_id !== null;
    }

    public function update(User $user, KnowledgeSource $knowledgeSource): bool
    {
        return $user->tenant_id === $knowledgeSource->tenant_id;
    }

    public function delete(User $user, KnowledgeSource $knowledgeSource): bool
    {
        return $user->tenant_id === $knowledgeSource->tenant_id;
    }
}

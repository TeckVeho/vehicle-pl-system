<?php

namespace Tests\Unit\Vpl;

use App\Models\User;
use App\Services\Vpl\UserSyncService;
use Tests\TestCase;

class UserSyncServiceTest extends TestCase
{
    public function test_map_role_matches_first_spatie_role_in_role_map_order(): void
    {
        $user = new User();
        $user->setRelation('roles', collect([
            (object) ['name' => 'crew'],
            (object) ['name' => 'dx_manager'],
        ]));

        $svc = new UserSyncService();
        $this->assertSame('CREW', $svc->mapRole($user));
    }

    public function test_map_role_returns_null_when_no_mapping(): void
    {
        $user = new User();
        $user->setRelation('roles', collect([
            (object) ['name' => 'unknown_role_xyz'],
        ]));

        $svc = new UserSyncService();
        $this->assertNull($svc->mapRole($user));
    }

    public function test_map_role_uses_role_column_when_spatie_roles_do_not_match_map(): void
    {
        $user = new User();
        $user->role = 'accounting';
        $user->setRelation('roles', collect([
            (object) ['name' => 'unknown_role_xyz'],
        ]));

        $svc = new UserSyncService();
        $this->assertSame('経理財務', $svc->mapRole($user));
    }

    public function test_map_role_fallback_when_spatie_roles_empty_uses_column(): void
    {
        $user = new User();
        $user->role = 'accounting';
        $user->setRelation('roles', collect([]));

        $svc = new UserSyncService();
        $this->assertSame('経理財務', $svc->mapRole($user));
    }
}

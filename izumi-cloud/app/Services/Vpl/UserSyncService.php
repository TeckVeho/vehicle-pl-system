<?php

namespace App\Services\Vpl;

use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * Transform IC User data → VPL POST /api/users/sync payload.
 *
 * Mapping reference: ic-sync-field-mapping.md §1
 *
 * VPL VALID_ROLES:
 *   CREW, 事務員, TL, 事業部, 人事労務, 総務広報, 経理財務, 品質管理,
 *   営業, 現場MG, 本社MG, 部長, 執行役員, 取締役, DX, DX管理者
 */
class UserSyncService
{
    /**
     * IC Role name (Spatie role.name, stored in `roles` table)
     * → VPL VALID_ROLES string
     *
     * Source: izumi-cloud/app/constants.php (ROLE_* constants)
     */
    protected const ROLE_MAP = [
        'crew' => 'CREW', // ROLE_CREW
        'clerks' => '事務員', // ROLE_CLERKS
        'tl' => 'TL', // ROLE_TL
        'department_office_staff' => '事業部', // ROLE_DEPARTMENT_OFFICE_STAFF (事業部事務員)
        'personnel_labor' => '人事労務', // ROLE_PERSONNEL_LABOR
        'general_affair' => '総務広報', // ROLE_GENERAL_AFFAIR
        'accounting' => '経理財務', // ROLE_ACCOUNTING
        'quality_control' => '品質管理', // ROLE_QUALITY_CONTROL
        'sales' => '営業', // ROLE_SALES
        'site_manager' => '現場MG', // ROLE_SITE_MANAGER
        'hq_manager' => '本社MG', // ROLE_HQ_MANAGER
        'department_manager' => '部長', // ROLE_DEPARTMENT_MANAGER
        'executive_officer' => '執行役員', // ROLE_EXECUTIVE_OFFICER
        'director' => '取締役', // ROLE_DIRECTOR
        'dx_user' => 'DX', // ROLE_DX_USER
        'dx_manager' => 'DX管理者', // ROLE_DX_MANAGER
        'am_sm' => '現場MG', // ROLE_AM_SM → closest fit
    ];

    protected string $logChannel;

    public function __construct()
    {
        $this->logChannel = config('vpl.log_channel', 'vpl-sync');
    }

    /**
     * Build the full users/sync payload from IC database.
     *
     * @return array{users: array, skipped: array}
     */
    public function buildPayload(): array
    {
        $users = User::query()
            ->with('roles')
            ->get();

        $payload = [];
        $skipped = [];
        $seenEmails = [];

        /** @var User $user */
        foreach ($users as $user) {
            $vplRole = $this->mapRole($user);

            if ($vplRole === null) {
                $skipped[] = [
                    'id'     => $user->id,
                    'name'   => $user->name,
                    'reason' => 'No matching VPL role',
                    'ic_roles' => $user->roles->pluck('name')->toArray(),
                ];
                continue;
            }

            $email = trim($user->email ?? '');
            if ($email === '' || isset($seenEmails[$email])) {
                // Ensure email uniqueness for VPL's @unique constraint
                $email = "user_{$user->id}@izumi-dummy.local";
            }
            $seenEmails[$email] = true;

            $payload[] = [
                'userId'    => (string) $user->id,
                'email'     => $email,
                'name'      => $user->name,
                'role'      => $vplRole,
                'createdAt' => $user->created_at?->toIso8601String(),
                'updatedAt' => $user->updated_at?->toIso8601String(),
                // Password intentionally omitted — VPL defaults to "changeme"
            ];
        }

        return [
            'users' => $payload,
            'skipped' => $skipped,
        ];
    }

    /**
     * Map a single IC User → VPL role string.
     * Uses first matched role. Returns null if no mapping found.
     */
    public function mapRole(User $user): ?string
    {
        $roleNames = $user->roles->pluck('name')->toArray();

        // First matching IC role in ROLE_MAP declaration order (not a strict privilege ladder).
        foreach (self::ROLE_MAP as $icRole => $vplRole) {
            if (in_array($icRole, $roleNames, true)) {
                return $vplRole;
            }
        }

        // Fallback: check if User.role column contains a direct role string
        if ($user->role && isset(self::ROLE_MAP[$user->role])) {
            return self::ROLE_MAP[$user->role];
        }

        return null;
    }

    /**
     * Execute the full sync via VplClient.
     *
     * @return array Summary with synced count, skipped, and VPL response
     */
    public function sync(VplClient $client): array
    {
        $data = $this->buildPayload();

        $total = count($data['users']) + count($data['skipped']);

        if (empty($data['users'])) {
            $this->log('warning', 'No users to sync (all skipped or empty)');
            return [
                'total'    => $total,
                'synced'   => 0,
                'skipped'  => $data['skipped'],
                'response' => null,
            ];
        }

        $this->log('info', 'Sending users/sync', [
            'count' => count($data['users']),
            'skipped' => count($data['skipped']),
        ]);

        $response = $client->post('/api/users/sync', [
            'users' => $data['users'],
        ]);

        if (isset($response['_error']) && $response['_error'] === true) {
            throw new \RuntimeException('Failed to sync users: ' . ($response['_body'] ?? 'Unknown error'));
        }

        $this->log('info', 'users/sync response', ['response' => $response]);

        return [
            'total'    => $total,
            'synced'   => $response['synced'] ?? count($data['users']),
            'skipped'  => $data['skipped'],
            'response' => $response,
        ];
    }

    protected function log(string $level, string $message, array $context = []): void
    {
        Log::channel($this->logChannel)->{$level}("[UserSync] {$message}", $context);
    }
}
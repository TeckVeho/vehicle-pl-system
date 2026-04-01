<?php

namespace App\Repositories;

use App\Models\InspectionNotificationRecipient;
use App\Models\User;
use App\Repositories\Contracts\InspectionNotificationRecipientRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Issue #810 / #826: 車検・定期点検の例外通知者マスタ
 */
class InspectionNotificationRecipientRepository implements InspectionNotificationRecipientRepositoryInterface
{
    /** MG以上のrole（候補者一覧に含める） */
    private const ROLES_MG_OR_ABOVE = [
        ROLE_SITE_MANAGER,
        ROLE_HQ_MANAGER,
        ROLE_DEPARTMENT_MANAGER,
        ROLE_EXECUTIVE_OFFICER,
        ROLE_DIRECTOR,
        ROLE_DX_USER,
        ROLE_DX_MANAGER,
    ];

    /**
     * 候補者一覧（MG以上のユーザ）
     */
    public function getCandidates(): Collection
    {
        return User::whereHas('roles', function ($q) {
            $q->whereIn('name', self::ROLES_MG_OR_ABOVE);
        })
            ->select('id', 'name', 'department_code')
            ->orderBy('name')
            ->get();
    }

    /**
     * 保存済み一覧（拠点・ユーザ付き）
     */
    public function getList(?int $departmentId = null): Collection
    {
        $query = InspectionNotificationRecipient::query()
            ->with(['department:id,name', 'user:id,name'])
            ->orderBy('department_id')
            ->orderBy('user_id');

        if ($departmentId !== null) {
            $query->where('department_id', $departmentId);
        }

        return $query->get();
    }

    /**
     * 一括保存（既存削除の上で recipients で置き換え）
     */
    public function store(array $recipients): Collection
    {
        if (empty($recipients)) {
            InspectionNotificationRecipient::query()->delete();
            return new Collection();
        }

        DB::transaction(function () use ($recipients) {
            InspectionNotificationRecipient::query()->delete();
            foreach ($recipients as $r) {
                InspectionNotificationRecipient::create([
                    'department_id' => $r['department_id'],
                    'user_id' => $r['user_id'],
                ]);
            }
        });

        return InspectionNotificationRecipient::with(['department:id,name', 'user:id,name'])->get();
    }

    /**
     * 通知用マップ取得（department_id => [user_id, ...]）
     */
    public function getForNotificationMap(): array
    {
        $all = InspectionNotificationRecipient::select('department_id', 'user_id')->get();
        $map = [];
        foreach ($all as $row) {
            $deptId = $row->department_id;
            if (!isset($map[$deptId])) {
                $map[$deptId] = [];
            }
            $map[$deptId][] = $row->user_id;
        }
        return $map;
    }
}

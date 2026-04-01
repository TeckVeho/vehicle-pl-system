<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;

/**
 * Interface InspectionNotificationRecipientRepositoryInterface
 *
 * Issue #810 / #826: 車検・定期点検の例外通知者マスタ
 */
interface InspectionNotificationRecipientRepositoryInterface
{
    /**
     * 候補者一覧（MG以上のユーザ）
     *
     * @return Collection
     */
    public function getCandidates(): Collection;

    /**
     * 保存済み一覧（拠点・ユーザ付き）
     *
     * @param int|null $departmentId
     * @return Collection
     */
    public function getList(?int $departmentId = null): Collection;

    /**
     * 一括保存（既存削除の上で recipients で置き換え）
     *
     * @param array $recipients [['department_id' => int, 'user_id' => int], ...]
     * @return Collection 保存後の一覧（department, user 付き）
     */
    public function store(array $recipients): Collection;

    /**
     * 通知用マップ取得（department_id => [user_id, ...]）
     *
     * @return array<int, array<int>>
     */
    public function getForNotificationMap(): array;
}

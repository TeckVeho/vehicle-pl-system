# PR: [BE] VPL集約API・IC→VPL同期・daily-operating整理 / API VPL và đồng bộ IC→VPL

**Issue:** https://github.com/TeckVeho/Izumi_Issue-Requests-Repo/issues/1044

Closes TeckVeho/Izumi_Issue-Requests-Repo#1044

## Tóm tắt triển khai

- **VPL (backend):** Thêm `POST /api/atmtc-transactions/sync` (MASTER), lib `daily-operating-records-sync.ts`, tách/refactor luồng đồng bộ daily operating; contract Vitest mở rộng trong `sync-routes.contract.test.ts`.
- **FE:** Cập nhật nhãn loại sync trên `sync-logs` (ATMTC / driver assignments).
- **IC (cloud):** Mở rộng `VplClient`, builder/service sync ATMTC→VPL, Data Connection **ICL_1044**, lệnh `vpl:sync --entity=atmtc-transactions`, PHPUnit tương ứng — chi tiết trong `docs/issues/Izumi_Issue-Requests-Repo/1044/dev.md`.

## Screenshots

Không có ảnh chụp màn hình (`.png` / `.jpg` / …) trong `evidence/` cho issue này.

## Evidence

**Lưu ý:** Không có `docs/issues/Izumi_Issue-Requests-Repo/1044/evidence/test-results.json` — phần dưới dựa trên `test.md` và log trong `evidence/`.

### 1. Backend Testing (VPL — Vitest)

**Command:**

```bash
cd backend && npx vitest run src/__tests__/sync-routes.contract.test.ts
```

**Result:**

- Theo `evidence/vpl-backend-vitest-sync-routes.log`: **43 tests passed**, 0 failed (Vitest 3.2.4).
- `test.md` (2026-04-10): **43** passed (BE-VPL).

### 2. Backend Testing (IC — PHPUnit)

**Command:**

```bash
cd d:/CtyVeHo/izumi/cloud && php artisan test --filter=AtmtcToVpl
```

**Result:**

- Theo `test.md`: **6** tests PHPUnit liên quan #1044 — **Passed** (chi tiết file test trong `test.md`).
- Log tham chiếu: `evidence/ic-cloud-phpunit-1044.log`.

### 3. Build Verification

**Command:**

```bash
yarn build
```

**Result:**

- **Chưa ghi nhận** output build trong evidence issue #1044; reviewer có thể chạy lại trước merge.

### 4. Type Checking

**Command:**

```bash
npx tsc --noEmit --strict
```

**Result:**

- **Chưa ghi nhận** trong evidence #1044; nên chạy tại root / `backend` theo chuẩn repo.

### 5. Code Linting

**Command:**

```bash
npx eslint src
```

**Result:**

- **Chưa ghi nhận** trong evidence #1044.

### Test Execution Summary

- **Tự động đã có log / báo cáo:** Vitest (VPL) và PHPUnit (IC) như trên và trong `test.md`.
- **Còn thiếu trong evidence:** `test-results.json`, build/tsc/eslint — không tự tạo số liệu giả.

## Tài liệu đính kèm

- `issue.md`, `plan.md`, `dev.md`, `test.md`, `pm-confirm-1044-vpl-atmtc-sync.md` (và bản EN nếu có).

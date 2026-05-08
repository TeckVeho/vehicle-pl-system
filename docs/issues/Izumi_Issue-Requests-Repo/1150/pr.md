# Pull Request — Izumi_Issue-Requests-Repo #1150

**Implementation repository**: `TeckVeho/vehicle-pl-system`  
**Base branch**: `develop`  
**Head branch**: `953-1150-feat-spreadsheet-revenue`  

---

## Closes TeckVeho/Izumi_Issue-Requests-Repo#1150

> Issue nằm repo **Izumi_Issue-Requests-Repo**; PR mở trên **vehicle-pl-system**. Dòng trên để GitHub đóng issue cross-repo sau khi merge.

---

## Summary

Implement UI quản trị **`spreadsheetId` theo từng địa điểm** (Google Sheets), menu MASTER, PATCH API, và cập nhật schema `Location`; cùng các thay đổi đã commit trên nhánh so với `develop` (xem diff với `develop`).

---

## Implementation highlights (#1150 + phụ trợ trên nhánh)

| Khu vực | Thay đổi |
|---------|----------|
| **Frontend** | `src/app/locations/page.tsx`: trang cấu hình spreadsheet (URL hoặc ID, extract ID, PATCH, badge, MASTER-only). `Header.tsx`: mục menu MASTER tới `/locations`. |
| **Backend** | `locations.ts`: `PATCH /api/locations/:id` (MASTER). `schema.prisma`: `spreadsheetId` cho `Location`. |
| **Config / deps** | `backend/package.json`, `package-lock.json`, `.env.example` (theo nhánh hiện tại). |
| **Khác trên nhánh** | Theo `origin/develop..HEAD` có thêm: `google-drive-client`, script smoke Drive, docs issues `953`/`1151`, evidence `1150` — **reviewer nên xác nhận scope** có gộp một PR hay tách. |

Chi tiết: `docs/issues/Izumi_Issue-Requests-Repo/1150/dev.md`.

---

## Screenshots

Không có file ảnh trong `docs/issues/Izumi_Issue-Requests-Repo/1150/evidence/`.

---

## Evidence

⚠️ **Không có** `evidence/test-results.json`.

### 1. Frontend Lint

**Command:**
```bash
npm run lint
```

**Result:** Exit **0**. **2** cảnh báo `react-hooks/exhaustive-deps` (`EditableCell.tsx`, `PLTable.tsx`).

### 2. Frontend production build

**Command:**
```bash
npm run build
```

**Result:** Exit **0**. Route **`/locations`** có trong build.

### 3. Log

`evidence/test_output.log`, `test_output_front_lint.log`, `test_output_front_build.log` — xem `test.md`.

---

## Checklist gợi ý trước merge

- [ ] QA DX/DX管理者: menu → cấu hình spreadsheet → lưu / xóa.
- [ ] DB production: cột `spreadsheetId` (MySQL) theo `dev.md`.

---

## Suggested PR title

```
feat(locations): UI Google Sheet ID per địa điểm — Closes TeckVeho/Izumi_Issue-Requests-Repo#1150
```

---

## Working tree (lúc tạo pr.md)

- `backend/package-lock.json`: **modified, chưa staged** — quyết định add hoặc revert.
- Untracked không liên quan #1150: `.nvmrc`, `atmtc` docs, workspace — **không nên** add hàng loạt nếu không chủ đích.

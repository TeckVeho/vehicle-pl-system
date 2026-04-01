# PR: [FE] CI GitHub Actions — lint / Jest / Mix (#923)

## Liên kết issue

- Issue (repo tracking): https://github.com/TeckVeho/Izumi_Issue-Requests-Repo/issues/923
- Parent: #914

**Đóng issue khi merge (cross-repo):**

Closes TeckVeho/Izumi_Issue-Requests-Repo#923

## Tóm tắt

- Thêm `.github/workflows/frontend-ci.yml`: workflow **Frontend CI** — `npm ci`, `npm run lint`, `npm run test`, `npm run production` trên `ubuntu-latest` (tách tên file với backend/deploy workflows).
- Node từ `.nvmrc` (**14.21.3**), `actions/setup-node` + cache npm.
- Biến `MIX_LARAVEL_TEST_URL` cho Jest trên CI; `NODE_OPTIONS` cho bộ nhớ webpack.
- Trigger: `push` tới `develop` / `staging` / `production` và mọi `pull_request`. Khối `paths` (chỉ chạy khi đổi file FE) **đang comment** để dễ test PR → `develop`; bật lại sau.
- Docs: `docs/issues/Izumi_Issue-Requests-Repo/914/cicd.md` (hướng dẫn khi FE CI fail).

## Evidence

⚠️ **Không có** `docs/issues/.../923/evidence/test-results.json` trong branch này.

### Lint / test / build (tham chiếu local — cần xác nhận trên CI)

| Bước | Ghi chú |
|------|--------|
| `npm run lint` | Nên chạy trên Node 14.21.3 khớp `.nvmrc`. |
| `npm run test` | Toàn suite có thể cần mock Pusher/Echo trong môi trường Jest; nếu đỏ, xử lý theo issue / PR follow-up. |
| `npm run production` | Laravel Mix production build. |

Sau khi merge, kiểm tra tab **Actions** trên `TeckVeho/izumi-cloud`.

## Screenshots

Không có.

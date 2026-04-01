# Issue #923 — Development log (FE CI)

## Parent / tracking

- **GitHub:** [Izumi_Issue-Requests-Repo#923](https://github.com/TeckVeho/Izumi_Issue-Requests-Repo/issues/923)
- **Parent:** #914
- **Code repo:** `TeckVeho/izumi-cloud`

## Approach

- Triển khai trực tiếp theo `plan.md`: workflow GitHub Actions, `.nvmrc`, tài liệu `cicd.md` (parent #914).
- **Không** `git commit` (theo `/dev`).

## Changes (files)

| File | Mô tả |
|------|--------|
| `.nvmrc` | Node **14.21.3** (khớp môi trường dev đã xác nhận). |
| `.github/workflows/frontend-ci.yml` | Job `frontend`: checkout, `setup-node` + `node-version-file` + npm cache, `npm ci`, `lint`, `test`, `production`; `MIX_LARAVEL_TEST_URL`, `NODE_OPTIONS` cho webpack. |
| `docs/issues/Izumi_Issue-Requests-Repo/914/cicd.md` | Hướng dẫn reproduce khi FE CI fail + Node version. |
| `resources/js/pages/DepartmentMaster/edit.vue` | Optional chaining trong `catch` (toast) để tránh crash khi `error.response` thiếu (Jest / môi trường không axios). |
| `resources/js/tests/DepartmentMaster/edit.spec.js` | VueRouter `abstract` + route edit có `params.id`; mock `getEmployeeAll`; stub `BCollapse`; cập nhật selector/assertions theo template hiện tại; bỏ gán `wrapper.vm.$route`. |
| Nhiều file `resources/js/**` | `eslint --fix` (indent Vue/HTML, v.v.) để `npm run lint` pass. |

## Validation (local)

- `npm run lint` — **pass** (sau eslint --fix).
- `npm run production` — **pass** (Laravel Mix).
- `npm run test` — trên **Node v24** trong môi trường agent có lúc **crash native** (Jest `deepCyclicCopyReplaceable` / `isolate_data`). **CI dùng Node 14.21.3** (`.nvmrc`) — cần xác nhận trên runner thật.
- `npx jest resources/js/tests/DepartmentMaster/edit.spec.js --runInBand` — **37/37 pass**.

## Notes

- `npm ci` từng báo lockfile lệch; sau `npm install` trong session dev thì `npm ci` chạy được — nếu PR có thay đổi `package-lock.json`, team review kỹ.
- Nếu CI Jest vẫn lỗi trên Node 14, xem log step **Jest** và đối chiếu `docs/issues/Izumi_Issue-Requests-Repo/914/cicd.md`.

## Next (suggested)

- `/test` / PR: chạy workflow trên GitHub sau khi push branch.
- Issue BE: thêm workflow `backend-ci.yml` (hoặc tên team thống nhất) khi sẵn sàng — tách file với `frontend-ci.yml`.

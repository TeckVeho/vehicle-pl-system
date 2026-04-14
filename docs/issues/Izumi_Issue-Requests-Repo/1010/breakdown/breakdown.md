# Breakdown #1010 — child issues

| Issue | SP | Layer | Description |
|-------|----|-------|-------------|
| [#1043](https://github.com/TeckVeho/Izumi_Issue-Requests-Repo/issues/1043) | 14 | BE (IC) | `GET` ATMTC export → CSV → `atmtc_delivery_data_results`, scheduler, PHPUnit |
| [#1044](https://github.com/TeckVeho/Izumi_Issue-Requests-Repo/issues/1044) | 14 | BE (VPL+IC bridge) | `POST /api/atmtc-transactions/sync`, `VplClient`, mapping, optional daily-summary |
| [#1045](https://github.com/TeckVeho/Izumi_Issue-Requests-Repo/issues/1045) | 8 | FE (PL) | sync-logs labels, daily-summary visibility, Header |

**Project:** [Izumi_Issue](https://github.com/orgs/TeckVeho/projects/4) (`PVT_kwDOCjwUv84Ajq0M`) — child items added via `gh project item-add`.

**SP根拠:** 1 SP ≈ 1h。BE を **IC / VPL+bridge** に分割（合計見積もり >20h のため）。IC=14h相当・VPL層=14h相当・FE=8h相当（既存ラベル `sp:14` / `sp:8`）。

**親 #1010:** Tasklist セクション `Implementation tasks (breakdown)` に同一リンクを注入済み。

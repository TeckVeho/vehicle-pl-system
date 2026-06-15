# Issue #59

**Title:** [FE] 拠点SS設定: フォルダ指定UI・ドロップダウン紐付け  
**URL:** https://github.com/TeckVeho/vehicle-pl-system/issues/59  
**Repository:** TeckVeho/vehicle-pl-system  
**Parent:** [#57](https://github.com/TeckVeho/vehicle-pl-system/issues/57)  
**Labels:** enhancement, frontend

## Summary

拠点スプレッドシート設定（`/locations`）に Drive フォルダ指定 → 一覧取得 → 拠点ごとドロップダウン紐付け UI を追加。保存は既存 `PATCH /api/locations/:id`（`spreadsheetId` = file ID）。

## Dependencies

- Backend [#58](https://github.com/TeckVeho/vehicle-pl-system/issues/58): `GET /api/drive/spreadsheets?folderId=`

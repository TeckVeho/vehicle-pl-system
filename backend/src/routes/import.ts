import { Router, Request, Response } from "express";
import multer from "multer";
import * as XLSX from "xlsx";
import { prisma } from "../lib/prisma.js";
import { accountItemEffectiveWhere } from "../lib/account-item-filter.js";

type DataRow = { vehicleNo: string; accountKey: string; amount: number };

const upload = multer({ storage: multer.memoryStorage() });

function parseCSV(text: string): DataRow[] {
  const lines = text
    .split("\n")
    .map((l) => l.trim())
    .filter(Boolean);
  if (lines.length < 2) return [];
  const rows: DataRow[] = [];
  for (const line of lines.slice(1)) {
    const parts = line.split(",").map((p) => p.trim().replace(/^"|"$/g, ""));
    if (parts.length < 3) continue;
    const [vehicleNo, accountKey, amountStr] = parts;
    rows.push({
      vehicleNo,
      accountKey,
      amount: parseFloat(amountStr.replace(/,/g, "")) || 0,
    });
  }
  return rows;
}

function parseExcel(buffer: Buffer): DataRow[] {
  const workbook = XLSX.read(buffer, { type: "buffer" });
  const sheetName = workbook.SheetNames[0];
  const sheet = workbook.Sheets[sheetName];
  const raw = XLSX.utils.sheet_to_json<unknown[]>(sheet, {
    header: 1,
    defval: "",
  });

  if (raw.length < 2) return [];
  const rows: DataRow[] = [];
  for (const row of raw.slice(1)) {
    if (!row || row.length < 3) continue;
    const vehicleNo = String(row[0] ?? "").trim();
    const accountKey = String(row[1] ?? "").trim();
    const rawAmt = row[2];
    const amount =
      typeof rawAmt === "number"
        ? rawAmt
        : parseFloat(String(rawAmt).replace(/,/g, "")) || 0;
    if (!vehicleNo || !accountKey) continue;
    rows.push({ vehicleNo, accountKey, amount });
  }
  return rows;
}

export const importRouter = Router();

importRouter.post(
  "/",
  upload.single("file"),
  async (req: Request, res: Response) => {
    const file = req.file;
    const locationId = req.body.locationId as string | undefined;
    const yearMonth = req.body.yearMonth as string | undefined;

    if (!file || !locationId || !yearMonth) {
      res.status(400).json({
        error: "file, locationId, yearMonth are required",
      });
      return;
    }

    const fileName = file.originalname.toLowerCase();
    let dataRows: DataRow[] = [];

    if (fileName.endsWith(".xlsx") || fileName.endsWith(".xls")) {
      dataRows = parseExcel(file.buffer);
    } else {
      const text = file.buffer.toString("utf-8");
      dataRows = parseCSV(text);
    }

    if (dataRows.length === 0) {
      res.status(400).json({
        success: 0,
        errors: ["ファイルにデータがありません"],
      });
      return;
    }

    const accountItems = await prisma.accountItem.findMany({
      where: accountItemEffectiveWhere(yearMonth),
    });
    const accountByName = new Map(accountItems.map((a) => [a.name, a]));
    const accountByCode = new Map(accountItems.map((a) => [a.code, a]));

    const vehicles = await prisma.vehicle.findMany({ where: { locationId } });
    const vehicleByNo = new Map(vehicles.map((v) => [v.vehicleNo, v]));

    let successCount = 0;
    const errors: string[] = [];

    for (let i = 0; i < dataRows.length; i++) {
      const { vehicleNo, accountKey, amount } = dataRows[i];
      const lineNo = i + 2;

      const vehicle = vehicleByNo.get(vehicleNo);
      if (!vehicle) {
        errors.push(`${lineNo}行目: コース名「${vehicleNo}」が見つかりません`);
        continue;
      }

      const accountItem =
        accountByName.get(accountKey) ?? accountByCode.get(accountKey);
      if (!accountItem) {
        errors.push(`${lineNo}行目: 勘定科目「${accountKey}」が見つかりません`);
        continue;
      }

      if (accountItem.isSubtotal) {
        errors.push(`${lineNo}行目: 小計行は編集できません`);
        continue;
      }

      try {
        const existing = await prisma.monthlyRecord.findUnique({
          where: {
            vehicleId_accountItemId_yearMonth: {
              vehicleId: vehicle.id,
              accountItemId: accountItem.id,
              yearMonth,
            },
          },
        });

        const oldAmount = existing ? Number(existing.amount) : 0;

        await prisma.monthlyRecord.upsert({
          where: {
            vehicleId_accountItemId_yearMonth: {
              vehicleId: vehicle.id,
              accountItemId: accountItem.id,
              yearMonth,
            },
          },
          update: { amount },
          create: {
            vehicleId: vehicle.id,
            accountItemId: accountItem.id,
            yearMonth,
            amount,
          },
        });

        if (oldAmount !== amount) {
          await prisma.monthlyRecordHistory.create({
            data: {
              vehicleId: vehicle.id,
              accountItemId: accountItem.id,
              yearMonth,
              oldAmount,
              newAmount: amount,
            },
          });
        }

        successCount++;
      } catch {
        errors.push(`${lineNo}行目: 保存に失敗しました`);
      }
    }

    // 成功時は連携記録を登録（1件以上成功した場合）
    if (successCount > 0) {
      try {
        await prisma.dataSyncLog.create({
          data: {
            source: "手動インポート",
            syncType: "monthly_records",
            recordCount: successCount,
            yearMonth,
            locationId: locationId || null,
          },
        });
      } catch (logErr) {
        console.error("Failed to create sync log:", logErr);
      }
    }

    res.json({ success: successCount, errors });
  }
);

import { Router, Request, Response } from "express";
import { z } from "zod";
import { listSpreadsheetsInFolder } from "../lib/google-drive-client.js";

const spreadsheetsQuerySchema = z.object({
  folderId: z.string().trim().min(1, "folderId is required"),
});

function getGoogleApiStatus(err: unknown): number | undefined {
  if (!err || typeof err !== "object") return undefined;
  const e = err as { code?: unknown; response?: { status?: unknown } };
  if (typeof e.code === "number") return e.code;
  if (typeof e.response?.status === "number") return e.response.status;
  return undefined;
}

function mapDriveSpreadsheetsError(err: unknown): { status: number; error: string } {
  const message = err instanceof Error ? err.message : String(err);
  if (
    message.includes("GOOGLE_SERVICE_ACCOUNT_JSON is not set") ||
    message.includes("[google-drive] GOOGLE_SERVICE_ACCOUNT_JSON")
  ) {
    return { status: 503, error: "サービスアカウントが設定されていません" };
  }

  const apiStatus = getGoogleApiStatus(err);
  if (apiStatus === 403) {
    return {
      status: 403,
      error: "フォルダがサービスアカウントと共有されていない可能性があります",
    };
  }
  if (apiStatus === 404) {
    return { status: 404, error: "フォルダが見つかりません" };
  }

  return { status: 502, error: "Drive API の取得に失敗しました" };
}

export const driveRouter = Router();

driveRouter.get("/spreadsheets", async (req: Request, res: Response) => {
  const parsed = spreadsheetsQuerySchema.safeParse(req.query);
  if (!parsed.success) {
    res.status(400).json({ error: "folderId is required" });
    return;
  }

  try {
    const spreadsheets = await listSpreadsheetsInFolder(parsed.data.folderId);
    res.json({ spreadsheets });
  } catch (err) {
    console.error("[drive/spreadsheets]", err);
    const mapped = mapDriveSpreadsheetsError(err);
    res.status(mapped.status).json({ error: mapped.error });
  }
});

import { test, expect } from "@playwright/test";
import { login } from "./helpers/auth";

test.describe("Locations", () => {
  test.beforeEach(async ({ page }) => {
    await login(page);
  });

  test("shows locations spreadsheet settings page", async ({ page }) => {
    await page.goto("/locations");
    await expect(
      page.getByRole("heading", { name: "拠点スプレッドシート設定" })
    ).toBeVisible();
    await expect(page.getByText("Drive フォルダから一覧取得")).toBeVisible();
  });
});

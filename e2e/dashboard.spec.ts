import { test, expect } from "@playwright/test";
import { login } from "./helpers/auth";

test.describe("Dashboard", () => {
  test.beforeEach(async ({ page }) => {
    await login(page);
  });

  test("displays dashboard summary after login", async ({ page }) => {
    await expect(page.getByRole("heading", { name: "ダッシュボード" })).toBeVisible();
    await expect(page.getByText("拠点別サマリー")).toBeVisible();
  });
});

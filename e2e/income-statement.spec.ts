import { test, expect } from "@playwright/test";
import { login } from "./helpers/auth";

test.describe("Income statement", () => {
  test.beforeEach(async ({ page }) => {
    await login(page);
  });

  test("loads income statement page", async ({ page }) => {
    await page.goto("/income-statement");
    await expect(
      page.getByRole("heading", { name: "車両損益計算書" })
    ).toBeVisible();
  });
});

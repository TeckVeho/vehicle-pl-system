import { test, expect } from "@playwright/test";

test.describe("Authentication", () => {
  test("redirects unauthenticated users to login", async ({ page }) => {
    await page.goto("/dashboard");
    await expect(page).toHaveURL(/\/login/);
  });

  test("shows error on invalid credentials", async ({ page }) => {
    await page.goto("/login");
    await page.getByLabel("ユーザーID または メールアドレス").fill("admin@example.com");
    await page.getByLabel("パスワード").fill("wrong-password");
    await page.getByRole("button", { name: "ログイン" }).click();
    await expect(page.getByRole("alert")).toBeVisible();
    await expect(page).toHaveURL(/\/login/);
  });

  test("logs in with valid credentials", async ({ page }) => {
    await page.goto("/login");
    await page.getByLabel("ユーザーID または メールアドレス").fill("admin@example.com");
    await page.getByLabel("パスワード").fill("password");
    await page.getByRole("button", { name: "ログイン" }).click();
    await page.waitForURL("**/dashboard");
    await expect(page.getByRole("heading", { name: "ダッシュボード" })).toBeVisible();
  });
});

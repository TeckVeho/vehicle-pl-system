import { expect, type Page } from "@playwright/test";

export async function login(
  page: Page,
  credentials: { loginId: string; password: string } = {
    loginId: "admin@example.com",
    password: "password",
  }
) {
  await page.goto("/login");
  await page.getByLabel("ユーザーID または メールアドレス").fill(credentials.loginId);
  await page.getByLabel("パスワード").fill(credentials.password);
  await page.getByRole("button", { name: "ログイン" }).click();
  await page.waitForURL("**/dashboard");
  await expect(page.getByRole("heading", { name: "ダッシュボード" })).toBeVisible();
}

import { test, expect } from "@playwright/test";

test.describe("Forbidden access", () => {
  test("redirects CREW user away from user management", async ({ page }) => {
    await page.goto("/login");
    await page.getByLabel("ユーザーID または メールアドレス").fill("crew@example.com");
    await page.getByLabel("パスワード").fill("password");
    await page.getByRole("button", { name: "ログイン" }).click();
    await page.waitForURL("**/dashboard");

    await page.goto("/users");
    await expect(page).toHaveURL(/\/forbidden/);
  });
});

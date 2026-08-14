import { defineConfig, devices } from "@playwright/test";

const e2eDatabaseUrl =
  process.env.E2E_DATABASE_URL ??
  (process.env.CI
    ? "mysql://root:test@127.0.0.1:3306/vehicle_pl_e2e"
    : "mysql://admin:admin123@localhost:3306/vehicle_pl_e2e");

export default defineConfig({
  testDir: "./e2e",
  fullyParallel: true,
  forbidOnly: !!process.env.CI,
  retries: process.env.CI ? 2 : 0,
  workers: process.env.CI ? 1 : undefined,
  reporter: [["html", { open: "never" }]],
  use: {
    baseURL: "http://localhost:3000",
    trace: "on-first-retry",
  },
  webServer: [
    {
      command: `bash scripts/e2e-prepare-db.sh && cd backend && DATABASE_URL="${e2eDatabaseUrl}" CORS_ORIGIN="http://localhost:3000" npm run dev`,
      url: "http://localhost:4000/api/health",
      reuseExistingServer: !process.env.CI,
      timeout: 180_000,
      cwd: ".",
    },
    {
      command: "npm run dev",
      url: "http://localhost:3000",
      reuseExistingServer: !process.env.CI,
      timeout: 180_000,
    },
  ],
  projects: [
    {
      name: "chromium",
      use: { ...devices["Desktop Chrome"] },
    },
  ],
});

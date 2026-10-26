import { defineConfig } from "vitest/config";

export default defineConfig({
  test: {
    environment: "node",
    include: ["src/**/*.test.ts"],
    pool: "forks",
    env: {
      DATABASE_URL: "mysql://test:test@127.0.0.1:3306/test",
      JWT_SECRET: "test-secret",
    },
  },
});

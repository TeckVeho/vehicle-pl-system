import fs from "node:fs";
import os from "node:os";
import path from "node:path";
import { afterEach, beforeEach, describe, expect, it } from "vitest";
import { loadAppSecrets } from "./loadAppSecrets.js";

describe("loadAppSecrets", () => {
  let tmpDir: string;
  const originalAppSecretsFile = process.env.APP_SECRETS_FILE;
  const originalJwt = process.env.JWT_SECRET;

  beforeEach(() => {
    tmpDir = fs.mkdtempSync(path.join(os.tmpdir(), "vpl-secrets-"));
  });

  afterEach(() => {
    if (originalAppSecretsFile === undefined) {
      delete process.env.APP_SECRETS_FILE;
    } else {
      process.env.APP_SECRETS_FILE = originalAppSecretsFile;
    }
    if (originalJwt === undefined) {
      delete process.env.JWT_SECRET;
    } else {
      process.env.JWT_SECRET = originalJwt;
    }
    fs.rmSync(tmpDir, { recursive: true, force: true });
  });

  it("loads keys from the mounted secrets file", () => {
    const secretsFile = path.join(tmpDir, "app.env");
    fs.writeFileSync(secretsFile, "JWT_SECRET=from-bundle\n");
    process.env.APP_SECRETS_FILE = secretsFile;
    delete process.env.JWT_SECRET;

    loadAppSecrets();

    expect(process.env.JWT_SECRET).toBe("from-bundle");
  });

  it("does not override env vars already set", () => {
    const secretsFile = path.join(tmpDir, "app.env");
    fs.writeFileSync(secretsFile, "JWT_SECRET=from-bundle\n");
    process.env.APP_SECRETS_FILE = secretsFile;
    process.env.JWT_SECRET = "already-set";

    loadAppSecrets();

    expect(process.env.JWT_SECRET).toBe("already-set");
  });
});

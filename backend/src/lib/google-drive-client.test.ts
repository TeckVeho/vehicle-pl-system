import { afterEach, beforeEach, describe, expect, it, vi } from "vitest";

const { googleAuthMock, driveFactoryMock } = vi.hoisted(() => {
  const googleAuthMock = vi.fn();
  const driveFactoryMock = vi.fn();
  return { googleAuthMock, driveFactoryMock };
});

vi.mock("googleapis", () => ({
  google: {
    auth: { GoogleAuth: googleAuthMock },
    drive: driveFactoryMock,
  },
}));

import {
  getDriveClient,
  resetGoogleDriveClientForTests,
} from "./google-drive-client.js";

const originalEnv = process.env.GOOGLE_SERVICE_ACCOUNT_JSON;

const minimalServiceAccountJson = JSON.stringify({
  type: "service_account",
  project_id: "test-proj",
  private_key_id: "keyid",
  private_key:
    "-----BEGIN PRIVATE KEY-----\nMIIEvgIBADANBg...\n-----END PRIVATE KEY-----\n",
  client_email: "svc@test-proj.iam.gserviceaccount.com",
  client_id: "123456789",
});

describe("getDriveClient", () => {
  beforeEach(() => {
    resetGoogleDriveClientForTests();
    vi.clearAllMocks();
    delete process.env.GOOGLE_SERVICE_ACCOUNT_JSON;
    driveFactoryMock.mockReturnValue({ mocked: true });
  });

  afterEach(() => {
    resetGoogleDriveClientForTests();
    if (originalEnv === undefined) {
      delete process.env.GOOGLE_SERVICE_ACCOUNT_JSON;
    } else {
      process.env.GOOGLE_SERVICE_ACCOUNT_JSON = originalEnv;
    }
  });

  it("throws when GOOGLE_SERVICE_ACCOUNT_JSON is not set", () => {
    expect(() => getDriveClient()).toThrow(
      "[google-drive] GOOGLE_SERVICE_ACCOUNT_JSON is not set"
    );
    expect(googleAuthMock).not.toHaveBeenCalled();
    expect(driveFactoryMock).not.toHaveBeenCalled();
  });

  it("throws when GOOGLE_SERVICE_ACCOUNT_JSON is empty or whitespace-only", () => {
    process.env.GOOGLE_SERVICE_ACCOUNT_JSON = "   ";
    expect(() => getDriveClient()).toThrow(
      "[google-drive] GOOGLE_SERVICE_ACCOUNT_JSON is not set"
    );
  });

  it("throws when GOOGLE_SERVICE_ACCOUNT_JSON is not valid JSON", () => {
    process.env.GOOGLE_SERVICE_ACCOUNT_JSON = "not-json";
    expect(() => getDriveClient()).toThrow(SyntaxError);
    expect(googleAuthMock).not.toHaveBeenCalled();
    expect(driveFactoryMock).not.toHaveBeenCalled();
  });

  it("returns the same singleton and builds drive v3 client with readonly scope", () => {
    process.env.GOOGLE_SERVICE_ACCOUNT_JSON = minimalServiceAccountJson;

    const client = getDriveClient();
    expect(client).toEqual({ mocked: true });
    expect(getDriveClient()).toBe(client);

    expect(googleAuthMock).toHaveBeenCalledTimes(1);
    expect(googleAuthMock).toHaveBeenCalledWith({
      credentials: JSON.parse(minimalServiceAccountJson),
      scopes: ["https://www.googleapis.com/auth/drive.readonly"],
    });

    expect(driveFactoryMock).toHaveBeenCalledTimes(1);
    expect(driveFactoryMock).toHaveBeenCalledWith({
      version: "v3",
      auth: expect.anything(),
    });
  });
});

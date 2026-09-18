import "../__tests__/helpers/setup-prisma-mock.js";
import request from "supertest";
import { beforeEach, describe, expect, it, vi } from "vitest";
import { getTestApp } from "../__tests__/helpers/app.js";
import {
  crewToken,
  dxAdminToken,
  masterToken,
  mockUser,
} from "../__tests__/helpers/auth.js";
import { prismaMock } from "../__tests__/helpers/setup-prisma-mock.js";

const app = getTestApp();

describe("users routes", () => {
  beforeEach(() => {
    vi.clearAllMocks();
    prismaMock.user.findMany.mockResolvedValue([
      {
        id: "u1",
        email: "admin@example.com",
        name: "Admin",
        role: "DX管理者",
        externalId: null,
        createdAt: new Date(),
        updatedAt: new Date(),
      },
    ]);
  });

  describe("GET /api/users", () => {
    it("returns 401 without auth", async () => {
      const res = await request(app).get("/api/users");
      expect(res.status).toBe(401);
    });

    it("returns 403 for non-USER_ADMIN role", async () => {
      prismaMock.user.findUnique.mockResolvedValue(mockUser("DX", "u1"));
      const token = masterToken();
      const res = await request(app)
        .get("/api/users")
        .set("Cookie", `auth-token=${token}`);

      expect(res.status).toBe(403);
    });

    it("returns 403 for CREW role", async () => {
      prismaMock.user.findUnique.mockResolvedValue(mockUser("CREW", "u2"));
      const token = crewToken();
      const res = await request(app)
        .get("/api/users")
        .set("Cookie", `auth-token=${token}`);

      expect(res.status).toBe(403);
    });

    it("returns user list for DX管理者", async () => {
      prismaMock.user.findUnique.mockResolvedValue(
        mockUser("DX管理者", "u-admin", "Admin")
      );
      const token = dxAdminToken();
      const res = await request(app)
        .get("/api/users")
        .set("Cookie", `auth-token=${token}`);

      expect(res.status).toBe(200);
      expect(res.body).toHaveLength(1);
      expect(res.body[0].email).toBe("admin@example.com");
    });
  });

  describe("GET /api/users/:idOrExternalId", () => {
    it("returns 404 when user not found", async () => {
      prismaMock.user.findUnique.mockResolvedValue(
        mockUser("DX管理者", "u-admin", "Admin")
      );
      prismaMock.user.findFirst.mockResolvedValue(null);
      const token = dxAdminToken();
      const res = await request(app)
        .get("/api/users/missing")
        .set("Cookie", `auth-token=${token}`);

      expect(res.status).toBe(404);
      expect(res.body).toMatchObject({ error: "User not found" });
    });

    it("returns user by id", async () => {
      prismaMock.user.findUnique.mockResolvedValue(
        mockUser("DX管理者", "u-admin", "Admin")
      );
      prismaMock.user.findFirst.mockResolvedValue({
        id: "u1",
        email: "admin@example.com",
        name: "Admin",
        role: "DX管理者",
        externalId: null,
        createdAt: new Date(),
        updatedAt: new Date(),
      });
      const token = dxAdminToken();
      const res = await request(app)
        .get("/api/users/u1")
        .set("Cookie", `auth-token=${token}`);

      expect(res.status).toBe(200);
      expect(res.body.id).toBe("u1");
    });
  });

  describe("POST /api/users/sync", () => {
    const syncUser = {
      userId: "ic-100",
      email: "tenko@example.com",
      name: "点呼 太郎",
      role: "点呼員",
    };

    beforeEach(() => {
      prismaMock.user.findUnique.mockResolvedValue(
        mockUser("DX管理者", "u-admin", "Admin")
      );
      prismaMock.user.findFirst.mockResolvedValue(null);
      prismaMock.user.create.mockResolvedValue({
        id: "u-new",
        email: syncUser.email,
        name: syncUser.name,
        role: syncUser.role,
        externalId: syncUser.userId,
        createdAt: new Date(),
        updatedAt: new Date(),
      });
    });

    it("creates user with 点呼員 role", async () => {
      const token = dxAdminToken();
      const res = await request(app)
        .post("/api/users/sync")
        .set("Cookie", `auth-token=${token}`)
        .send({ users: [syncUser] });

      expect(res.status).toBe(200);
      expect(res.body).toMatchObject({
        synced: 1,
        results: [
          { email: syncUser.email, status: "created", id: "u-new" },
        ],
      });
      expect(prismaMock.user.create).toHaveBeenCalledWith({
        data: expect.objectContaining({
          email: syncUser.email,
          name: syncUser.name,
          role: "点呼員",
          externalId: syncUser.userId,
        }),
      });
    });

    it("creates user with 事務員 role (regression)", async () => {
      const clerkUser = {
        userId: "ic-101",
        email: "clerk@example.com",
        name: "事務 花子",
        role: "事務員",
      };
      prismaMock.user.create.mockResolvedValue({
        id: "u-clerk",
        email: clerkUser.email,
        name: clerkUser.name,
        role: clerkUser.role,
        externalId: clerkUser.userId,
        createdAt: new Date(),
        updatedAt: new Date(),
      });

      const token = dxAdminToken();
      const res = await request(app)
        .post("/api/users/sync")
        .set("Cookie", `auth-token=${token}`)
        .send({ users: [clerkUser] });

      expect(res.status).toBe(200);
      expect(res.body.synced).toBe(1);
      expect(prismaMock.user.create).toHaveBeenCalledWith({
        data: expect.objectContaining({ role: "事務員" }),
      });
    });

    it("skips user with invalid role", async () => {
      const token = dxAdminToken();
      const res = await request(app)
        .post("/api/users/sync")
        .set("Cookie", `auth-token=${token}`)
        .send({
          users: [
            {
              userId: "ic-bad",
              email: "bad@example.com",
              name: "Bad User",
              role: "invalid_role",
            },
          ],
        });

      expect(res.status).toBe(200);
      expect(res.body).toMatchObject({ synced: 0, results: [] });
      expect(prismaMock.user.create).not.toHaveBeenCalled();
      expect(prismaMock.user.update).not.toHaveBeenCalled();
    });
  });
});

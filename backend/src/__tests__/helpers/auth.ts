import jwt from "jsonwebtoken";

export const JWT_SECRET =
  process.env.JWT_SECRET ?? "dev-secret-change-in-production";

export function signToken(role: string, userId: string): string {
  return jwt.sign(
    { userId, email: `${userId}@test.local`, role },
    JWT_SECRET,
    { expiresIn: "7d" }
  );
}

export function masterToken(userId = "u1"): string {
  return signToken("DX", userId);
}

export function crewToken(userId = "u2"): string {
  return signToken("CREW", userId);
}

export function dxAdminToken(userId = "u-admin"): string {
  return signToken("DX管理者", userId);
}

export function mockUser(
  role: string,
  userId: string,
  name = "Test"
): { id: string; email: string; name: string; role: string } {
  return {
    id: userId,
    email: `${userId}@test.local`,
    name,
    role,
  };
}

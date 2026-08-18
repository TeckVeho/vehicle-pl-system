import { vi } from "vitest";

const { prismaMock } = vi.hoisted(() => {
  const f = vi.fn;
  return {
    prismaMock: {
      user: {
        findUnique: f(),
        findFirst: f(),
        findMany: f(),
        create: f(),
        update: f(),
        upsert: f(),
        delete: f(),
      },
      location: {
        findMany: f(),
        findUnique: f(),
        update: f(),
      },
      vehicle: {
        findMany: f(),
        findFirst: f(),
        findUnique: f(),
        upsert: f(),
        update: f(),
      },
      course: {
        findFirst: f(),
        findUnique: f(),
        findMany: f().mockResolvedValue([]),
        create: f(),
        update: f(),
        aggregate: f(),
      },
      driver: {
        findFirst: f(),
        findUnique: f(),
        create: f(),
        update: f(),
      },
      accountItem: {
        findMany: f(),
        findUnique: f(),
        create: f(),
        update: f(),
        aggregate: f(),
      },
      driverMonthlyAmount: { upsert: f() },
      dailyDriverAssignment: {
        findMany: f(),
        deleteMany: f(),
        createMany: f(),
        upsert: f(),
      },
      dailyAtmtcRun: {
        deleteMany: f(),
        create: f(),
        upsert: f(),
      },
      dailyOperatingRecord: { upsert: f() },
      vehicleMonthlyCost: {
        findUnique: f(),
        findMany: f(),
        upsert: f(),
      },
      locationMonthlyExpense: { findMany: f() },
      locationCalculationParameter: { findMany: f() },
      driveSpreadsheetRevenueLine: { findMany: f() },
      monthlyRecord: {
        findMany: f(),
        findUnique: f(),
        upsert: f(),
      },
      dataSyncLog: {
        findMany: f(),
        create: f(),
      },
      $executeRawUnsafe: f(),
    },
  };
});

vi.mock("../../lib/prisma.js", () => ({
  prisma: prismaMock,
}));

export { prismaMock };

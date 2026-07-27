/**
 * ATMTC 連携の仮データを本社（LOC001）に投入し、コース別損益の表示確認用データを作る。
 *
 * 実行: cd backend && npx tsx --env-file=.env scripts/seed-atmtc-demo.ts
 *
 * UI: 損益計算書 → 拠点「本社」→ 対象月（デフォルト当月）→ 表示「コース」
 */
import { PrismaClient } from "@prisma/client";
import { syncDailyOperatingRecordsFromRows } from "../src/lib/daily-operating-records-sync.js";
import { runCourseAllocationScope } from "../src/lib/course-allocation-trigger.js";
import { runDriverAllocation } from "../src/lib/driver-allocation.js";
import { runSalaryRunCountAllocation } from "../src/lib/salary-run-count-allocation.js";

const prisma = new PrismaClient();

const LOCATION_CODE = "LOC001";
const DEMO_DRIVERS = [
  { code: "DEMO001", name: "デモ乗務員A", externalId: "atmtc-driver-a" },
  { code: "DEMO002", name: "デモ乗務員B", externalId: "atmtc-driver-b" },
] as const;

function currentYearMonth(): string {
  const now = new Date();
  return `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, "0")}`;
}

async function resolveCourseId(
  locId: string,
  courseCode: string
): Promise<string | null> {
  const c = await prisma.course.findUnique({
    where: { locationId_code: { locationId: locId, code: courseCode } },
    select: { id: true },
  });
  return c?.id ?? null;
}

async function main() {
  const yearMonth = process.env.DEMO_YEAR_MONTH?.trim() || currentYearMonth();
  const [y, m] = yearMonth.split("-").map(Number);
  const day = (d: number) =>
    `${yearMonth}-${String(d).padStart(2, "0")}`;

  const loc = await prisma.location.findUnique({
    where: { code: LOCATION_CODE },
  });
  if (!loc) {
    throw new Error(`Location ${LOCATION_CODE} not found. Run db:seed first.`);
  }

  const vehicleNos = ["001-001", "001-002", "001-003"];
  const vehicles = await prisma.vehicle.findMany({
    where: { locationId: loc.id, vehicleNo: { in: vehicleNos } },
    include: { course: { select: { code: true, name: true } } },
  });
  if (vehicles.length < 3) {
    throw new Error(
      `Expected vehicles ${vehicleNos.join(", ")} at ${LOCATION_CODE}. Run db:seed.`
    );
  }
  const byNo = new Map(vehicles.map((v) => [v.vehicleNo, v]));

  for (const v of vehicles) {
    await prisma.vehicle.update({
      where: { id: v.id },
      data: { externalId: v.vehicleNo },
    });
  }

  const driverIds: string[] = [];
  for (const d of DEMO_DRIVERS) {
    const row = await prisma.driver.upsert({
      where: { locationId_code: { locationId: loc.id, code: d.code } },
      create: {
        locationId: loc.id,
        code: d.code,
        name: d.name,
        externalId: d.externalId,
      },
      update: { name: d.name, externalId: d.externalId },
    });
    driverIds.push(row.id);
  }

  const yamazaki = await prisma.accountItem.findFirst({
    where: { code: "5010", name: "山崎製パン" },
    select: { id: true },
  });
  const salaryItem = await prisma.accountItem.findFirst({
    where: { code: "6138", name: "乗務員給料" },
    select: { id: true },
  });
  if (!yamazaki || !salaryItem) {
    throw new Error("Account items missing. Run db:seed.");
  }

  const v1 = byNo.get("001-001")!;
  const v2 = byNo.get("001-002")!;
  const v3 = byNo.get("001-003")!;

  await prisma.dailyAtmtcRun.deleteMany({
    where: { locationId: loc.id, yearMonth },
  });

  type AtmtcRow = {
    date: string;
    vehicleExternalId: string;
    driverExternalId: string;
    courseCode?: string;
    weight: number;
    sourceTxnId: string;
  };

  const atmtcRecords: AtmtcRow[] = [
    {
      date: day(1),
      vehicleExternalId: v1.vehicleNo,
      driverExternalId: DEMO_DRIVERS[0].externalId,
      courseCode: "001-001",
      weight: 1,
      sourceTxnId: `demo-atmtc-${yearMonth}-001`,
    },
    {
      date: day(2),
      vehicleExternalId: v1.vehicleNo,
      driverExternalId: DEMO_DRIVERS[0].externalId,
      courseCode: "001-001",
      weight: 1,
      sourceTxnId: `demo-atmtc-${yearMonth}-002`,
    },
    {
      date: day(3),
      vehicleExternalId: v1.vehicleNo,
      driverExternalId: DEMO_DRIVERS[0].externalId,
      courseCode: "001-001",
      weight: 0.6,
      sourceTxnId: `demo-atmtc-${yearMonth}-003a`,
    },
    {
      date: day(3),
      vehicleExternalId: v1.vehicleNo,
      driverExternalId: DEMO_DRIVERS[0].externalId,
      courseCode: "001-002",
      weight: 0.4,
      sourceTxnId: `demo-atmtc-${yearMonth}-003b`,
    },
    {
      date: day(4),
      vehicleExternalId: v2.vehicleNo,
      driverExternalId: DEMO_DRIVERS[1].externalId,
      courseCode: "001-002",
      weight: 1,
      sourceTxnId: `demo-atmtc-${yearMonth}-004`,
    },
    {
      date: day(5),
      vehicleExternalId: v3.vehicleNo,
      driverExternalId: DEMO_DRIVERS[1].externalId,
      courseCode: "UNKNOWN-COURSE",
      weight: 1,
      sourceTxnId: `demo-atmtc-${yearMonth}-005`,
    },
  ];

  const runCountByVehicleDate = new Map<string, number>();
  let atmtcRuns = 0;

  for (const r of atmtcRecords) {
    const vehicle = await prisma.vehicle.findFirst({
      where: { locationId: loc.id, externalId: r.vehicleExternalId },
    });
    const driver = await prisma.driver.findFirst({
      where: { locationId: loc.id, externalId: r.driverExternalId },
    });
    if (!vehicle) continue;

    const courseId = r.courseCode
      ? await resolveCourseId(loc.id, r.courseCode)
      : null;

    const runData = {
      locationId: loc.id,
      date: r.date,
      yearMonth,
      vehicleId: vehicle.id,
      driverId: driver?.id ?? null,
      courseId,
      courseExternalId: null as string | null,
      weight: r.weight,
      sourceTxnId: r.sourceTxnId,
    };

    await prisma.dailyAtmtcRun.upsert({
      where: { sourceTxnId: r.sourceTxnId },
      create: runData,
      update: runData,
    });
    atmtcRuns++;

    if (driver) {
      await prisma.dailyDriverAssignment.upsert({
        where: {
          driverId_vehicleId_date: {
            driverId: driver.id,
            vehicleId: vehicle.id,
            date: r.date,
          },
        },
        create: {
          driverId: driver.id,
          vehicleId: vehicle.id,
          date: r.date,
          yearMonth,
        },
        update: {},
      });
    }

    const aggKey = `${vehicle.id}|${r.date}`;
    runCountByVehicleDate.set(
      aggKey,
      (runCountByVehicleDate.get(aggKey) ?? 0) + r.weight
    );
  }

  const operatingRows = Array.from(runCountByVehicleDate.entries()).map(
    ([key, runCount]) => {
      const [vehicleId, date] = key.split("|");
      return { vehicleId, date, runCount, isOperating: true };
    }
  );
  const operating = await syncDailyOperatingRecordsFromRows({
    yearMonth,
    locationId: loc.id,
    records: operatingRows,
  });

  for (const d of DEMO_DRIVERS) {
    const driver = await prisma.driver.findFirst({
      where: { locationId: loc.id, code: d.code },
    });
    if (!driver) continue;
    await prisma.driverMonthlyAmount.upsert({
      where: {
        driverId_accountItemId_yearMonth: {
          driverId: driver.id,
          accountItemId: salaryItem.id,
          yearMonth,
        },
      },
      create: {
        driverId: driver.id,
        accountItemId: salaryItem.id,
        yearMonth,
        amount: d.code === "DEMO001" ? 310_000 : 280_000,
      },
      update: {
        amount: d.code === "DEMO001" ? 310_000 : 280_000,
      },
    });
  }

  const salaryAlloc = await runSalaryRunCountAllocation(yearMonth, loc.id);
  await runDriverAllocation(yearMonth, loc.id);

  const revenueByCourse: Array<{ courseCode: string; amount: number }> = [
    { courseCode: "001-001", amount: 1_250_000 },
    { courseCode: "001-002", amount: 980_000 },
    { courseCode: "001-003", amount: 420_000 },
  ];
  for (const line of revenueByCourse) {
    const course = await prisma.course.findUnique({
      where: { locationId_code: { locationId: loc.id, code: line.courseCode } },
    });
    if (!course) continue;
    await prisma.driveSpreadsheetRevenueCourseLine.upsert({
      where: {
        locationId_yearMonth_courseId_accountItemId: {
          locationId: loc.id,
          yearMonth,
          courseId: course.id,
          accountItemId: yamazaki.id,
        },
      },
      create: {
        locationId: loc.id,
        yearMonth,
        courseId: course.id,
        accountItemId: yamazaki.id,
        amount: line.amount,
      },
      update: { amount: line.amount },
    });
    const vehicle = await prisma.vehicle.findFirst({
      where: { locationId: loc.id, vehicleNo: line.courseCode },
    });
    if (vehicle) {
      await prisma.driveSpreadsheetRevenueLine.upsert({
        where: {
          locationId_yearMonth_vehicleId_accountItemId: {
            locationId: loc.id,
            yearMonth,
            vehicleId: vehicle.id,
            accountItemId: yamazaki.id,
          },
        },
        create: {
          locationId: loc.id,
          yearMonth,
          vehicleId: vehicle.id,
          accountItemId: yamazaki.id,
          amount: line.amount,
        },
        update: { amount: line.amount },
      });
    }
  }

  const courseAllocation = await runCourseAllocationScope(yearMonth, loc.id);

  await prisma.dataSyncLog.create({
    data: {
      source: "ATMTC",
      syncType: "atmtc_transactions",
      recordCount: atmtcRecords.length,
      yearMonth,
      locationId: loc.id,
    },
  });

  const courseRecords = await prisma.courseMonthlyRecord.count({
    where: { locationId: loc.id, yearMonth },
  });

  console.log("");
  console.log("=== ATMTC デモデータ投入完了 ===");
  console.log(`拠点: ${loc.name} (${LOCATION_CODE})`);
  console.log(`対象月: ${yearMonth}`);
  console.log(`DailyAtmtcRun: ${atmtcRuns} 件`);
  console.log(`DailyOperating upserted: ${operating.upserted}`);
  console.log(`給与配賦: 車両 ${salaryAlloc.vehiclesUpdated} / レコード ${salaryAlloc.recordsUpdated}`);
  console.log(`CourseMonthlyRecord: ${courseRecords} 件`);
  console.log(`コース集計: ${JSON.stringify(courseAllocation)}`);
  if (operating.errors.length) {
    console.warn("operating errors:", operating.errors);
  }
  console.log("");
  console.log("確認手順:");
  console.log("  1. バックエンド :4000 / フロント :3000 が起動していること");
  console.log("  2. admin@example.com / password でログイン");
  console.log(
    `  3. 直接開く: http://localhost:3000/income-statement?yearMonth=${yearMonth}&locationId=${loc.id}`
  );
  console.log(`     （拠点「本社」・${yearMonth}・表示「コース」）`);
  console.log("  4. 右端の「合計」列に山崎製パン・乗務員給料の合計が出れば OK");
  console.log("  5. 連携記録: http://localhost:3000/sync-logs に ATMTC 行が追加されます");
  console.log("");
}

main()
  .catch((e) => {
    console.error(e);
    process.exit(1);
  })
  .finally(() => prisma.$disconnect());

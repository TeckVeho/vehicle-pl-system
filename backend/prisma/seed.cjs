const { PrismaClient } = require("@prisma/client");
const bcrypt = require("bcrypt");
const prisma = new PrismaClient();

const ADMIN_USER = {
  email: "admin@example.com",
  password: "password",
  name: "管理者",
  role: "DX管理者",
};

const ACCOUNT_ITEMS = [
  { code: "5010", name: "山崎製パン", category: "revenue", sortOrder: 1 },
  { code: "5010", name: "ヤマザキ物流", category: "revenue", sortOrder: 2 },
  { code: "5010", name: "サンロジスティックス", category: "revenue", sortOrder: 3 },
  { code: "5010", name: "末広製菓", category: "revenue", sortOrder: 4 },
  { code: "5010", name: "富士エコー", category: "revenue", sortOrder: 5 },
  { code: "5010", name: "パスコ", category: "revenue", sortOrder: 6 },
  { code: "5010", name: "日立物流", category: "revenue", sortOrder: 7 },
  { code: "5010", name: "菱倉運輸", category: "revenue", sortOrder: 8 },
  { code: "5010", name: "ロジスティクス・ネットワーク", category: "revenue", sortOrder: 9 },
  { code: "5010", name: "ダイセーロジスティクス", category: "revenue", sortOrder: 10 },
  { code: "5010", name: "関東運輸", category: "revenue", sortOrder: 11 },
  { code: "5010", name: "その他", category: "revenue", sortOrder: 12 },
  { code: "5010", name: "不動産収入", category: "revenue", sortOrder: 13 },
  { code: "5010", name: "人材派遣収入", category: "revenue", sortOrder: 14 },
  { code: "SUBTOTAL_REV", name: "純売上高", category: "subtotal_revenue", sortOrder: 15, isSubtotal: true },
  { code: "6138", name: "乗務員給料", category: "expense", sortOrder: 16 },
  { code: "6139", name: "業務給料", category: "expense", sortOrder: 17 },
  { code: "6141", name: "人材派遣費", category: "expense", sortOrder: 18 },
  { code: "6145", name: "賞与", category: "expense", sortOrder: 19 },
  { code: "6147", name: "通勤手当", category: "expense", sortOrder: 20 },
  { code: "6148", name: "法定福利費", category: "expense", sortOrder: 21 },
  { code: "6149", name: "福利厚生費", category: "expense", sortOrder: 22 },
  { code: "6150", name: "旅費交通地", category: "expense", sortOrder: 23 },
  { code: "6151", name: "消耗品", category: "expense", sortOrder: 24 },
  { code: "6154", name: "修繕費", category: "expense", sortOrder: 25 },
  { code: "6156", name: "通信費", category: "expense", sortOrder: 26 },
  { code: "6159", name: "水道光熱費", category: "expense", sortOrder: 27 },
  { code: "6160", name: "保険料", category: "expense", sortOrder: 28 },
  { code: "6162", name: "租税公課", category: "expense", sortOrder: 29 },
  { code: "6164", name: "他手数料", category: "expense", sortOrder: 30 },
  { code: "6165", name: "交際接待費", category: "expense", sortOrder: 31 },
  { code: "6166", name: "会議費", category: "expense", sortOrder: 32 },
  { code: "6167", name: "広告宣伝費", category: "expense", sortOrder: 33 },
  { code: "6168", name: "諸会費", category: "expense", sortOrder: 34 },
  { code: "6171", name: "地代家賃", category: "expense", sortOrder: 35 },
  { code: "6172", name: "借家料", category: "expense", sortOrder: 36 },
  { code: "6173", name: "賃借料", category: "expense", sortOrder: 37 },
  { code: "6174", name: "保守料", category: "expense", sortOrder: 38 },
  { code: "6175", name: "燃料費", category: "expense", sortOrder: 39 },
  { code: "6176", name: "道路使用料", category: "expense", sortOrder: 40 },
  { code: "6177", name: "図書研修費", category: "expense", sortOrder: 41 },
  { code: "6178", name: "減価償却費", category: "expense", sortOrder: 42 },
  { code: "6188", name: "雑費", category: "expense", sortOrder: 43 },
  { code: "6189", name: "事故賠償費", category: "expense", sortOrder: 44 },
  { code: "6190", name: "車両修繕費", category: "expense", sortOrder: 45 },
  { code: "6191", name: "リース車償却", category: "expense", sortOrder: 46 },
  { code: "6192", name: "車両償却費", category: "expense", sortOrder: 47 },
  { code: "6193", name: "車両リース", category: "expense", sortOrder: 48 },
  { code: "6194", name: "損害保険料", category: "expense", sortOrder: 49 },
  { code: "6195", name: "賦課税", category: "expense", sortOrder: 50 },
  { code: "6196", name: "メンテナンスリース", category: "expense", sortOrder: 51 },
  { code: "6197", name: "etc（常用外）", category: "expense", sortOrder: 52 },
  { code: "SUBTOTAL_EXP", name: "自車原価計", category: "subtotal_expense", sortOrder: 53, isSubtotal: true },
  { code: "SUBTOTAL_GROSS", name: "自車粗利益", category: "subtotal_gross", sortOrder: 54, isSubtotal: true },
  { code: "SUMMARY_REV", name: "売　　上　　計", category: "summary", sortOrder: 55, isSubtotal: true },
  { code: "SUMMARY_EXP", name: "原　　価　　計", category: "summary", sortOrder: 56, isSubtotal: true },
  { code: "SUMMARY_GROSS", name: "粗　　利　　益", category: "summary", sortOrder: 57, isSubtotal: true },
];

const LOCATIONS = [
  { code: "LOC001", name: "横浜第1" },
  { code: "LOC002", name: "横浜第2" },
  { code: "LOC003", name: "横浜第3" },
  { code: "LOC004", name: "平塚" },
  { code: "LOC005", name: "静岡" },
  { code: "LOC006", name: "武蔵野" },
  { code: "LOC007", name: "所沢" },
  { code: "LOC008", name: "古河" },
  { code: "LOC009", name: "千葉" },
  { code: "LOC010", name: "八千代" },
  { code: "LOC011", name: "東京" },
  { code: "LOC012", name: "新潟" },
  { code: "LOC013", name: "名古屋" },
  { code: "LOC014", name: "浜松" },
  { code: "LOC015", name: "安城" },
  { code: "LOC016", name: "富山" },
  { code: "LOC017", name: "大阪" },
  { code: "LOC018", name: "神戸" },
  { code: "LOC019", name: "本社" },
  { code: "LOC020", name: "管理本部" },
  { code: "LOC021", name: "不動産管理" },
  { code: "LOC022", name: "米沢" },
];

async function main() {
  console.log("Seeding database...");

  const adminPasswordHash = await bcrypt.hash(ADMIN_USER.password, 10);
  await prisma.user.upsert({
    where: { email: ADMIN_USER.email },
    update: { name: ADMIN_USER.name, role: ADMIN_USER.role, passwordHash: adminPasswordHash },
    create: {
      email: ADMIN_USER.email,
      passwordHash: adminPasswordHash,
      name: ADMIN_USER.name,
      role: ADMIN_USER.role,
    },
  });

  for (const item of ACCOUNT_ITEMS) {
    await prisma.accountItem.upsert({
      where: {
        code_name: { code: item.code, name: item.name },
      },
      update: {},
      create: {
        code: item.code,
        name: item.name,
        category: item.category,
        sortOrder: item.sortOrder,
        isSubtotal: item.isSubtotal ?? false,
      },
    });
  }

  for (const loc of LOCATIONS) {
    await prisma.location.upsert({
      where: { code: loc.code },
      update: {},
      create: loc,
    });
  }

  const COURSE_NAME_LETTERS = "ＡＢＣＤＥＦＧＨＩＪＫＬＭＮＯＰＱＲＳＴＵＶＷＸＹＺ";
  const getCourseName = (v) => {
    if (v <= 26) return `山崎製パン${COURSE_NAME_LETTERS[v - 1]}便`;
    return `山崎製パン${COURSE_NAME_LETTERS[0]}${COURSE_NAME_LETTERS[v - 27]}便`;
  };

  const locations = await prisma.location.findMany();
  for (const loc of locations) {
    for (let v = 1; v <= 35; v++) {
      const vehicleNo = `${loc.code.replace("LOC", "")}-${String(v).padStart(3, "0")}`;
      const courseName = getCourseName(v);
      const course = await prisma.course.upsert({
        where: {
          locationId_code: {
            locationId: loc.id,
            code: vehicleNo,
          },
        },
        update: { name: courseName },
        create: {
          locationId: loc.id,
          name: courseName,
          code: vehicleNo,
          sortOrder: v,
        },
      });
      await prisma.vehicle.upsert({
        where: {
          locationId_vehicleNo: {
            locationId: loc.id,
            vehicleNo,
          },
        },
        update: { courseId: course.id },
        create: {
          locationId: loc.id,
          vehicleNo,
          courseId: course.id,
        },
      });
    }
  }

  console.log("Seed completed.");
}

main()
  .catch((e) => {
    console.error(e);
    process.exit(1);
  })
  .finally(async () => {
    await prisma.$disconnect();
  });

-- ATMTC daily runs + course-level monthly amounts + spreadsheet revenue by course code

CREATE TABLE `DailyAtmtcRun` (
    `id` VARCHAR(191) NOT NULL,
    `locationId` VARCHAR(191) NOT NULL,
    `date` VARCHAR(191) NOT NULL,
    `yearMonth` VARCHAR(191) NOT NULL,
    `vehicleId` VARCHAR(191) NOT NULL,
    `driverId` VARCHAR(191) NULL,
    `courseId` VARCHAR(191) NULL,
    `courseExternalId` VARCHAR(191) NULL,
    `weight` DOUBLE NOT NULL DEFAULT 1,
    `sourceTxnId` VARCHAR(191) NULL,
    `createdAt` DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3),
    `updatedAt` DATETIME(3) NOT NULL,

    UNIQUE INDEX `DailyAtmtcRun_sourceTxnId_key`(`sourceTxnId`),
    INDEX `DailyAtmtcRun_yearMonth_locationId_idx`(`yearMonth`, `locationId`),
    INDEX `DailyAtmtcRun_yearMonth_vehicleId_idx`(`yearMonth`, `vehicleId`),
    INDEX `DailyAtmtcRun_yearMonth_courseId_idx`(`yearMonth`, `courseId`),
    PRIMARY KEY (`id`)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE `CourseMonthlyRecord` (
    `id` VARCHAR(191) NOT NULL,
    `locationId` VARCHAR(191) NOT NULL,
    `courseSlotKey` VARCHAR(191) NOT NULL,
    `courseId` VARCHAR(191) NULL,
    `accountItemId` VARCHAR(191) NOT NULL,
    `yearMonth` VARCHAR(191) NOT NULL,
    `amount` DOUBLE NOT NULL DEFAULT 0,
    `createdAt` DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3),
    `updatedAt` DATETIME(3) NOT NULL,

    UNIQUE INDEX `cmr_loc_ym_slot_ai_key`(`locationId`, `yearMonth`, `courseSlotKey`, `accountItemId`),
    INDEX `CourseMonthlyRecord_yearMonth_locationId_idx`(`yearMonth`, `locationId`),
    INDEX `CourseMonthlyRecord_courseId_idx`(`courseId`),
    PRIMARY KEY (`id`)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE `DriveSpreadsheetRevenueCourseLine` (
    `id` VARCHAR(191) NOT NULL,
    `locationId` VARCHAR(191) NOT NULL,
    `yearMonth` VARCHAR(191) NOT NULL,
    `courseId` VARCHAR(191) NOT NULL,
    `accountItemId` VARCHAR(191) NOT NULL,
    `amount` DOUBLE NOT NULL DEFAULT 0,
    `createdAt` DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3),
    `updatedAt` DATETIME(3) NOT NULL,

    UNIQUE INDEX `dsr_course_rev_uq`(`locationId`, `yearMonth`, `courseId`, `accountItemId`),
    INDEX `DriveSpreadsheetRevenueCourseLine_locationId_yearMonth_idx`(`locationId`, `yearMonth`),
    PRIMARY KEY (`id`)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

ALTER TABLE `DailyAtmtcRun` ADD CONSTRAINT `DailyAtmtcRun_locationId_fkey` FOREIGN KEY (`locationId`) REFERENCES `Location`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `DailyAtmtcRun` ADD CONSTRAINT `DailyAtmtcRun_vehicleId_fkey` FOREIGN KEY (`vehicleId`) REFERENCES `Vehicle`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `DailyAtmtcRun` ADD CONSTRAINT `DailyAtmtcRun_driverId_fkey` FOREIGN KEY (`driverId`) REFERENCES `Driver`(`id`) ON DELETE SET NULL ON UPDATE CASCADE;
ALTER TABLE `DailyAtmtcRun` ADD CONSTRAINT `DailyAtmtcRun_courseId_fkey` FOREIGN KEY (`courseId`) REFERENCES `Course`(`id`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `CourseMonthlyRecord` ADD CONSTRAINT `CourseMonthlyRecord_locationId_fkey` FOREIGN KEY (`locationId`) REFERENCES `Location`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `CourseMonthlyRecord` ADD CONSTRAINT `CourseMonthlyRecord_courseId_fkey` FOREIGN KEY (`courseId`) REFERENCES `Course`(`id`) ON DELETE SET NULL ON UPDATE CASCADE;
ALTER TABLE `CourseMonthlyRecord` ADD CONSTRAINT `CourseMonthlyRecord_accountItemId_fkey` FOREIGN KEY (`accountItemId`) REFERENCES `AccountItem`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `DriveSpreadsheetRevenueCourseLine` ADD CONSTRAINT `DriveSpreadsheetRevenueCourseLine_locationId_fkey` FOREIGN KEY (`locationId`) REFERENCES `Location`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `DriveSpreadsheetRevenueCourseLine` ADD CONSTRAINT `DriveSpreadsheetRevenueCourseLine_courseId_fkey` FOREIGN KEY (`courseId`) REFERENCES `Course`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `DriveSpreadsheetRevenueCourseLine` ADD CONSTRAINT `DriveSpreadsheetRevenueCourseLine_accountItemId_fkey` FOREIGN KEY (`accountItemId`) REFERENCES `AccountItem`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;

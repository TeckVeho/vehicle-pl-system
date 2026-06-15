-- CreateTable
CREATE TABLE `User` (
    `id` VARCHAR(191) NOT NULL,
    `email` VARCHAR(191) NOT NULL,
    `passwordHash` VARCHAR(191) NOT NULL,
    `name` VARCHAR(191) NOT NULL,
    `role` VARCHAR(191) NOT NULL,
    `externalId` VARCHAR(191) NULL,
    `createdAt` DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3),
    `updatedAt` DATETIME(3) NOT NULL,
    `deletedAt` DATETIME(3) NULL,

    UNIQUE INDEX `User_email_key`(`email`),
    INDEX `User_externalId_idx`(`externalId`),
    INDEX `User_role_idx`(`role`),
    PRIMARY KEY (`id`)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- CreateTable
CREATE TABLE `Location` (
    `id` VARCHAR(191) NOT NULL,
    `code` VARCHAR(191) NOT NULL,
    `name` VARCHAR(191) NOT NULL,
    `spreadsheetId` VARCHAR(191) NULL,
    `createdAt` DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3),
    `updatedAt` DATETIME(3) NOT NULL,

    UNIQUE INDEX `Location_code_key`(`code`),
    PRIMARY KEY (`id`)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- CreateTable
CREATE TABLE `Course` (
    `id` VARCHAR(191) NOT NULL,
    `locationId` VARCHAR(191) NOT NULL,
    `name` VARCHAR(191) NOT NULL,
    `code` VARCHAR(191) NOT NULL,
    `sortOrder` INTEGER NOT NULL DEFAULT 0,
    `externalId` VARCHAR(191) NULL,
    `createdAt` DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3),
    `updatedAt` DATETIME(3) NOT NULL,

    INDEX `Course_locationId_idx`(`locationId`),
    INDEX `Course_externalId_idx`(`externalId`),
    UNIQUE INDEX `Course_locationId_code_key`(`locationId`, `code`),
    PRIMARY KEY (`id`)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- CreateTable
CREATE TABLE `Vehicle` (
    `id` VARCHAR(191) NOT NULL,
    `locationId` VARCHAR(191) NOT NULL,
    `courseId` VARCHAR(191) NULL,
    `vehicleNo` VARCHAR(191) NOT NULL,
    `serviceType` VARCHAR(191) NULL,
    `tonnage` DOUBLE NULL,
    `externalId` VARCHAR(191) NULL,
    `createdAt` DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3),
    `updatedAt` DATETIME(3) NOT NULL,

    INDEX `Vehicle_locationId_idx`(`locationId`),
    INDEX `Vehicle_courseId_idx`(`courseId`),
    INDEX `Vehicle_externalId_idx`(`externalId`),
    UNIQUE INDEX `Vehicle_locationId_vehicleNo_key`(`locationId`, `vehicleNo`),
    PRIMARY KEY (`id`)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- CreateTable
CREATE TABLE `LocationMonthlyExpense` (
    `id` VARCHAR(191) NOT NULL,
    `locationId` VARCHAR(191) NOT NULL,
    `accountItemId` VARCHAR(191) NOT NULL,
    `yearMonth` VARCHAR(191) NOT NULL,
    `amount` DOUBLE NOT NULL DEFAULT 0,
    `createdAt` DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3),
    `updatedAt` DATETIME(3) NOT NULL,

    INDEX `LocationMonthlyExpense_yearMonth_idx`(`yearMonth`),
    INDEX `LocationMonthlyExpense_locationId_idx`(`locationId`),
    UNIQUE INDEX `LocationMonthlyExpense_locationId_accountItemId_yearMonth_key`(`locationId`, `accountItemId`, `yearMonth`),
    PRIMARY KEY (`id`)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- CreateTable
CREATE TABLE `VehicleMonthlyCost` (
    `id` VARCHAR(191) NOT NULL,
    `vehicleId` VARCHAR(191) NOT NULL,
    `yearMonth` VARCHAR(191) NOT NULL,
    `leaseDepreciation` DOUBLE NOT NULL DEFAULT 0,
    `vehicleDepreciation` DOUBLE NOT NULL DEFAULT 0,
    `vehicleLease` DOUBLE NOT NULL DEFAULT 0,
    `insuranceCost` DOUBLE NOT NULL DEFAULT 0,
    `taxCost` DOUBLE NOT NULL DEFAULT 0,
    `fuelEfficiency` DOUBLE NOT NULL DEFAULT 0,
    `roadUsageFee` DOUBLE NOT NULL DEFAULT 0,
    `createdAt` DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3),
    `updatedAt` DATETIME(3) NOT NULL,

    INDEX `VehicleMonthlyCost_yearMonth_idx`(`yearMonth`),
    INDEX `VehicleMonthlyCost_vehicleId_idx`(`vehicleId`),
    UNIQUE INDEX `VehicleMonthlyCost_vehicleId_yearMonth_key`(`vehicleId`, `yearMonth`),
    PRIMARY KEY (`id`)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- CreateTable
CREATE TABLE `LocationCalculationParameter` (
    `id` VARCHAR(191) NOT NULL,
    `locationId` VARCHAR(191) NOT NULL,
    `yearMonth` VARCHAR(191) NOT NULL,
    `fuelUnitPrice` DOUBLE NOT NULL DEFAULT 0,
    `roadUsageDiscountRate` DOUBLE NOT NULL DEFAULT 1,
    `createdAt` DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3),
    `updatedAt` DATETIME(3) NOT NULL,

    INDEX `LocationCalculationParameter_yearMonth_idx`(`yearMonth`),
    INDEX `LocationCalculationParameter_locationId_idx`(`locationId`),
    UNIQUE INDEX `LocationCalculationParameter_locationId_yearMonth_key`(`locationId`, `yearMonth`),
    PRIMARY KEY (`id`)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- CreateTable
CREATE TABLE `Driver` (
    `id` VARCHAR(191) NOT NULL,
    `locationId` VARCHAR(191) NOT NULL,
    `code` VARCHAR(191) NOT NULL,
    `name` VARCHAR(191) NOT NULL,
    `externalId` VARCHAR(191) NULL,
    `createdAt` DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3),
    `updatedAt` DATETIME(3) NOT NULL,

    INDEX `Driver_locationId_idx`(`locationId`),
    INDEX `Driver_externalId_idx`(`externalId`),
    UNIQUE INDEX `Driver_locationId_code_key`(`locationId`, `code`),
    PRIMARY KEY (`id`)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- CreateTable
CREATE TABLE `DailyDriverAssignment` (
    `id` VARCHAR(191) NOT NULL,
    `driverId` VARCHAR(191) NOT NULL,
    `vehicleId` VARCHAR(191) NOT NULL,
    `date` VARCHAR(191) NOT NULL,
    `yearMonth` VARCHAR(191) NOT NULL,
    `createdAt` DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3),
    `updatedAt` DATETIME(3) NOT NULL,

    INDEX `DailyDriverAssignment_yearMonth_vehicleId_idx`(`yearMonth`, `vehicleId`),
    INDEX `DailyDriverAssignment_yearMonth_driverId_idx`(`yearMonth`, `driverId`),
    UNIQUE INDEX `DailyDriverAssignment_driverId_vehicleId_date_key`(`driverId`, `vehicleId`, `date`),
    PRIMARY KEY (`id`)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- CreateTable
CREATE TABLE `DriverMonthlyAmount` (
    `id` VARCHAR(191) NOT NULL,
    `driverId` VARCHAR(191) NOT NULL,
    `accountItemId` VARCHAR(191) NOT NULL,
    `yearMonth` VARCHAR(191) NOT NULL,
    `amount` DOUBLE NOT NULL DEFAULT 0,
    `createdAt` DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3),
    `updatedAt` DATETIME(3) NOT NULL,

    INDEX `DriverMonthlyAmount_yearMonth_idx`(`yearMonth`),
    INDEX `DriverMonthlyAmount_driverId_idx`(`driverId`),
    UNIQUE INDEX `DriverMonthlyAmount_driverId_accountItemId_yearMonth_key`(`driverId`, `accountItemId`, `yearMonth`),
    PRIMARY KEY (`id`)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- CreateTable
CREATE TABLE `AccountItem` (
    `id` VARCHAR(191) NOT NULL,
    `code` VARCHAR(191) NOT NULL,
    `name` VARCHAR(191) NOT NULL,
    `category` VARCHAR(191) NOT NULL,
    `sortOrder` INTEGER NOT NULL,
    `isSubtotal` BOOLEAN NOT NULL DEFAULT false,
    `isVehicleRelated` BOOLEAN NOT NULL DEFAULT false,
    `isDriverRelated` BOOLEAN NOT NULL DEFAULT false,
    `revenuePricingType` VARCHAR(191) NULL,
    `linkageMethod` VARCHAR(191) NULL,
    `effectiveFrom` VARCHAR(191) NULL,
    `effectiveTo` VARCHAR(191) NULL,
    `createdAt` DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3),
    `updatedAt` DATETIME(3) NOT NULL,

    INDEX `AccountItem_category_idx`(`category`),
    INDEX `AccountItem_isVehicleRelated_idx`(`isVehicleRelated`),
    INDEX `AccountItem_isDriverRelated_idx`(`isDriverRelated`),
    UNIQUE INDEX `AccountItem_code_name_key`(`code`, `name`),
    PRIMARY KEY (`id`)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- CreateTable
CREATE TABLE `DailyOperatingRecord` (
    `id` VARCHAR(191) NOT NULL,
    `vehicleId` VARCHAR(191) NOT NULL,
    `date` VARCHAR(191) NOT NULL,
    `runCount` INTEGER NOT NULL DEFAULT 0,
    `isOperating` BOOLEAN NOT NULL DEFAULT false,
    `yearMonth` VARCHAR(191) NOT NULL,
    `createdAt` DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3),
    `updatedAt` DATETIME(3) NOT NULL,

    INDEX `DailyOperatingRecord_yearMonth_vehicleId_idx`(`yearMonth`, `vehicleId`),
    UNIQUE INDEX `DailyOperatingRecord_vehicleId_date_key`(`vehicleId`, `date`),
    PRIMARY KEY (`id`)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- CreateTable
CREATE TABLE `MonthlyRecord` (
    `id` VARCHAR(191) NOT NULL,
    `vehicleId` VARCHAR(191) NOT NULL,
    `accountItemId` VARCHAR(191) NOT NULL,
    `yearMonth` VARCHAR(191) NOT NULL,
    `amount` DOUBLE NOT NULL DEFAULT 0,
    `createdAt` DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3),
    `updatedAt` DATETIME(3) NOT NULL,

    INDEX `MonthlyRecord_yearMonth_idx`(`yearMonth`),
    INDEX `MonthlyRecord_vehicleId_idx`(`vehicleId`),
    UNIQUE INDEX `MonthlyRecord_vehicleId_accountItemId_yearMonth_key`(`vehicleId`, `accountItemId`, `yearMonth`),
    PRIMARY KEY (`id`)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- CreateTable
CREATE TABLE `MonthlyRecordHistory` (
    `id` VARCHAR(191) NOT NULL,
    `vehicleId` VARCHAR(191) NOT NULL,
    `accountItemId` VARCHAR(191) NOT NULL,
    `yearMonth` VARCHAR(191) NOT NULL,
    `oldAmount` DOUBLE NOT NULL,
    `newAmount` DOUBLE NOT NULL,
    `createdAt` DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3),
    `createdById` VARCHAR(191) NULL,

    INDEX `MonthlyRecordHistory_yearMonth_idx`(`yearMonth`),
    INDEX `MonthlyRecordHistory_vehicleId_idx`(`vehicleId`),
    INDEX `MonthlyRecordHistory_accountItemId_idx`(`accountItemId`),
    PRIMARY KEY (`id`)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- CreateTable
CREATE TABLE `ArbitraryInsuranceMaster` (
    `id` VARCHAR(191) NOT NULL,
    `tonnage` DOUBLE NOT NULL,
    `amount` DOUBLE NOT NULL DEFAULT 0,
    `sortOrder` INTEGER NOT NULL DEFAULT 0,
    `createdAt` DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3),
    `updatedAt` DATETIME(3) NOT NULL,

    UNIQUE INDEX `ArbitraryInsuranceMaster_tonnage_key`(`tonnage`),
    PRIMARY KEY (`id`)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- CreateTable
CREATE TABLE `DataSyncLog` (
    `id` VARCHAR(191) NOT NULL,
    `source` VARCHAR(191) NOT NULL,
    `syncType` VARCHAR(191) NOT NULL,
    `recordCount` INTEGER NOT NULL DEFAULT 0,
    `yearMonth` VARCHAR(191) NULL,
    `locationId` VARCHAR(191) NULL,
    `createdAt` DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3),

    INDEX `DataSyncLog_createdAt_idx`(`createdAt`),
    INDEX `DataSyncLog_source_idx`(`source`),
    PRIMARY KEY (`id`)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- AddForeignKey
ALTER TABLE `Course` ADD CONSTRAINT `Course_locationId_fkey` FOREIGN KEY (`locationId`) REFERENCES `Location`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- AddForeignKey
ALTER TABLE `Vehicle` ADD CONSTRAINT `Vehicle_locationId_fkey` FOREIGN KEY (`locationId`) REFERENCES `Location`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- AddForeignKey
ALTER TABLE `Vehicle` ADD CONSTRAINT `Vehicle_courseId_fkey` FOREIGN KEY (`courseId`) REFERENCES `Course`(`id`) ON DELETE SET NULL ON UPDATE CASCADE;

-- AddForeignKey
ALTER TABLE `LocationMonthlyExpense` ADD CONSTRAINT `LocationMonthlyExpense_locationId_fkey` FOREIGN KEY (`locationId`) REFERENCES `Location`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- AddForeignKey
ALTER TABLE `LocationMonthlyExpense` ADD CONSTRAINT `LocationMonthlyExpense_accountItemId_fkey` FOREIGN KEY (`accountItemId`) REFERENCES `AccountItem`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- AddForeignKey
ALTER TABLE `VehicleMonthlyCost` ADD CONSTRAINT `VehicleMonthlyCost_vehicleId_fkey` FOREIGN KEY (`vehicleId`) REFERENCES `Vehicle`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- AddForeignKey
ALTER TABLE `LocationCalculationParameter` ADD CONSTRAINT `LocationCalculationParameter_locationId_fkey` FOREIGN KEY (`locationId`) REFERENCES `Location`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- AddForeignKey
ALTER TABLE `Driver` ADD CONSTRAINT `Driver_locationId_fkey` FOREIGN KEY (`locationId`) REFERENCES `Location`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- AddForeignKey
ALTER TABLE `DailyDriverAssignment` ADD CONSTRAINT `DailyDriverAssignment_driverId_fkey` FOREIGN KEY (`driverId`) REFERENCES `Driver`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- AddForeignKey
ALTER TABLE `DailyDriverAssignment` ADD CONSTRAINT `DailyDriverAssignment_vehicleId_fkey` FOREIGN KEY (`vehicleId`) REFERENCES `Vehicle`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- AddForeignKey
ALTER TABLE `DriverMonthlyAmount` ADD CONSTRAINT `DriverMonthlyAmount_driverId_fkey` FOREIGN KEY (`driverId`) REFERENCES `Driver`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- AddForeignKey
ALTER TABLE `DriverMonthlyAmount` ADD CONSTRAINT `DriverMonthlyAmount_accountItemId_fkey` FOREIGN KEY (`accountItemId`) REFERENCES `AccountItem`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- AddForeignKey
ALTER TABLE `DailyOperatingRecord` ADD CONSTRAINT `DailyOperatingRecord_vehicleId_fkey` FOREIGN KEY (`vehicleId`) REFERENCES `Vehicle`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- AddForeignKey
ALTER TABLE `MonthlyRecord` ADD CONSTRAINT `MonthlyRecord_vehicleId_fkey` FOREIGN KEY (`vehicleId`) REFERENCES `Vehicle`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- AddForeignKey
ALTER TABLE `MonthlyRecord` ADD CONSTRAINT `MonthlyRecord_accountItemId_fkey` FOREIGN KEY (`accountItemId`) REFERENCES `AccountItem`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- AddForeignKey
ALTER TABLE `MonthlyRecordHistory` ADD CONSTRAINT `MonthlyRecordHistory_vehicleId_fkey` FOREIGN KEY (`vehicleId`) REFERENCES `Vehicle`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- AddForeignKey
ALTER TABLE `MonthlyRecordHistory` ADD CONSTRAINT `MonthlyRecordHistory_accountItemId_fkey` FOREIGN KEY (`accountItemId`) REFERENCES `AccountItem`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- AddForeignKey
ALTER TABLE `MonthlyRecordHistory` ADD CONSTRAINT `MonthlyRecordHistory_createdById_fkey` FOREIGN KEY (`createdById`) REFERENCES `User`(`id`) ON DELETE SET NULL ON UPDATE CASCADE;

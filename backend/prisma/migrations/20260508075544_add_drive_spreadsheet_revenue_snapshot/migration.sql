-- CreateTable
CREATE TABLE `LocationDriveSyncMeta` (
    `id` VARCHAR(191) NOT NULL,
    `locationId` VARCHAR(191) NOT NULL,
    `yearMonth` VARCHAR(191) NOT NULL,
    `driveFileId` VARCHAR(191) NOT NULL,
    `revenueSheetTab` VARCHAR(191) NULL,
    `status` VARCHAR(191) NOT NULL,
    `errorMessage` TEXT NULL,
    `syncedAt` DATETIME(3) NOT NULL,
    `recordCount` INTEGER NOT NULL DEFAULT 0,
    `createdAt` DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3),
    `updatedAt` DATETIME(3) NOT NULL,

    INDEX `LocationDriveSyncMeta_locationId_yearMonth_idx`(`locationId`, `yearMonth`),
    UNIQUE INDEX `LocationDriveSyncMeta_locationId_yearMonth_key`(`locationId`, `yearMonth`),
    PRIMARY KEY (`id`)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- CreateTable
CREATE TABLE `DriveSpreadsheetRevenueLine` (
    `id` VARCHAR(191) NOT NULL,
    `locationId` VARCHAR(191) NOT NULL,
    `yearMonth` VARCHAR(191) NOT NULL,
    `vehicleId` VARCHAR(191) NOT NULL,
    `accountItemId` VARCHAR(191) NOT NULL,
    `amount` DOUBLE NOT NULL DEFAULT 0,
    `createdAt` DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3),
    `updatedAt` DATETIME(3) NOT NULL,

    INDEX `DriveSpreadsheetRevenueLine_locationId_yearMonth_idx`(`locationId`, `yearMonth`),
    UNIQUE INDEX `DriveSpreadsheetRevenueLine_locationId_yearMonth_vehicleId_a_key`(`locationId`, `yearMonth`, `vehicleId`, `accountItemId`),
    PRIMARY KEY (`id`)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- AddForeignKey
ALTER TABLE `LocationDriveSyncMeta` ADD CONSTRAINT `LocationDriveSyncMeta_locationId_fkey` FOREIGN KEY (`locationId`) REFERENCES `Location`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- AddForeignKey
ALTER TABLE `DriveSpreadsheetRevenueLine` ADD CONSTRAINT `DriveSpreadsheetRevenueLine_locationId_fkey` FOREIGN KEY (`locationId`) REFERENCES `Location`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- AddForeignKey
ALTER TABLE `DriveSpreadsheetRevenueLine` ADD CONSTRAINT `DriveSpreadsheetRevenueLine_vehicleId_fkey` FOREIGN KEY (`vehicleId`) REFERENCES `Vehicle`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- AddForeignKey
ALTER TABLE `DriveSpreadsheetRevenueLine` ADD CONSTRAINT `DriveSpreadsheetRevenueLine_accountItemId_fkey` FOREIGN KEY (`accountItemId`) REFERENCES `AccountItem`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;

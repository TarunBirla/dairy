-- ==============================================================================
-- Mobile Dairy Software Database Update Script
-- Can be imported into phpMyAdmin / MySQL CLI on production server
-- ==============================================================================

-- 1. Ensure farmer_emergency_funds table exists
CREATE TABLE IF NOT EXISTS `farmer_emergency_funds` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `farmer_id` BIGINT UNSIGNED NOT NULL,
  `request_no` VARCHAR(255) NULL UNIQUE,
  `request_date` DATE NOT NULL,
  `amount` DECIMAL(10, 2) NOT NULL,
  `reason` TEXT NULL,
  `status` VARCHAR(50) NOT NULL DEFAULT 'approved',
  `approved_by` BIGINT UNSIGNED NULL,
  `disbursed_at` DATETIME NULL,
  `recovered_amount` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
  `payment_mode` VARCHAR(50) NOT NULL DEFAULT 'Cash',
  `notes` TEXT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_fef_farmer_id` (`farmer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Add columns to farmers table if they do not exist
SET @dbname = DATABASE();

-- name_hi
SET @col = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = 'farmers' AND COLUMN_NAME = 'name_hi');
SET @sql = IF(@col = 0, 'ALTER TABLE `farmers` ADD COLUMN `name_hi` VARCHAR(255) NULL AFTER `name`', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- photo
SET @col = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = 'farmers' AND COLUMN_NAME = 'photo');
SET @sql = IF(@col = 0, 'ALTER TABLE `farmers` ADD COLUMN `photo` VARCHAR(255) NULL AFTER `name_hi`', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- vehicle
SET @col = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = 'farmers' AND COLUMN_NAME = 'vehicle');
SET @sql = IF(@col = 0, 'ALTER TABLE `farmers` ADD COLUMN `vehicle` VARCHAR(100) NULL AFTER `address`', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- route_id
SET @col = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = 'farmers' AND COLUMN_NAME = 'route_id');
SET @sql = IF(@col = 0, 'ALTER TABLE `farmers` ADD COLUMN `route_id` BIGINT UNSIGNED NULL AFTER `vehicle`', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- cow_milk_rate
SET @col = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = 'farmers' AND COLUMN_NAME = 'cow_milk_rate');
SET @sql = IF(@col = 0, 'ALTER TABLE `farmers` ADD COLUMN `cow_milk_rate` DECIMAL(8, 2) NULL AFTER `animal_type`', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- buffalo_milk_rate
SET @col = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = 'farmers' AND COLUMN_NAME = 'buffalo_milk_rate');
SET @sql = IF(@col = 0, 'ALTER TABLE `farmers` ADD COLUMN `buffalo_milk_rate` DECIMAL(8, 2) NULL AFTER `cow_milk_rate`', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- branch_name
SET @col = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = 'farmers' AND COLUMN_NAME = 'branch_name');
SET @sql = IF(@col = 0, 'ALTER TABLE `farmers` ADD COLUMN `branch_name` VARCHAR(255) NULL AFTER `animal_type`', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- anamat
SET @col = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = 'farmers' AND COLUMN_NAME = 'anamat');
SET @sql = IF(@col = 0, 'ALTER TABLE `farmers` ADD COLUMN `anamat` DECIMAL(10, 2) NOT NULL DEFAULT 0.00 AFTER `current_balance`', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- building_fund
SET @col = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = 'farmers' AND COLUMN_NAME = 'building_fund');
SET @sql = IF(@col = 0, 'ALTER TABLE `farmers` ADD COLUMN `building_fund` DECIMAL(10, 2) NOT NULL DEFAULT 0.00 AFTER `anamat`', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- installment
SET @col = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = 'farmers' AND COLUMN_NAME = 'installment');
SET @sql = IF(@col = 0, 'ALTER TABLE `farmers` ADD COLUMN `installment` DECIMAL(10, 2) NOT NULL DEFAULT 0.00 AFTER `building_fund`', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- etc_amount
SET @col = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = 'farmers' AND COLUMN_NAME = 'etc_amount');
SET @sql = IF(@col = 0, 'ALTER TABLE `farmers` ADD COLUMN `etc_amount` DECIMAL(10, 2) NOT NULL DEFAULT 0.00 AFTER `installment`', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- grant_amount
SET @col = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = 'farmers' AND COLUMN_NAME = 'grant_amount');
SET @sql = IF(@col = 0, 'ALTER TABLE `farmers` ADD COLUMN `grant_amount` DECIMAL(10, 2) NOT NULL DEFAULT 0.00 AFTER `etc_amount`', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 3. Default Center Information Settings (Addition & Deduction Types)
INSERT INTO `system_settings` (`key`, `value`, `created_at`, `updated_at`)
VALUES
  ('center_addition_types', '[{\"name\":\"Incentive\",\"value\":\"Farmer Wise\"},{\"name\":\"Incentive Day\",\"value\":\"-\"},{\"name\":\"Subsidy\",\"value\":\"1.00/ltr\"},{\"name\":\"Transport\",\"value\":\"1.00/ltr\"},{\"name\":\"Other Payment Cr\",\"value\":\"-\"},{\"name\":\"Commission\",\"value\":\"0.00/ltr\"}]', NOW(), NOW())
ON DUPLICATE KEY UPDATE `updated_at` = NOW();

INSERT INTO `system_settings` (`key`, `value`, `created_at`, `updated_at`)
VALUES
  ('center_deduction_types', '[{\"name\":\"Anamat\",\"value\":\"0.00/ltr\"},{\"name\":\"Food\",\"value\":\"-\"},{\"name\":\"Advance\",\"value\":\"-\"}]', NOW(), NOW())
ON DUPLICATE KEY UPDATE `updated_at` = NOW();

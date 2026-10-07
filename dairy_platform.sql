-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: dairy_platform
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `audit_logs`
--

DROP TABLE IF EXISTS `audit_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `audit_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `model_type` varchar(255) DEFAULT NULL,
  `model_id` bigint(20) unsigned DEFAULT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payload`)),
  `ip_address` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `audit_logs`
--

LOCK TABLES `audit_logs` WRITE;
/*!40000 ALTER TABLE `audit_logs` DISABLE KEYS */;
INSERT INTO `audit_logs` VALUES (1,1,'Demo Switch Login','User',1,NULL,'127.0.0.1','2026-10-06 12:30:15','2026-10-06 12:30:15'),(2,1,'User Logged Out','User',1,NULL,'127.0.0.1','2026-10-06 12:37:27','2026-10-06 12:37:27'),(3,2,'Demo Switch Login','User',2,NULL,'127.0.0.1','2026-10-06 12:37:35','2026-10-06 12:37:35'),(4,2,'User Logged Out','User',2,NULL,'127.0.0.1','2026-10-06 12:38:06','2026-10-06 12:38:06'),(5,9,'Demo Switch Login','User',9,NULL,'127.0.0.1','2026-10-06 12:38:17','2026-10-06 12:38:17'),(6,9,'User Logged Out','User',9,NULL,'127.0.0.1','2026-10-06 12:40:17','2026-10-06 12:40:17'),(7,6,'Demo Switch Login','User',6,NULL,'127.0.0.1','2026-10-06 12:40:31','2026-10-06 12:40:31'),(8,6,'User Logged Out','User',6,NULL,'127.0.0.1','2026-10-06 12:41:18','2026-10-06 12:41:18'),(9,1,'Demo Switch Login','User',1,NULL,'127.0.0.1','2026-10-06 12:44:09','2026-10-06 12:44:09'),(10,2,'Demo Switch Login','User',2,NULL,'127.0.0.1','2026-10-06 12:44:44','2026-10-06 12:44:44'),(11,1,'Demo Switch Login','User',1,NULL,'127.0.0.1','2026-10-06 22:42:07','2026-10-06 22:42:07'),(12,1,'Toggled Stock Availability','Product',2,'{\"in_stock\":true}','127.0.0.1','2026-10-06 23:01:10','2026-10-06 23:01:10'),(13,1,'Toggled Stock Availability','Product',2,'{\"in_stock\":false}','127.0.0.1','2026-10-06 23:01:14','2026-10-06 23:01:14'),(14,1,'Quick Stock Added','Product',2,'{\"added\":\"40\"}','127.0.0.1','2026-10-06 23:01:29','2026-10-06 23:01:29'),(15,1,'Recorded Milk Collection','MilkCollection',8,'{\"liters\":\"10\",\"rate\":74.25,\"net\":746.5}','127.0.0.1','2026-10-06 23:11:25','2026-10-06 23:11:25'),(16,1,'Created Delivery Route','DeliveryRoute',3,NULL,'127.0.0.1','2026-10-06 23:24:57','2026-10-06 23:24:57'),(17,1,'User Logged Out','User',1,NULL,'127.0.0.1','2026-10-06 23:39:37','2026-10-06 23:39:37'),(18,1,'Demo Switch Login','User',1,NULL,'127.0.0.1','2026-10-07 00:04:22','2026-10-07 00:04:22'),(19,1,'User Logged Out','User',1,NULL,'127.0.0.1','2026-10-07 00:10:58','2026-10-07 00:10:58'),(20,1,'Demo Switch Login','User',1,NULL,'127.0.0.1','2026-10-07 00:12:14','2026-10-07 00:12:14'),(21,1,'Updated Customer Section: special_rate','Customer',1,NULL,'127.0.0.1','2026-10-07 00:13:23','2026-10-07 00:13:23');
/*!40000 ALTER TABLE `audit_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bottle_trackings`
--

DROP TABLE IF EXISTS `bottle_trackings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bottle_trackings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` bigint(20) unsigned NOT NULL,
  `issued_count` int(11) NOT NULL DEFAULT 0,
  `returned_count` int(11) NOT NULL DEFAULT 0,
  `broken_count` int(11) NOT NULL DEFAULT 0,
  `deposit_rate_per_bottle` decimal(8,2) NOT NULL DEFAULT 50.00,
  `total_deposit_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `balance_bottles` int(11) NOT NULL DEFAULT 0,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `bottle_trackings_customer_id_foreign` (`customer_id`),
  CONSTRAINT `bottle_trackings_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bottle_trackings`
--

LOCK TABLES `bottle_trackings` WRITE;
/*!40000 ALTER TABLE `bottle_trackings` DISABLE KEYS */;
INSERT INTO `bottle_trackings` VALUES (1,1,4,2,0,50.00,100.00,2,'Customer has 2 glass bottles on loan','2026-10-06 11:46:27','2026-10-06 11:46:27'),(2,2,4,2,0,50.00,100.00,2,'Customer has 2 glass bottles on loan','2026-10-06 11:46:27','2026-10-06 11:46:27'),(3,3,4,2,0,50.00,100.00,2,'Customer has 2 glass bottles on loan','2026-10-06 11:46:27','2026-10-06 11:46:27'),(4,4,4,2,0,50.00,100.00,2,'Customer has 2 glass bottles on loan','2026-10-06 11:46:27','2026-10-06 11:46:27'),(5,5,4,2,0,50.00,100.00,2,'Customer has 2 glass bottles on loan','2026-10-06 11:46:27','2026-10-06 11:46:27'),(6,6,4,2,0,50.00,100.00,2,'Customer has 2 glass bottles on loan','2026-10-06 11:46:27','2026-10-06 11:46:27');
/*!40000 ALTER TABLE `bottle_trackings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `branches`
--

DROP TABLE IF EXISTS `branches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `branches` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `code` varchar(255) NOT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `manager_id` bigint(20) unsigned DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `branches_code_unique` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `branches`
--

LOCK TABLES `branches` WRITE;
/*!40000 ALTER TABLE `branches` DISABLE KEYS */;
INSERT INTO `branches` VALUES (1,'Central Dairy Hub & Plant','BR-001','+91 98765 43210','hub@simpledairy.com','Plot 42, Dairy Processing Zone','Indore',2,'active','2026-10-06 11:46:26','2026-10-06 11:46:26',NULL),(2,'North Distribution Center','BR-002','+91 98765 43211','north@simpledairy.com','Station Road, North Zone','Indore',3,'active','2026-10-06 11:46:26','2026-10-06 11:46:26',NULL);
/*!40000 ALTER TABLE `branches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `categories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `categories_slug_unique` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES (1,'Fresh Dairy','fresh-dairy','Daily farm-fresh cow and buffalo milk products','2026-10-06 11:46:26','2026-10-06 11:46:26'),(2,'Traditional Sweets & Mawa','sweets-mawa','Pure khoya, mawa and dairy sweets','2026-10-06 11:46:26','2026-10-06 11:46:26'),(3,'Cattle Feed & Supplements','cattle-feed','Nutritional feed for dairy cattle','2026-10-06 11:46:26','2026-10-06 11:46:26'),(4,'text','text',NULL,'2026-10-06 23:08:26','2026-10-06 23:08:26');
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `collection_centers`
--

DROP TABLE IF EXISTS `collection_centers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `collection_centers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `branch_id` bigint(20) unsigned DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `code` varchar(255) NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `morning_shift_time` varchar(255) NOT NULL DEFAULT '06:00 - 09:30',
  `evening_shift_time` varchar(255) NOT NULL DEFAULT '17:00 - 20:30',
  `operator_id` bigint(20) unsigned DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `collection_centers_code_unique` (`code`),
  KEY `collection_centers_branch_id_foreign` (`branch_id`),
  CONSTRAINT `collection_centers_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `collection_centers`
--

LOCK TABLES `collection_centers` WRITE;
/*!40000 ALTER TABLE `collection_centers` DISABLE KEYS */;
INSERT INTO `collection_centers` VALUES (1,1,'Green Valley Mandi Center','CC-001','Village Green Valley, Sector 4','06:00 - 09:30','17:00 - 20:30',4,'active','2026-10-06 11:46:26','2026-10-06 11:46:26',NULL),(2,1,'Kisan Seva Kendra Depot','CC-002','Main Chowk, Village Palasia','06:00 - 09:30','17:00 - 20:30',4,'active','2026-10-06 11:46:26','2026-10-06 11:46:26',NULL);
/*!40000 ALTER TABLE `collection_centers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `counter_cashbooks`
--

DROP TABLE IF EXISTS `counter_cashbooks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `counter_cashbooks` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `branch_id` bigint(20) unsigned DEFAULT NULL,
  `counter_id` bigint(20) unsigned DEFAULT NULL,
  `operator_id` bigint(20) unsigned DEFAULT NULL,
  `entry_date` date NOT NULL,
  `opening_cash` decimal(10,2) NOT NULL DEFAULT 0.00,
  `cash_sales` decimal(10,2) NOT NULL DEFAULT 0.00,
  `cash_expenses` decimal(10,2) NOT NULL DEFAULT 0.00,
  `closing_cash` decimal(10,2) NOT NULL DEFAULT 0.00,
  `actual_counted_cash` decimal(10,2) NOT NULL DEFAULT 0.00,
  `variance` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` varchar(255) NOT NULL DEFAULT 'open',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `counter_cashbooks_branch_id_foreign` (`branch_id`),
  KEY `counter_cashbooks_counter_id_foreign` (`counter_id`),
  CONSTRAINT `counter_cashbooks_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL,
  CONSTRAINT `counter_cashbooks_counter_id_foreign` FOREIGN KEY (`counter_id`) REFERENCES `pos_counters` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `counter_cashbooks`
--

LOCK TABLES `counter_cashbooks` WRITE;
/*!40000 ALTER TABLE `counter_cashbooks` DISABLE KEYS */;
INSERT INTO `counter_cashbooks` VALUES (1,1,1,7,'2026-10-06',2000.00,3450.00,150.00,5300.00,5300.00,0.00,'open','Today counter running smoothly','2026-10-06 11:46:27','2026-10-06 11:46:27');
/*!40000 ALTER TABLE `counter_cashbooks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `customer_addresses`
--

DROP TABLE IF EXISTS `customer_addresses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `customer_addresses` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` bigint(20) unsigned NOT NULL,
  `address_type` varchar(255) NOT NULL DEFAULT 'home',
  `address_line` text NOT NULL,
  `landmark` varchar(255) DEFAULT NULL,
  `pincode` varchar(255) DEFAULT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `customer_addresses_customer_id_foreign` (`customer_id`),
  CONSTRAINT `customer_addresses_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customer_addresses`
--

LOCK TABLES `customer_addresses` WRITE;
/*!40000 ALTER TABLE `customer_addresses` DISABLE KEYS */;
INSERT INTO `customer_addresses` VALUES (1,1,'home','Flat 102, Royal Residency, Vijay Nagar','Near City Garden','452010',1,'2026-10-06 11:46:27','2026-10-06 11:46:27'),(2,2,'home','Flat 102, Royal Residency, Scheme 54','Near City Garden','452010',1,'2026-10-06 11:46:27','2026-10-06 11:46:27'),(3,3,'home','Flat 102, Royal Residency, Bapat Square','Near City Garden','452010',1,'2026-10-06 11:46:27','2026-10-06 11:46:27'),(4,4,'home','Flat 102, Royal Residency, Old Palasia','Near City Garden','452010',1,'2026-10-06 11:46:27','2026-10-06 11:46:27'),(5,5,'home','Flat 102, Royal Residency, Geeta Bhawan','Near City Garden','452010',1,'2026-10-06 11:46:27','2026-10-06 11:46:27'),(6,6,'home','Flat 102, Royal Residency, Scheme 54','Near City Garden','452010',1,'2026-10-06 11:46:27','2026-10-06 11:46:27');
/*!40000 ALTER TABLE `customer_addresses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `customer_bottle_openings`
--

DROP TABLE IF EXISTS `customer_bottle_openings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `customer_bottle_openings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` bigint(20) unsigned NOT NULL,
  `product_id` bigint(20) unsigned NOT NULL,
  `opening_count` int(11) NOT NULL DEFAULT 0,
  `is_locked` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `customer_bottle_openings_customer_id_product_id_unique` (`customer_id`,`product_id`),
  KEY `customer_bottle_openings_product_id_foreign` (`product_id`),
  CONSTRAINT `customer_bottle_openings_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `customer_bottle_openings_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customer_bottle_openings`
--

LOCK TABLES `customer_bottle_openings` WRITE;
/*!40000 ALTER TABLE `customer_bottle_openings` DISABLE KEYS */;
/*!40000 ALTER TABLE `customer_bottle_openings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `customer_group_pivot`
--

DROP TABLE IF EXISTS `customer_group_pivot`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `customer_group_pivot` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` bigint(20) unsigned NOT NULL,
  `customer_group_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `customer_group_pivot_customer_id_customer_group_id_unique` (`customer_id`,`customer_group_id`),
  KEY `customer_group_pivot_customer_group_id_foreign` (`customer_group_id`),
  CONSTRAINT `customer_group_pivot_customer_group_id_foreign` FOREIGN KEY (`customer_group_id`) REFERENCES `customer_groups` (`id`) ON DELETE CASCADE,
  CONSTRAINT `customer_group_pivot_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customer_group_pivot`
--

LOCK TABLES `customer_group_pivot` WRITE;
/*!40000 ALTER TABLE `customer_group_pivot` DISABLE KEYS */;
/*!40000 ALTER TABLE `customer_group_pivot` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `customer_groups`
--

DROP TABLE IF EXISTS `customer_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `customer_groups` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `color` varchar(30) NOT NULL DEFAULT '#10B981',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `customer_groups_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customer_groups`
--

LOCK TABLES `customer_groups` WRITE;
/*!40000 ALTER TABLE `customer_groups` DISABLE KEYS */;
/*!40000 ALTER TABLE `customer_groups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `customer_ledgers`
--

DROP TABLE IF EXISTS `customer_ledgers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `customer_ledgers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` bigint(20) unsigned NOT NULL,
  `transaction_date` date NOT NULL,
  `type` varchar(255) NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `balance` decimal(12,2) NOT NULL,
  `reference_type` varchar(255) DEFAULT NULL,
  `reference_id` bigint(20) unsigned DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `customer_ledgers_customer_id_foreign` (`customer_id`),
  CONSTRAINT `customer_ledgers_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customer_ledgers`
--

LOCK TABLES `customer_ledgers` WRITE;
/*!40000 ALTER TABLE `customer_ledgers` DISABLE KEYS */;
/*!40000 ALTER TABLE `customer_ledgers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `customer_special_rates`
--

DROP TABLE IF EXISTS `customer_special_rates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `customer_special_rates` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` bigint(20) unsigned NOT NULL,
  `product_id` bigint(20) unsigned NOT NULL,
  `special_price` decimal(10,2) NOT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `customer_special_rates_customer_id_product_id_unique` (`customer_id`,`product_id`),
  KEY `customer_special_rates_product_id_foreign` (`product_id`),
  CONSTRAINT `customer_special_rates_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `customer_special_rates_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customer_special_rates`
--

LOCK TABLES `customer_special_rates` WRITE;
/*!40000 ALTER TABLE `customer_special_rates` DISABLE KEYS */;
INSERT INTO `customer_special_rates` VALUES (1,1,1,320.00,NULL,NULL,1,'2026-10-07 00:13:23','2026-10-07 00:13:23');
/*!40000 ALTER TABLE `customer_special_rates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `customers`
--

DROP TABLE IF EXISTS `customers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `customers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `customer_code` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `branch_id` bigint(20) unsigned DEFAULT NULL,
  `route_id` bigint(20) unsigned DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `locality` varchar(255) DEFAULT NULL,
  `category` varchar(255) NOT NULL DEFAULT 'household',
  `credit_limit` decimal(10,2) NOT NULL DEFAULT 1000.00,
  `current_balance` decimal(12,2) NOT NULL DEFAULT 0.00,
  `delivery_sequence` int(11) NOT NULL DEFAULT 1,
  `delivery_instructions` text DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `customers_customer_code_unique` (`customer_code`),
  KEY `customers_user_id_foreign` (`user_id`),
  KEY `customers_branch_id_foreign` (`branch_id`),
  KEY `customers_route_id_foreign` (`route_id`),
  CONSTRAINT `customers_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL,
  CONSTRAINT `customers_route_id_foreign` FOREIGN KEY (`route_id`) REFERENCES `delivery_routes` (`id`) ON DELETE SET NULL,
  CONSTRAINT `customers_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customers`
--

LOCK TABLES `customers` WRITE;
/*!40000 ALTER TABLE `customers` DISABLE KEYS */;
INSERT INTO `customers` VALUES (1,'CUST-201',9,1,1,'Sunil Mehta','9827011111','sunilmehta@example.com','Flat 102, Royal Residency, Vijay Nagar','Vijay Nagar','household',2000.00,840.00,1,'Ring bell once, leave at door pouch.','active','2026-10-06 11:46:27','2026-10-06 11:46:27',NULL),(2,'CUST-202',NULL,1,1,'Dr. Ananya Roy','9827022222','dr.ananyaroy@example.com','Flat 102, Royal Residency, Scheme 54','Scheme 54','household',2000.00,1250.00,2,'Ring bell once, leave at door pouch.','active','2026-10-06 11:46:27','2026-10-06 11:46:27',NULL),(3,'CUST-203',NULL,1,1,'Hotel Shanti Palace','9827033333','hotelshantipalace@example.com','Flat 102, Royal Residency, Bapat Square','Bapat Square','hotel',2000.00,4500.00,3,'Ring bell once, leave at door pouch.','active','2026-10-06 11:46:27','2026-10-06 11:46:27',NULL),(4,'CUST-204',NULL,1,2,'Pooja Agarwal','9827044444','poojaagarwal@example.com','Flat 102, Royal Residency, Old Palasia','Old Palasia','household',2000.00,0.00,1,'Ring bell once, leave at door pouch.','active','2026-10-06 11:46:27','2026-10-06 11:46:27',NULL),(5,'CUST-205',NULL,1,2,'Manoj Sweet Shop','9827055555','manojsweetshop@example.com','Flat 102, Royal Residency, Geeta Bhawan','Geeta Bhawan','shop',2000.00,2800.00,2,'Ring bell once, leave at door pouch.','active','2026-10-06 11:46:27','2026-10-06 11:46:27',NULL),(6,'CUST-206',NULL,1,1,'Kavita Joshi','9827066666','kavitajoshi@example.com','Flat 102, Royal Residency, Scheme 54','Scheme 54','household',2000.00,420.00,4,'Ring bell once, leave at door pouch.','active','2026-10-06 11:46:27','2026-10-06 11:46:27',NULL);
/*!40000 ALTER TABLE `customers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `daily_deliveries`
--

DROP TABLE IF EXISTS `daily_deliveries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `daily_deliveries` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `delivery_date` date NOT NULL,
  `shift` varchar(255) NOT NULL DEFAULT 'morning',
  `route_id` bigint(20) unsigned DEFAULT NULL,
  `delivery_boy_id` bigint(20) unsigned DEFAULT NULL,
  `customer_id` bigint(20) unsigned NOT NULL,
  `subscription_id` bigint(20) unsigned DEFAULT NULL,
  `product_id` bigint(20) unsigned NOT NULL,
  `quantity` decimal(6,2) NOT NULL,
  `extra_quantity` decimal(6,2) NOT NULL DEFAULT 0.00,
  `delivered_quantity` decimal(6,2) NOT NULL DEFAULT 0.00,
  `unit_price` decimal(8,2) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `failure_reason` varchar(255) DEFAULT NULL,
  `cash_collected` decimal(10,2) NOT NULL DEFAULT 0.00,
  `notes` text DEFAULT NULL,
  `delivered_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `daily_deliveries_route_id_foreign` (`route_id`),
  KEY `daily_deliveries_customer_id_foreign` (`customer_id`),
  KEY `daily_deliveries_subscription_id_foreign` (`subscription_id`),
  CONSTRAINT `daily_deliveries_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `daily_deliveries_route_id_foreign` FOREIGN KEY (`route_id`) REFERENCES `delivery_routes` (`id`) ON DELETE SET NULL,
  CONSTRAINT `daily_deliveries_subscription_id_foreign` FOREIGN KEY (`subscription_id`) REFERENCES `subscriptions` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `daily_deliveries`
--

LOCK TABLES `daily_deliveries` WRITE;
/*!40000 ALTER TABLE `daily_deliveries` DISABLE KEYS */;
INSERT INTO `daily_deliveries` VALUES (1,'2026-10-06','morning',1,6,1,1,7,2.00,0.00,2.00,58.00,116.00,'delivered',NULL,116.00,'Left at doorstep. Cash collected.','2026-10-06 09:46:27','2026-10-06 11:46:27','2026-10-06 11:46:27'),(2,'2026-10-06','morning',1,6,2,2,4,1.50,0.00,1.50,48.00,72.00,'delivered',NULL,0.00,'Monthly billing customer.','2026-10-06 10:46:27','2026-10-06 11:46:27','2026-10-06 11:46:27'),(3,'2026-10-06','morning',1,6,3,3,4,10.00,0.00,0.00,46.00,460.00,'pending',NULL,0.00,'Bulk supply to hotel kitchen',NULL,'2026-10-06 11:46:27','2026-10-06 11:46:27'),(4,'2026-10-06','morning',2,6,4,4,7,1.00,0.00,0.00,58.00,0.00,'skipped','Customer on leave / Vacation pause',0.00,NULL,NULL,'2026-10-06 11:46:27','2026-10-06 11:46:27'),(5,'2026-10-07','morning',1,6,1,1,7,2.00,0.00,0.00,58.00,116.00,'pending',NULL,0.00,NULL,NULL,'2026-10-06 23:25:11','2026-10-06 23:25:11'),(6,'2026-10-07','morning',1,6,2,2,4,1.50,0.00,1.50,48.00,72.00,'delivered',NULL,0.00,NULL,'2026-10-06 23:25:46','2026-10-06 23:25:11','2026-10-06 23:25:46'),(7,'2026-10-07','morning',1,6,3,3,4,10.00,0.00,10.00,46.00,460.00,'delivered',NULL,0.00,NULL,'2026-10-06 23:25:41','2026-10-06 23:25:11','2026-10-06 23:25:41');
/*!40000 ALTER TABLE `daily_deliveries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `delivery_routes`
--

DROP TABLE IF EXISTS `delivery_routes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `delivery_routes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `branch_id` bigint(20) unsigned DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `code` varchar(255) NOT NULL,
  `area_name` varchar(255) DEFAULT NULL,
  `delivery_boy_id` bigint(20) unsigned DEFAULT NULL,
  `description` text DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `delivery_routes_code_unique` (`code`),
  KEY `delivery_routes_branch_id_foreign` (`branch_id`),
  CONSTRAINT `delivery_routes_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `delivery_routes`
--

LOCK TABLES `delivery_routes` WRITE;
/*!40000 ALTER TABLE `delivery_routes` DISABLE KEYS */;
INSERT INTO `delivery_routes` VALUES (1,1,'Route A - Scheme 54 & Vijay Nagar','RT-001','Vijay Nagar, Scheme 54, Bapat Square',6,'Morning delivery run starting 05:30 AM','active','2026-10-06 11:46:27','2026-10-06 11:46:27'),(2,1,'Route B - Old Palasia & Manoramaganj','RT-002','Old Palasia, Geeta Bhawan, Navlakha',6,'Morning delivery run starting 06:15 AM','active','2026-10-06 11:46:27','2026-10-06 11:46:27'),(3,NULL,'hirapur to Bediya','RT-003','Delivery Boy',6,NULL,'active','2026-10-06 23:24:57','2026-10-06 23:24:57');
/*!40000 ALTER TABLE `delivery_routes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `expense_categories`
--

DROP TABLE IF EXISTS `expense_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `expense_categories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `expense_categories_slug_unique` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `expense_categories`
--

LOCK TABLES `expense_categories` WRITE;
/*!40000 ALTER TABLE `expense_categories` DISABLE KEYS */;
INSERT INTO `expense_categories` VALUES (1,'Cattle Feed & Fodder','cattle-feed-exp','Purchase of fodder, straw, khalli','2026-10-06 11:46:27','2026-10-06 11:46:27'),(2,'Fuel & Transportation','fuel-transport','Delivery bikes fuel, van diesel','2026-10-06 11:46:27','2026-10-06 11:46:27'),(3,'Electricity & Chilling Unit','electricity','Bulk milk cooler (BMC) and plant power','2026-10-06 11:46:27','2026-10-06 11:46:27'),(4,'Staff Salaries & Wages','salaries','Delivery staff and plant labor','2026-10-06 11:46:27','2026-10-06 11:46:27'),(5,'Packaging & Bottles','packaging','Pouches, glass bottles, caps, crates','2026-10-06 11:46:27','2026-10-06 11:46:27');
/*!40000 ALTER TABLE `expense_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `expenses`
--

DROP TABLE IF EXISTS `expenses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `expenses` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `branch_id` bigint(20) unsigned DEFAULT NULL,
  `expense_category_id` bigint(20) unsigned NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `expense_date` date NOT NULL,
  `payment_mode` varchar(255) NOT NULL DEFAULT 'cash',
  `vendor_name` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `receipt_file` varchar(255) DEFAULT NULL,
  `recorded_by` bigint(20) unsigned DEFAULT NULL,
  `approved_by` bigint(20) unsigned DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'approved',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `expenses_branch_id_foreign` (`branch_id`),
  KEY `expenses_expense_category_id_foreign` (`expense_category_id`),
  CONSTRAINT `expenses_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL,
  CONSTRAINT `expenses_expense_category_id_foreign` FOREIGN KEY (`expense_category_id`) REFERENCES `expense_categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `expenses`
--

LOCK TABLES `expenses` WRITE;
/*!40000 ALTER TABLE `expenses` DISABLE KEYS */;
INSERT INTO `expenses` VALUES (1,1,2,650.00,'2026-10-06','cash','Indian Oil Petrol Pump','Fuel for delivery bikes Route A & Route B',NULL,5,2,'approved','2026-10-06 11:46:27','2026-10-06 11:46:27'),(2,1,3,4200.00,'2026-09-30','bank_transfer','MP Electricity Board','Chiller unit power bill for collection center',NULL,5,2,'approved','2026-10-06 11:46:27','2026-10-06 11:46:27');
/*!40000 ALTER TABLE `expenses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `farmer_advances`
--

DROP TABLE IF EXISTS `farmer_advances`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `farmer_advances` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `farmer_id` bigint(20) unsigned NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `advance_date` date NOT NULL,
  `purpose` varchar(255) DEFAULT NULL,
  `deducted_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `farmer_advances_farmer_id_foreign` (`farmer_id`),
  CONSTRAINT `farmer_advances_farmer_id_foreign` FOREIGN KEY (`farmer_id`) REFERENCES `farmers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `farmer_advances`
--

LOCK TABLES `farmer_advances` WRITE;
/*!40000 ALTER TABLE `farmer_advances` DISABLE KEYS */;
INSERT INTO `farmer_advances` VALUES (1,1,1500.00,'2026-10-01','Cattle feed purchase advance',500.00,'partially_deducted','To be deducted in next 2 billing cycles','2026-10-06 11:46:26','2026-10-06 11:46:26');
/*!40000 ALTER TABLE `farmer_advances` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `farmer_ledgers`
--

DROP TABLE IF EXISTS `farmer_ledgers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `farmer_ledgers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `farmer_id` bigint(20) unsigned NOT NULL,
  `transaction_date` date NOT NULL,
  `type` varchar(255) NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `balance` decimal(12,2) NOT NULL,
  `reference_type` varchar(255) DEFAULT NULL,
  `reference_id` bigint(20) unsigned DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `farmer_ledgers_farmer_id_foreign` (`farmer_id`),
  CONSTRAINT `farmer_ledgers_farmer_id_foreign` FOREIGN KEY (`farmer_id`) REFERENCES `farmers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `farmer_ledgers`
--

LOCK TABLES `farmer_ledgers` WRITE;
/*!40000 ALTER TABLE `farmer_ledgers` DISABLE KEYS */;
INSERT INTO `farmer_ledgers` VALUES (1,1,'2026-10-06','credit',989.21,4250.00,'milk_collection',1,'Milk Collection morning (15.5 Ltr @ ₹63.82/Ltr)','2026-10-06 11:46:26','2026-10-06 11:46:26'),(2,2,'2026-10-06','credit',1987.92,8400.00,'milk_collection',2,'Milk Collection morning (22 Ltr @ ₹90.36/Ltr)','2026-10-06 11:46:26','2026-10-06 11:46:26'),(3,3,'2026-10-06','credit',731.52,3120.00,'milk_collection',3,'Milk Collection morning (12 Ltr @ ₹60.96/Ltr)','2026-10-06 11:46:26','2026-10-06 11:46:26'),(4,4,'2026-10-06','credit',2662.47,12500.00,'milk_collection',4,'Milk Collection morning (28.5 Ltr @ ₹93.42/Ltr)','2026-10-06 11:46:26','2026-10-06 11:46:26'),(5,1,'2026-10-05','credit',878.22,4250.00,'milk_collection',5,'Milk Collection evening (14 Ltr @ ₹62.73/Ltr)','2026-10-06 11:46:26','2026-10-06 11:46:26'),(6,2,'2026-10-05','credit',1769.40,8400.00,'milk_collection',6,'Milk Collection evening (20 Ltr @ ₹88.47/Ltr)','2026-10-06 11:46:26','2026-10-06 11:46:26'),(7,5,'2026-10-05','credit',1192.86,1890.00,'milk_collection',7,'Milk Collection morning (18 Ltr @ ₹66.27/Ltr)','2026-10-06 11:46:26','2026-10-06 11:46:26'),(8,4,'2026-10-07','credit',746.50,13246.50,'milk_collection',8,'Milk Collection morning (10 Ltr @ ₹74.25/Ltr)','2026-10-06 23:11:25','2026-10-06 23:11:25');
/*!40000 ALTER TABLE `farmer_ledgers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `farmer_loans`
--

DROP TABLE IF EXISTS `farmer_loans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `farmer_loans` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `farmer_id` bigint(20) unsigned NOT NULL,
  `principal_amount` decimal(12,2) NOT NULL,
  `installments_count` int(11) NOT NULL DEFAULT 1,
  `installment_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_repaid` decimal(12,2) NOT NULL DEFAULT 0.00,
  `remaining_amount` decimal(12,2) NOT NULL,
  `start_date` date NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `farmer_loans_farmer_id_foreign` (`farmer_id`),
  CONSTRAINT `farmer_loans_farmer_id_foreign` FOREIGN KEY (`farmer_id`) REFERENCES `farmers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `farmer_loans`
--

LOCK TABLES `farmer_loans` WRITE;
/*!40000 ALTER TABLE `farmer_loans` DISABLE KEYS */;
/*!40000 ALTER TABLE `farmer_loans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `farmer_settlements`
--

DROP TABLE IF EXISTS `farmer_settlements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `farmer_settlements` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `settlement_number` varchar(255) NOT NULL,
  `farmer_id` bigint(20) unsigned NOT NULL,
  `period_start` date NOT NULL,
  `period_end` date NOT NULL,
  `total_liters` decimal(10,2) NOT NULL,
  `gross_amount` decimal(12,2) NOT NULL,
  `bonus_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `deduction_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `advance_recovered` decimal(10,2) NOT NULL DEFAULT 0.00,
  `net_payable` decimal(12,2) NOT NULL,
  `paid_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `payment_mode` varchar(255) DEFAULT NULL,
  `payment_reference` varchar(255) DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'unsettled',
  `settled_by` bigint(20) unsigned DEFAULT NULL,
  `settled_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `farmer_settlements_settlement_number_unique` (`settlement_number`),
  KEY `farmer_settlements_farmer_id_foreign` (`farmer_id`),
  CONSTRAINT `farmer_settlements_farmer_id_foreign` FOREIGN KEY (`farmer_id`) REFERENCES `farmers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `farmer_settlements`
--

LOCK TABLES `farmer_settlements` WRITE;
/*!40000 ALTER TABLE `farmer_settlements` DISABLE KEYS */;
/*!40000 ALTER TABLE `farmer_settlements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `farmers`
--

DROP TABLE IF EXISTS `farmers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `farmers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `farmer_code` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `branch_id` bigint(20) unsigned DEFAULT NULL,
  `collection_center_id` bigint(20) unsigned DEFAULT NULL,
  `rate_chart_id` bigint(20) unsigned DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `village` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `supplier_type` varchar(255) NOT NULL DEFAULT 'farmer',
  `animal_type` varchar(255) NOT NULL DEFAULT 'cow',
  `bank_name` varchar(255) DEFAULT NULL,
  `account_number` varchar(255) DEFAULT NULL,
  `ifsc_code` varchar(255) DEFAULT NULL,
  `upi_id` varchar(255) DEFAULT NULL,
  `custom_rate_override` decimal(8,2) DEFAULT NULL,
  `current_balance` decimal(12,2) NOT NULL DEFAULT 0.00,
  `status` varchar(255) NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `farmers_farmer_code_unique` (`farmer_code`),
  KEY `farmers_user_id_foreign` (`user_id`),
  KEY `farmers_branch_id_foreign` (`branch_id`),
  KEY `farmers_collection_center_id_foreign` (`collection_center_id`),
  KEY `farmers_rate_chart_id_foreign` (`rate_chart_id`),
  CONSTRAINT `farmers_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL,
  CONSTRAINT `farmers_collection_center_id_foreign` FOREIGN KEY (`collection_center_id`) REFERENCES `collection_centers` (`id`) ON DELETE SET NULL,
  CONSTRAINT `farmers_rate_chart_id_foreign` FOREIGN KEY (`rate_chart_id`) REFERENCES `rate_charts` (`id`) ON DELETE SET NULL,
  CONSTRAINT `farmers_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `farmers`
--

LOCK TABLES `farmers` WRITE;
/*!40000 ALTER TABLE `farmers` DISABLE KEYS */;
INSERT INTO `farmers` VALUES (1,'FAR-101',8,1,1,1,'Ramesh Patel','9826011111','Palasia','Village Palasia, Tehsil Indore','farmer','cow','State Bank of India','30891283712','SBIN0001234','ramesh@upi',NULL,4250.00,'active','2026-10-06 11:46:26','2026-10-06 11:46:26',NULL),(2,'FAR-102',NULL,1,1,2,'Suresh Yadav','9826022222','Green Valley','Village Green Valley, Tehsil Indore','farmer','buffalo','Bank of Baroda','09281293847','BARB0INDORE','suresh@ybl',NULL,8400.00,'active','2026-10-06 11:46:26','2026-10-06 11:46:26',NULL),(3,'FAR-103',NULL,1,1,1,'Mukesh Sharma','9826033333','Pipaliya','Village Pipaliya, Tehsil Indore','farmer','cow','Punjab National Bank','49281928374','PUNB0192800','mukesh@paytm',NULL,3120.00,'active','2026-10-06 11:46:26','2026-10-06 11:46:26',NULL),(4,'FAR-104',NULL,1,1,2,'Gopal Bhai','9826044444','Palasia','Village Palasia, Tehsil Indore','farmer','buffalo','HDFC Bank','50100293847','HDFC0000241','gopal@okaxis',NULL,13246.50,'active','2026-10-06 11:46:26','2026-10-06 23:11:25',NULL),(5,'FAR-105',NULL,1,1,1,'Kailash Choudhary','9826055555','Bicholi','Village Bicholi, Tehsil Indore','farmer','mixed','Central Bank','21928374619','CBIN0281928','kailash@upi',NULL,1890.00,'active','2026-10-06 11:46:26','2026-10-06 11:46:26',NULL),(6,'FAR-106',NULL,1,1,2,'Jagdish Gurjar','9826066666','Green Valley','Village Green Valley, Tehsil Indore','farmer','buffalo','Union Bank','59281928374','UBIN0549281','jagdish@icici',NULL,6700.00,'active','2026-10-06 11:46:26','2026-10-06 11:46:26',NULL);
/*!40000 ALTER TABLE `farmers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `inventory_transactions`
--

DROP TABLE IF EXISTS `inventory_transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `inventory_transactions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint(20) unsigned NOT NULL,
  `branch_id` bigint(20) unsigned DEFAULT NULL,
  `transaction_type` varchar(255) NOT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `unit_cost` decimal(8,2) NOT NULL DEFAULT 0.00,
  `balance_after` decimal(10,2) NOT NULL DEFAULT 0.00,
  `reference_type` varchar(255) DEFAULT NULL,
  `reference_id` bigint(20) unsigned DEFAULT NULL,
  `recorded_by` bigint(20) unsigned DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `inventory_transactions_product_id_foreign` (`product_id`),
  KEY `inventory_transactions_branch_id_foreign` (`branch_id`),
  CONSTRAINT `inventory_transactions_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL,
  CONSTRAINT `inventory_transactions_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inventory_transactions`
--

LOCK TABLES `inventory_transactions` WRITE;
/*!40000 ALTER TABLE `inventory_transactions` DISABLE KEYS */;
INSERT INTO `inventory_transactions` VALUES (1,1,1,'purchase_inward',8.00,230.00,8.00,NULL,NULL,NULL,'Opening stock setup','2026-10-06 11:46:26','2026-10-06 11:46:26'),(2,4,1,'purchase_inward',45.00,40.00,45.00,NULL,NULL,NULL,'Opening stock setup','2026-10-06 11:46:27','2026-10-06 11:46:27'),(3,6,1,'purchase_inward',12.00,2100.00,12.00,NULL,NULL,NULL,'Opening stock setup','2026-10-06 11:46:27','2026-10-06 11:46:27'),(4,7,1,'purchase_inward',18.00,45.00,18.00,NULL,NULL,NULL,'Opening stock setup','2026-10-06 11:46:27','2026-10-06 11:46:27'),(5,2,NULL,'production_inward',40.00,0.00,40.00,NULL,NULL,1,'Quick stock top-up from catalog','2026-10-06 23:01:29','2026-10-06 23:01:29');
/*!40000 ALTER TABLE `inventory_transactions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `invoice_items`
--

DROP TABLE IF EXISTS `invoice_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `invoice_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `invoice_id` bigint(20) unsigned NOT NULL,
  `product_id` bigint(20) unsigned DEFAULT NULL,
  `description` varchar(255) NOT NULL,
  `quantity` decimal(8,2) NOT NULL,
  `unit_price` decimal(8,2) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `invoice_items_invoice_id_foreign` (`invoice_id`),
  KEY `invoice_items_product_id_foreign` (`product_id`),
  CONSTRAINT `invoice_items_invoice_id_foreign` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE,
  CONSTRAINT `invoice_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `invoice_items`
--

LOCK TABLES `invoice_items` WRITE;
/*!40000 ALTER TABLE `invoice_items` DISABLE KEYS */;
INSERT INTO `invoice_items` VALUES (1,1,4,'सादा दूध (Standard Cow Milk) - 30 days @ 1.5 L/day',45.00,48.00,1440.00,'2026-10-06 11:46:27','2026-10-06 11:46:27');
/*!40000 ALTER TABLE `invoice_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `invoices`
--

DROP TABLE IF EXISTS `invoices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `invoices` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `invoice_number` varchar(255) NOT NULL,
  `customer_id` bigint(20) unsigned NOT NULL,
  `branch_id` bigint(20) unsigned DEFAULT NULL,
  `period_start` date NOT NULL,
  `period_end` date NOT NULL,
  `invoice_date` date NOT NULL,
  `due_date` date NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `tax_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `discount_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `previous_due` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(10,2) NOT NULL,
  `paid_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `balance_due` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` varchar(255) NOT NULL DEFAULT 'unpaid',
  `billing_type` varchar(255) NOT NULL DEFAULT 'subscription',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `invoices_invoice_number_unique` (`invoice_number`),
  KEY `invoices_customer_id_foreign` (`customer_id`),
  KEY `invoices_branch_id_foreign` (`branch_id`),
  CONSTRAINT `invoices_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL,
  CONSTRAINT `invoices_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `invoices`
--

LOCK TABLES `invoices` WRITE;
/*!40000 ALTER TABLE `invoices` DISABLE KEYS */;
INSERT INTO `invoices` VALUES (1,'INV-2026-0901',2,1,'2026-09-01','2026-09-30','2026-10-01','2026-10-11',1440.00,0.00,0.00,0.00,1440.00,1440.00,0.00,'paid','subscription','Monthly milk delivery invoice for September','2026-10-06 11:46:27','2026-10-06 11:46:27');
/*!40000 ALTER TABLE `invoices` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'2014_10_12_000000_create_users_table',1),(2,'2014_10_12_100000_create_password_reset_tokens_table',1),(3,'2019_08_19_000000_create_failed_jobs_table',1),(4,'2019_12_14_000001_create_personal_access_tokens_table',1),(5,'2023_01_01_000001_create_branches_table',1),(6,'2023_01_01_000002_create_rate_charts_and_slabs_table',1),(7,'2023_01_01_000003_create_farmers_and_collections_table',1),(8,'2023_01_01_000004_create_customers_and_delivery_table',1),(9,'2023_01_01_000005_create_products_and_inventory_table',1),(10,'2023_01_01_000006_create_sales_and_billing_table',1),(11,'2023_01_01_000007_create_finance_support_system_table',1),(12,'2026_10_07_051004_create_customer_groups_table',2),(13,'2026_10_07_051006_create_customer_special_rates_table',2),(14,'2026_10_07_051008_create_customer_bottle_openings_table',2),(15,'2026_10_07_051154_create_staff_attendances_table',2),(16,'2026_10_07_051158_create_staff_advances_table',2),(17,'2026_10_07_051202_create_staff_salaries_table',2),(18,'2026_10_07_051211_create_website_banners_table',2),(19,'2026_10_07_051218_create_website_settings_table',2),(20,'2026_10_07_051222_create_milk_dispatches_table',2),(21,'2026_10_07_051224_create_product_bookings_table',2);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `milk_collections`
--

DROP TABLE IF EXISTS `milk_collections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `milk_collections` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `receipt_number` varchar(255) NOT NULL,
  `farmer_id` bigint(20) unsigned NOT NULL,
  `collection_center_id` bigint(20) unsigned DEFAULT NULL,
  `operator_id` bigint(20) unsigned DEFAULT NULL,
  `collection_date` date NOT NULL,
  `shift` varchar(255) NOT NULL,
  `milk_type` varchar(255) NOT NULL DEFAULT 'cow',
  `quantity_liters` decimal(8,2) NOT NULL,
  `fat` decimal(4,2) NOT NULL DEFAULT 4.00,
  `snf` decimal(4,2) NOT NULL DEFAULT 8.50,
  `clr` decimal(5,2) DEFAULT NULL,
  `calculated_rate` decimal(8,2) NOT NULL,
  `applied_rate` decimal(8,2) NOT NULL,
  `gross_amount` decimal(10,2) NOT NULL,
  `bonus` decimal(8,2) NOT NULL DEFAULT 0.00,
  `deduction` decimal(8,2) NOT NULL DEFAULT 0.00,
  `net_amount` decimal(10,2) NOT NULL,
  `payment_status` varchar(255) NOT NULL DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `milk_collections_receipt_number_unique` (`receipt_number`),
  KEY `milk_collections_farmer_id_foreign` (`farmer_id`),
  KEY `milk_collections_collection_center_id_foreign` (`collection_center_id`),
  CONSTRAINT `milk_collections_collection_center_id_foreign` FOREIGN KEY (`collection_center_id`) REFERENCES `collection_centers` (`id`) ON DELETE SET NULL,
  CONSTRAINT `milk_collections_farmer_id_foreign` FOREIGN KEY (`farmer_id`) REFERENCES `farmers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `milk_collections`
--

LOCK TABLES `milk_collections` WRITE;
/*!40000 ALTER TABLE `milk_collections` DISABLE KEYS */;
INSERT INTO `milk_collections` VALUES (1,'COL-1001',1,1,4,'2026-10-06','morning','cow',15.50,4.20,8.60,28.50,63.82,63.82,989.21,0.00,0.00,989.21,'pending','Good freshness, chilled immediately.','2026-10-06 11:46:26','2026-10-06 11:46:26'),(2,'COL-1002',2,1,4,'2026-10-06','morning','buffalo',22.00,6.80,9.20,30.00,90.36,90.36,1987.92,0.00,0.00,1987.92,'pending','Good freshness, chilled immediately.','2026-10-06 11:46:26','2026-10-06 11:46:26'),(3,'COL-1003',3,1,4,'2026-10-06','morning','cow',12.00,3.90,8.40,28.00,60.96,60.96,731.52,0.00,0.00,731.52,'pending','Good freshness, chilled immediately.','2026-10-06 11:46:26','2026-10-06 11:46:26'),(4,'COL-1004',4,1,4,'2026-10-06','morning','buffalo',28.50,7.10,9.40,31.00,93.42,93.42,2662.47,0.00,0.00,2662.47,'pending','Good freshness, chilled immediately.','2026-10-06 11:46:26','2026-10-06 11:46:26'),(5,'COL-1005',1,1,4,'2026-10-05','evening','cow',14.00,4.10,8.50,28.00,62.73,62.73,878.22,0.00,0.00,878.22,'pending','Good freshness, chilled immediately.','2026-10-06 11:46:26','2026-10-06 11:46:26'),(6,'COL-1006',2,1,4,'2026-10-05','evening','buffalo',20.00,6.60,9.10,29.50,88.47,88.47,1769.40,0.00,0.00,1769.40,'pending','Good freshness, chilled immediately.','2026-10-06 11:46:26','2026-10-06 11:46:26'),(7,'COL-1007',5,1,4,'2026-10-05','morning','mixed',18.00,4.50,8.70,29.00,66.27,66.27,1192.86,0.00,0.00,1192.86,'pending','Good freshness, chilled immediately.','2026-10-06 11:46:26','2026-10-06 11:46:26'),(8,'COL-1008',4,1,1,'2026-10-07','morning','buffalo',10.00,5.00,8.50,28.00,74.25,74.25,742.50,10.00,6.00,746.50,'pending','Remarks / Freshness Notes','2026-10-06 23:11:25','2026-10-06 23:11:25');
/*!40000 ALTER TABLE `milk_collections` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `milk_dispatches`
--

DROP TABLE IF EXISTS `milk_dispatches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `milk_dispatches` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `dispatch_number` varchar(255) NOT NULL,
  `dispatch_date` date NOT NULL,
  `shift` enum('morning','evening') NOT NULL DEFAULT 'morning',
  `route_id` bigint(20) unsigned DEFAULT NULL,
  `delivery_boy_id` bigint(20) unsigned DEFAULT NULL,
  `branch_id` bigint(20) unsigned DEFAULT NULL,
  `vehicle_number` varchar(255) DEFAULT NULL,
  `total_milk_quantity` decimal(10,2) NOT NULL,
  `fat` decimal(4,2) DEFAULT NULL,
  `snf` decimal(4,2) DEFAULT NULL,
  `temperature` decimal(4,1) DEFAULT NULL,
  `bottles_loaded` int(11) NOT NULL DEFAULT 0,
  `crates_loaded` int(11) NOT NULL DEFAULT 0,
  `dispatched_at` time DEFAULT NULL,
  `returned_at` time DEFAULT NULL,
  `status` enum('prepared','in_transit','delivered','returned') NOT NULL DEFAULT 'prepared',
  `notes` text DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `milk_dispatches_dispatch_number_unique` (`dispatch_number`),
  KEY `milk_dispatches_route_id_foreign` (`route_id`),
  KEY `milk_dispatches_delivery_boy_id_foreign` (`delivery_boy_id`),
  KEY `milk_dispatches_branch_id_foreign` (`branch_id`),
  KEY `milk_dispatches_created_by_foreign` (`created_by`),
  CONSTRAINT `milk_dispatches_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL,
  CONSTRAINT `milk_dispatches_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `milk_dispatches_delivery_boy_id_foreign` FOREIGN KEY (`delivery_boy_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `milk_dispatches_route_id_foreign` FOREIGN KEY (`route_id`) REFERENCES `delivery_routes` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `milk_dispatches`
--

LOCK TABLES `milk_dispatches` WRITE;
/*!40000 ALTER TABLE `milk_dispatches` DISABLE KEYS */;
/*!40000 ALTER TABLE `milk_dispatches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notifications` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `channel` varchar(255) NOT NULL DEFAULT 'web',
  `type` varchar(255) NOT NULL DEFAULT 'info',
  `recipient_phone` varchar(255) DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'sent',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
INSERT INTO `notifications` VALUES (1,8,'Milk Collection Receipt COL-1001','Morning shift milk recorded: 15.50 Ltr, FAT: 4.2%, SNF: 8.6%, Rate: ₹42.50, Amount: ₹658.75 credited to your ledger.','whatsapp','success','9826011111','sent','2026-10-06 11:46:27','2026-10-06 11:46:27'),(2,9,'Morning Milk Delivered','Your 2.0 Ltr Bottle Milk was delivered at 06:15 AM by Vikram Singh.','sms','info','9827011111','sent','2026-10-06 11:46:27','2026-10-06 11:46:27'),(3,NULL,'Milk Slip: COL-1008','Milk Recorded: 10 Ltr (buffalo), FAT: 5%, SNF: 8.5%, Rate: ₹74.25, Net: ₹746.5 credited to your ledger.','whatsapp','success','9826044444','sent','2026-10-06 23:11:25','2026-10-06 23:11:25');
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `payment_number` varchar(255) NOT NULL,
  `customer_id` bigint(20) unsigned NOT NULL,
  `invoice_id` bigint(20) unsigned DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_mode` varchar(255) NOT NULL DEFAULT 'cash',
  `transaction_reference` varchar(255) DEFAULT NULL,
  `payment_date` date NOT NULL,
  `collected_by` bigint(20) unsigned DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `payments_payment_number_unique` (`payment_number`),
  KEY `payments_customer_id_foreign` (`customer_id`),
  KEY `payments_invoice_id_foreign` (`invoice_id`),
  CONSTRAINT `payments_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `payments_invoice_id_foreign` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payments`
--

LOCK TABLES `payments` WRITE;
/*!40000 ALTER TABLE `payments` DISABLE KEYS */;
INSERT INTO `payments` VALUES (1,'PAY-2026-001',2,1,1440.00,'upi','UPI/291823749/GooglePay','2026-10-02',2,'Invoice full payment cleared','2026-10-06 11:46:27','2026-10-06 11:46:27');
/*!40000 ALTER TABLE `payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `personal_access_tokens`
--

DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personal_access_tokens`
--

LOCK TABLES `personal_access_tokens` WRITE;
/*!40000 ALTER TABLE `personal_access_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `personal_access_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pos_counters`
--

DROP TABLE IF EXISTS `pos_counters`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pos_counters` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `branch_id` bigint(20) unsigned DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `code` varchar(255) NOT NULL,
  `operator_id` bigint(20) unsigned DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pos_counters_code_unique` (`code`),
  KEY `pos_counters_branch_id_foreign` (`branch_id`),
  CONSTRAINT `pos_counters_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pos_counters`
--

LOCK TABLES `pos_counters` WRITE;
/*!40000 ALTER TABLE `pos_counters` DISABLE KEYS */;
INSERT INTO `pos_counters` VALUES (1,1,'Booth #1 Main Dairy Outlet','POS-001',7,'active','2026-10-06 11:46:26','2026-10-06 11:46:26');
/*!40000 ALTER TABLE `pos_counters` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pos_order_items`
--

DROP TABLE IF EXISTS `pos_order_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pos_order_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `pos_order_id` bigint(20) unsigned NOT NULL,
  `product_id` bigint(20) unsigned NOT NULL,
  `quantity` decimal(8,2) NOT NULL,
  `unit_price` decimal(8,2) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pos_order_items_pos_order_id_foreign` (`pos_order_id`),
  KEY `pos_order_items_product_id_foreign` (`product_id`),
  CONSTRAINT `pos_order_items_pos_order_id_foreign` FOREIGN KEY (`pos_order_id`) REFERENCES `pos_orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `pos_order_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pos_order_items`
--

LOCK TABLES `pos_order_items` WRITE;
/*!40000 ALTER TABLE `pos_order_items` DISABLE KEYS */;
INSERT INTO `pos_order_items` VALUES (1,1,1,2.00,300.00,600.00,'2026-10-06 11:46:27','2026-10-06 11:46:27');
/*!40000 ALTER TABLE `pos_order_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pos_orders`
--

DROP TABLE IF EXISTS `pos_orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pos_orders` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_number` varchar(255) NOT NULL,
  `branch_id` bigint(20) unsigned DEFAULT NULL,
  `counter_id` bigint(20) unsigned DEFAULT NULL,
  `customer_id` bigint(20) unsigned DEFAULT NULL,
  `customer_name` varchar(255) NOT NULL DEFAULT 'Walk-in Customer',
  `customer_phone` varchar(255) DEFAULT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `discount_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `tax_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `grand_total` decimal(10,2) NOT NULL,
  `payment_mode` varchar(255) NOT NULL DEFAULT 'cash',
  `payment_status` varchar(255) NOT NULL DEFAULT 'paid',
  `operator_id` bigint(20) unsigned DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pos_orders_order_number_unique` (`order_number`),
  KEY `pos_orders_branch_id_foreign` (`branch_id`),
  KEY `pos_orders_counter_id_foreign` (`counter_id`),
  KEY `pos_orders_customer_id_foreign` (`customer_id`),
  CONSTRAINT `pos_orders_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL,
  CONSTRAINT `pos_orders_counter_id_foreign` FOREIGN KEY (`counter_id`) REFERENCES `pos_counters` (`id`) ON DELETE SET NULL,
  CONSTRAINT `pos_orders_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pos_orders`
--

LOCK TABLES `pos_orders` WRITE;
/*!40000 ALTER TABLE `pos_orders` DISABLE KEYS */;
INSERT INTO `pos_orders` VALUES (1,'POS-202610-001',1,1,NULL,'Walk-in (Shri Verma)','9893012345',600.00,0.00,0.00,600.00,'upi','paid',7,'Paid via PhonePe QR','2026-10-06 11:46:27','2026-10-06 11:46:27');
/*!40000 ALTER TABLE `pos_orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_bookings`
--

DROP TABLE IF EXISTS `product_bookings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `product_bookings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `booking_number` varchar(255) NOT NULL,
  `customer_id` bigint(20) unsigned DEFAULT NULL,
  `customer_name` varchar(255) NOT NULL,
  `customer_phone` varchar(255) NOT NULL,
  `product_id` bigint(20) unsigned NOT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `advance_paid` decimal(10,2) NOT NULL DEFAULT 0.00,
  `booking_date` date NOT NULL,
  `delivery_date` date NOT NULL,
  `shift` enum('morning','evening','any') NOT NULL DEFAULT 'morning',
  `status` enum('confirmed','processing','delivered','cancelled') NOT NULL DEFAULT 'confirmed',
  `delivery_address` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `product_bookings_booking_number_unique` (`booking_number`),
  KEY `product_bookings_customer_id_foreign` (`customer_id`),
  KEY `product_bookings_product_id_foreign` (`product_id`),
  CONSTRAINT `product_bookings_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL,
  CONSTRAINT `product_bookings_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_bookings`
--

LOCK TABLES `product_bookings` WRITE;
/*!40000 ALTER TABLE `product_bookings` DISABLE KEYS */;
/*!40000 ALTER TABLE `product_bookings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `products` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `category_id` bigint(20) unsigned DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `code` varchar(255) NOT NULL,
  `product_type` varchar(255) NOT NULL DEFAULT 'milk',
  `unit` varchar(255) NOT NULL DEFAULT 'liter',
  `pack_size` varchar(255) DEFAULT NULL,
  `price` decimal(8,2) NOT NULL,
  `subscription_price` decimal(8,2) DEFAULT NULL,
  `cost_price` decimal(8,2) NOT NULL DEFAULT 0.00,
  `tax_percent` decimal(5,2) NOT NULL DEFAULT 0.00,
  `current_stock` decimal(10,2) NOT NULL DEFAULT 0.00,
  `min_stock_alert` decimal(10,2) NOT NULL DEFAULT 5.00,
  `in_stock` tinyint(1) NOT NULL DEFAULT 1,
  `image` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `products_code_unique` (`code`),
  KEY `products_category_id_foreign` (`category_id`),
  CONSTRAINT `products_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products`
--

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` VALUES (1,1,'पनीर','PRD-001','paneer','kg','1 kg',300.00,290.00,230.00,0.00,8.00,5.00,1,NULL,'Fresh malai paneer made from pure whole milk','active','2026-10-06 11:46:26','2026-10-06 11:46:26',NULL),(2,1,'घी','PRD-002','ghee','kg','1 kg',680.00,660.00,520.00,0.00,40.00,10.00,1,NULL,'11/10/25 se 580 par kg special batch pure bilona ghee','active','2026-10-06 11:46:26','2026-10-06 23:01:29',NULL),(3,2,'बर्फी मावा','PRD-003','custom','kg','1 kg',285.00,275.00,210.00,0.00,0.00,5.00,0,NULL,'शुद्ध मावा / Fresh sweet khoya mawa for traditional sweets','active','2026-10-06 11:46:26','2026-10-06 11:46:26',NULL),(4,1,'सादा दूध','PRD-004','milk','liter','1 Ltr',50.00,48.00,40.00,0.00,45.00,20.00,1,NULL,'ताज़ा गाय का सादा दूध / Pure farm cow milk','active','2026-10-06 11:46:27','2026-10-06 11:46:27',NULL),(5,1,'गोल्ड दूध','PRD-005','milk','liter','1 Ltr',60.00,58.00,48.00,0.00,0.00,20.00,0,NULL,'फुल क्रीम भैंस का दूध / Rich creamy buffalo gold milk','active','2026-10-06 11:46:27','2026-10-06 11:46:27',NULL),(6,3,'khalli','PRD-006','feed','piece','50 kg Bag',2500.00,2450.00,2100.00,0.00,12.00,4.00,1,NULL,'प्रीमियम बिनौला / सरसों खल्ली बोरी (Cattle cake feed 50kg)','active','2026-10-06 11:46:27','2026-10-06 11:46:27',NULL),(7,1,'Bottle milk 1 ltr','PRD-007','milk','bottle','1 Ltr Glass Bottle',60.00,58.00,45.00,0.00,18.00,10.00,1,NULL,'Pasteurized glass bottle packaged farm fresh milk','active','2026-10-06 11:46:27','2026-10-06 11:46:27',NULL),(8,1,'मक्खन (Butter)','PRD-008','butter','kg','500g',420.00,400.00,330.00,0.00,0.00,5.00,0,NULL,'Farm-churned fresh white butter (Makhan)','active','2026-10-06 11:46:27','2026-10-06 11:46:27',NULL);
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rate_chart_slabs`
--

DROP TABLE IF EXISTS `rate_chart_slabs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rate_chart_slabs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `rate_chart_id` bigint(20) unsigned NOT NULL,
  `fat_from` decimal(4,2) NOT NULL,
  `fat_to` decimal(4,2) NOT NULL,
  `snf_from` decimal(4,2) NOT NULL,
  `snf_to` decimal(4,2) NOT NULL,
  `rate` decimal(8,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `rate_chart_slabs_rate_chart_id_foreign` (`rate_chart_id`),
  CONSTRAINT `rate_chart_slabs_rate_chart_id_foreign` FOREIGN KEY (`rate_chart_id`) REFERENCES `rate_charts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rate_chart_slabs`
--

LOCK TABLES `rate_chart_slabs` WRITE;
/*!40000 ALTER TABLE `rate_chart_slabs` DISABLE KEYS */;
INSERT INTO `rate_chart_slabs` VALUES (1,1,3.50,4.00,8.30,8.70,42.50,'2026-10-06 11:46:26','2026-10-06 11:46:26');
/*!40000 ALTER TABLE `rate_chart_slabs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rate_charts`
--

DROP TABLE IF EXISTS `rate_charts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rate_charts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `milk_type` varchar(255) NOT NULL DEFAULT 'cow',
  `calculation_type` varchar(255) NOT NULL DEFAULT 'fat_snf_formula',
  `base_rate` decimal(8,2) NOT NULL DEFAULT 35.00,
  `min_fat` decimal(4,2) NOT NULL DEFAULT 3.00,
  `max_fat` decimal(4,2) NOT NULL DEFAULT 10.00,
  `min_snf` decimal(4,2) NOT NULL DEFAULT 8.00,
  `max_snf` decimal(4,2) NOT NULL DEFAULT 10.00,
  `fat_factor` decimal(6,2) NOT NULL DEFAULT 6.50,
  `snf_factor` decimal(6,2) NOT NULL DEFAULT 4.20,
  `effective_date` date DEFAULT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT 1,
  `status` varchar(255) NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rate_charts`
--

LOCK TABLES `rate_charts` WRITE;
/*!40000 ALTER TABLE `rate_charts` DISABLE KEYS */;
INSERT INTO `rate_charts` VALUES (1,'Standard Cow Milk Formula Chart (TS)','cow','fat_snf_formula',38.00,3.20,5.50,8.20,9.50,6.80,4.10,'2026-07-06',1,'active','2026-10-06 11:46:26','2026-10-06 11:46:26'),(2,'Standard Buffalo Milk Chart','buffalo','fat_snf_formula',55.00,6.00,10.00,8.80,10.00,7.20,4.50,'2026-07-06',1,'active','2026-10-06 11:46:26','2026-10-06 11:46:26');
/*!40000 ALTER TABLE `rate_charts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `staff_advances`
--

DROP TABLE IF EXISTS `staff_advances`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `staff_advances` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `advance_date` date NOT NULL,
  `payment_mode` varchar(255) NOT NULL DEFAULT 'cash',
  `status` enum('pending','approved','deducted','rejected') NOT NULL DEFAULT 'approved',
  `reason` text DEFAULT NULL,
  `approved_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `staff_advances_user_id_foreign` (`user_id`),
  KEY `staff_advances_approved_by_foreign` (`approved_by`),
  CONSTRAINT `staff_advances_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `staff_advances_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `staff_advances`
--

LOCK TABLES `staff_advances` WRITE;
/*!40000 ALTER TABLE `staff_advances` DISABLE KEYS */;
/*!40000 ALTER TABLE `staff_advances` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `staff_attendances`
--

DROP TABLE IF EXISTS `staff_attendances`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `staff_attendances` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `date` date NOT NULL,
  `status` enum('present','absent','half_day','leave') NOT NULL DEFAULT 'present',
  `check_in` time DEFAULT NULL,
  `check_out` time DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `staff_attendances_user_id_date_unique` (`user_id`,`date`),
  CONSTRAINT `staff_attendances_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `staff_attendances`
--

LOCK TABLES `staff_attendances` WRITE;
/*!40000 ALTER TABLE `staff_attendances` DISABLE KEYS */;
/*!40000 ALTER TABLE `staff_attendances` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `staff_salaries`
--

DROP TABLE IF EXISTS `staff_salaries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `staff_salaries` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `salary_month` varchar(7) NOT NULL,
  `base_salary` decimal(10,2) NOT NULL,
  `total_days` int(11) NOT NULL DEFAULT 30,
  `present_days` int(11) NOT NULL DEFAULT 30,
  `allowances` decimal(10,2) NOT NULL DEFAULT 0.00,
  `advance_deduction` decimal(10,2) NOT NULL DEFAULT 0.00,
  `other_deductions` decimal(10,2) NOT NULL DEFAULT 0.00,
  `net_payable` decimal(10,2) NOT NULL,
  `payment_status` varchar(255) NOT NULL DEFAULT 'paid',
  `disbursed_at` date DEFAULT NULL,
  `payment_mode` varchar(255) NOT NULL DEFAULT 'bank_transfer',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `staff_salaries_user_id_salary_month_unique` (`user_id`,`salary_month`),
  CONSTRAINT `staff_salaries_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `staff_salaries`
--

LOCK TABLES `staff_salaries` WRITE;
/*!40000 ALTER TABLE `staff_salaries` DISABLE KEYS */;
/*!40000 ALTER TABLE `staff_salaries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stock_transfers`
--

DROP TABLE IF EXISTS `stock_transfers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stock_transfers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `transfer_number` varchar(255) NOT NULL,
  `from_branch_id` bigint(20) unsigned NOT NULL,
  `to_branch_id` bigint(20) unsigned NOT NULL,
  `product_id` bigint(20) unsigned NOT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'completed',
  `dispatched_by` bigint(20) unsigned DEFAULT NULL,
  `received_by` bigint(20) unsigned DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `stock_transfers_transfer_number_unique` (`transfer_number`),
  KEY `stock_transfers_from_branch_id_foreign` (`from_branch_id`),
  KEY `stock_transfers_to_branch_id_foreign` (`to_branch_id`),
  KEY `stock_transfers_product_id_foreign` (`product_id`),
  CONSTRAINT `stock_transfers_from_branch_id_foreign` FOREIGN KEY (`from_branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  CONSTRAINT `stock_transfers_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `stock_transfers_to_branch_id_foreign` FOREIGN KEY (`to_branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stock_transfers`
--

LOCK TABLES `stock_transfers` WRITE;
/*!40000 ALTER TABLE `stock_transfers` DISABLE KEYS */;
/*!40000 ALTER TABLE `stock_transfers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `subscriptions`
--

DROP TABLE IF EXISTS `subscriptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `subscriptions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `subscription_code` varchar(255) NOT NULL,
  `customer_id` bigint(20) unsigned NOT NULL,
  `product_id` bigint(20) unsigned NOT NULL,
  `route_id` bigint(20) unsigned DEFAULT NULL,
  `quantity` decimal(6,2) NOT NULL DEFAULT 1.00,
  `frequency` varchar(255) NOT NULL DEFAULT 'daily',
  `custom_days` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`custom_days`)),
  `shift` varchar(255) NOT NULL DEFAULT 'morning',
  `unit_price` decimal(8,2) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'active',
  `pause_from` date DEFAULT NULL,
  `pause_until` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `subscriptions_subscription_code_unique` (`subscription_code`),
  KEY `subscriptions_customer_id_foreign` (`customer_id`),
  KEY `subscriptions_route_id_foreign` (`route_id`),
  CONSTRAINT `subscriptions_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `subscriptions_route_id_foreign` FOREIGN KEY (`route_id`) REFERENCES `delivery_routes` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `subscriptions`
--

LOCK TABLES `subscriptions` WRITE;
/*!40000 ALTER TABLE `subscriptions` DISABLE KEYS */;
INSERT INTO `subscriptions` VALUES (1,'SUB-101',1,7,1,2.00,'daily',NULL,'morning',58.00,'2026-08-06',NULL,'active',NULL,NULL,'2026-10-06 11:46:27','2026-10-06 11:46:27'),(2,'SUB-102',2,4,1,1.50,'daily',NULL,'morning',48.00,'2026-09-06',NULL,'active',NULL,NULL,'2026-10-06 11:46:27','2026-10-06 11:46:27'),(3,'SUB-103',3,4,1,10.00,'daily',NULL,'morning',46.00,'2026-07-06',NULL,'active',NULL,NULL,'2026-10-06 11:46:27','2026-10-06 11:46:27'),(4,'SUB-104',4,7,2,1.00,'daily',NULL,'morning',58.00,'2026-09-06',NULL,'paused','2026-10-06','2026-10-10','2026-10-06 11:46:27','2026-10-06 11:46:27');
/*!40000 ALTER TABLE `subscriptions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `support_tickets`
--

DROP TABLE IF EXISTS `support_tickets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `support_tickets` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `ticket_number` varchar(255) NOT NULL,
  `customer_id` bigint(20) unsigned DEFAULT NULL,
  `farmer_id` bigint(20) unsigned DEFAULT NULL,
  `category` varchar(255) NOT NULL DEFAULT 'delivery',
  `subject` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `priority` varchar(255) NOT NULL DEFAULT 'medium',
  `status` varchar(255) NOT NULL DEFAULT 'open',
  `assigned_to` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `support_tickets_ticket_number_unique` (`ticket_number`),
  KEY `support_tickets_customer_id_foreign` (`customer_id`),
  KEY `support_tickets_farmer_id_foreign` (`farmer_id`),
  CONSTRAINT `support_tickets_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL,
  CONSTRAINT `support_tickets_farmer_id_foreign` FOREIGN KEY (`farmer_id`) REFERENCES `farmers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `support_tickets`
--

LOCK TABLES `support_tickets` WRITE;
/*!40000 ALTER TABLE `support_tickets` DISABLE KEYS */;
INSERT INTO `support_tickets` VALUES (1,'TCK-2026-001',1,NULL,'delivery','Please deliver earlier before 6:30 AM','Need early delivery for school preparation.','medium','in_progress',6,'2026-10-06 11:46:27','2026-10-06 11:46:27');
/*!40000 ALTER TABLE `support_tickets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `system_settings`
--

DROP TABLE IF EXISTS `system_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `system_settings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(255) NOT NULL,
  `value` text DEFAULT NULL,
  `group` varchar(255) NOT NULL DEFAULT 'general',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `system_settings_key_unique` (`key`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `system_settings`
--

LOCK TABLES `system_settings` WRITE;
/*!40000 ALTER TABLE `system_settings` DISABLE KEYS */;
INSERT INTO `system_settings` VALUES (1,'dairy_name','Simple Dairy','dairy','2026-10-06 11:46:24','2026-10-06 11:46:24'),(2,'tagline','Fresh, Pure & Natural Dairy Products','dairy','2026-10-06 11:46:24','2026-10-06 11:46:24'),(3,'owner_name','Narendra Malviya','dairy','2026-10-06 11:46:24','2026-10-06 11:46:24'),(4,'phone','+91 98765 43210','dairy','2026-10-06 11:46:24','2026-10-06 11:46:24'),(5,'email','contact@simpledairy.com','dairy','2026-10-06 11:46:24','2026-10-06 11:46:24'),(6,'address','Plot 42, Dairy Processing Zone, Industrial Area','dairy','2026-10-06 11:46:24','2026-10-06 11:46:24'),(7,'currency','₹','general','2026-10-06 11:46:24','2026-10-06 11:46:24'),(8,'morning_shift','06:00 - 09:30','dairy','2026-10-06 11:46:24','2026-10-06 11:46:24'),(9,'evening_shift','17:00 - 20:30','dairy','2026-10-06 11:46:24','2026-10-06 11:46:24'),(10,'whatsapp_enabled','1','notification','2026-10-06 11:46:24','2026-10-06 11:46:24'),(11,'sms_enabled','1','notification','2026-10-06 11:46:24','2026-10-06 11:46:24');
/*!40000 ALTER TABLE `system_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ticket_replies`
--

DROP TABLE IF EXISTS `ticket_replies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ticket_replies` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `support_ticket_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `message` text NOT NULL,
  `is_internal_note` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ticket_replies_support_ticket_id_foreign` (`support_ticket_id`),
  CONSTRAINT `ticket_replies_support_ticket_id_foreign` FOREIGN KEY (`support_ticket_id`) REFERENCES `support_tickets` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ticket_replies`
--

LOCK TABLES `ticket_replies` WRITE;
/*!40000 ALTER TABLE `ticket_replies` DISABLE KEYS */;
/*!40000 ALTER TABLE `ticket_replies` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `role` varchar(255) NOT NULL DEFAULT 'customer',
  `branch_id` bigint(20) unsigned DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'active',
  `avatar` varchar(255) DEFAULT NULL,
  `permissions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`permissions`)),
  `two_factor_otp` varchar(255) DEFAULT NULL,
  `otp_expires_at` timestamp NULL DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  UNIQUE KEY `users_phone_unique` (`phone`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'System SuperAdmin','superadmin@simpledairy.com','9000000001','super_admin',NULL,'active',NULL,NULL,NULL,NULL,NULL,'$2y$10$muU4HskBZjkxlTUavDCb4.aOJtnqMK8clU3sUGgoWGKNI4yy1LuYK',NULL,'2026-10-06 11:46:25','2026-10-06 11:46:25',NULL),(2,'Narendra Malviya','admin@simpledairy.com','9000000002','dairy_admin',NULL,'active',NULL,NULL,NULL,NULL,NULL,'$2y$10$8SAKxSvamiR1asGb.sQKs.G6CFoIuwbB7BoJo.M8tj1CSedefmume',NULL,'2026-10-06 11:46:25','2026-10-06 11:46:25',NULL),(3,'Rajesh Sharma','branch@simpledairy.com','9000000003','branch_manager',NULL,'active',NULL,NULL,NULL,NULL,NULL,'$2y$10$qlQOTOLkbRsYKtr4RCxFju8ERqwxnFwEymXI5yvP4EnfyNr54eZ9e',NULL,'2026-10-06 11:46:25','2026-10-06 11:46:25',NULL),(4,'Dinesh Verma','operator@simpledairy.com','9000000004','collection_operator',NULL,'active',NULL,NULL,NULL,NULL,NULL,'$2y$10$u93OUurFSY6RYflBDnxMz.VybXT2mvVxP5E7FlP28WX3iIJAP5SrG',NULL,'2026-10-06 11:46:25','2026-10-06 11:46:25',NULL),(5,'Amit Joshi','accountant@simpledairy.com','9000000005','accountant',NULL,'active',NULL,NULL,NULL,NULL,NULL,'$2y$10$MfcxjtMkO0S9Aj4Xlk7uGOe3kBNuV0DG.XjzfJ0ZcpPwfETTNrHVu',NULL,'2026-10-06 11:46:25','2026-10-06 11:46:25',NULL),(6,'Vikram Singh','delivery@simpledairy.com','9000000006','delivery_boy',NULL,'active',NULL,NULL,NULL,NULL,NULL,'$2y$10$dHvRIK9O9k.vWPv47T65Wus6jZsROnBZKLE/jKl3ieLsgZObGF55m',NULL,'2026-10-06 11:46:26','2026-10-06 11:46:26',NULL),(7,'Pooja Tiwari','pos@simpledairy.com','9000000007','sales_operator',NULL,'active',NULL,NULL,NULL,NULL,NULL,'$2y$10$CpbrXaC/mLK4tfOIEd.VP.fhzGFOjUjF73U6HhY72gPwyFJ6f2aOC',NULL,'2026-10-06 11:46:26','2026-10-06 11:46:26',NULL),(8,'Ramesh Patel','farmer@simpledairy.com','9000000008','farmer',NULL,'active',NULL,NULL,NULL,NULL,NULL,'$2y$10$H8bNveGP5Mb79xq8z9EVyO3QW7U9MhLvbRwetuBh0msTTjobw7hx.',NULL,'2026-10-06 11:46:26','2026-10-06 11:46:26',NULL),(9,'Sunil Mehta','customer@simpledairy.com','9000000009','customer',NULL,'active',NULL,NULL,NULL,NULL,NULL,'$2y$10$OsIjmonewvcRuF/l7DJ/3eeDjOkQDlIWSiGoVJdjJGW36NlGDmAFy',NULL,'2026-10-06 11:46:26','2026-10-06 11:46:26',NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `website_banners`
--

DROP TABLE IF EXISTS `website_banners`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `website_banners` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `subtitle` varchar(255) DEFAULT NULL,
  `badge_text` varchar(255) DEFAULT NULL,
  `cta_text` varchar(255) NOT NULL DEFAULT 'Order Pure Milk',
  `cta_url` varchar(255) NOT NULL DEFAULT '/#products',
  `image_url` varchar(255) DEFAULT NULL,
  `bg_gradient` varchar(255) NOT NULL DEFAULT 'from-emerald-900 to-slate-900',
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `website_banners`
--

LOCK TABLES `website_banners` WRITE;
/*!40000 ALTER TABLE `website_banners` DISABLE KEYS */;
/*!40000 ALTER TABLE `website_banners` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `website_settings`
--

DROP TABLE IF EXISTS `website_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `website_settings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(255) NOT NULL,
  `value` text DEFAULT NULL,
  `type` varchar(255) NOT NULL DEFAULT 'string',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `website_settings_key_unique` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `website_settings`
--

LOCK TABLES `website_settings` WRITE;
/*!40000 ALTER TABLE `website_settings` DISABLE KEYS */;
/*!40000 ALTER TABLE `website_settings` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-10-07 12:02:48

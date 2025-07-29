/*
 Navicat Premium Data Transfer

 Source Server         : localhost
 Source Server Type    : MySQL
 Source Server Version : 110404 (11.4.4-MariaDB)
 Source Host           : localhost:3306
 Source Schema         : admisi

 Target Server Type    : MySQL
 Target Server Version : 110404 (11.4.4-MariaDB)
 File Encoding         : 65001

 Date: 29/07/2025 09:35:17
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for menu_items
-- ----------------------------
DROP TABLE IF EXISTS `menu_items`;
CREATE TABLE `menu_items`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `menu_location_id` int NOT NULL,
  `title` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `type` enum('page','post','external') CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `link_id` int NULL DEFAULT NULL,
  `external_url` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `parent_id` int NOT NULL DEFAULT 0,
  `menu_order` int NOT NULL DEFAULT 0,
  `css_classes` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `target` varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `visibility` enum('all','logged_in','logged_out') CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT 'all',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `menu_location_id`(`menu_location_id` ASC) USING BTREE,
  CONSTRAINT `menu_items_ibfk_1` FOREIGN KEY (`menu_location_id`) REFERENCES `menu_locations` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 15 CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of menu_items
-- ----------------------------
INSERT INTO `menu_items` VALUES (1, 1, 'Beranda', 'page', 1, NULL, 0, 2, NULL, NULL, 'all');
INSERT INTO `menu_items` VALUES (3, 1, 'Layanan', 'page', 3, NULL, 0, 3, NULL, NULL, 'all');
INSERT INTO `menu_items` VALUES (4, 1, 'Blog', 'post', 101, NULL, 6, 6, NULL, NULL, 'all');
INSERT INTO `menu_items` VALUES (5, 1, 'Desain Web', 'page', 4, NULL, 3, 4, NULL, NULL, 'all');
INSERT INTO `menu_items` VALUES (6, 1, 'Pengembangan Aplikasi', 'page', 5, NULL, 0, 5, NULL, NULL, 'all');
INSERT INTO `menu_items` VALUES (11, 1, 'Putrasoft', 'external', NULL, 'https://putrasoft.com', 6, 7, NULL, NULL, 'all');
INSERT INTO `menu_items` VALUES (12, 1, 'Tes Menu', 'post', 102, NULL, 0, 1, NULL, NULL, 'all');
INSERT INTO `menu_items` VALUES (13, 2, 'STMIK IM', 'external', NULL, 'https://stmik-im.ac.id', 14, 2, NULL, NULL, 'all');
INSERT INTO `menu_items` VALUES (14, 2, 'Home', 'page', 1, NULL, 0, 1, NULL, NULL, 'all');

-- ----------------------------
-- Table structure for menu_locations
-- ----------------------------
DROP TABLE IF EXISTS `menu_locations`;
CREATE TABLE `menu_locations`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `slug` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `slug`(`slug` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 4 CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of menu_locations
-- ----------------------------
INSERT INTO `menu_locations` VALUES (1, 'Menu Utama', 'main-menu');
INSERT INTO `menu_locations` VALUES (2, 'Atas', 'atas');

SET FOREIGN_KEY_CHECKS = 1;

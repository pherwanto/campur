/*
 Navicat Premium Data Transfer

 Source Server         : localhost
 Source Server Type    : MySQL
 Source Server Version : 110404 (11.4.4-MariaDB)
 Source Host           : localhost:3306
 Source Schema         : menu

 Target Server Type    : MySQL
 Target Server Version : 110404 (11.4.4-MariaDB)
 File Encoding         : 65001

 Date: 29/07/2025 09:33:40
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for menus
-- ----------------------------
DROP TABLE IF EXISTS `menus`;
CREATE TABLE `menus`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `parent_id` int NULL DEFAULT 0,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `type` enum('internal','external') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'internal',
  `page_id` int NULL DEFAULT NULL,
  `url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `position` enum('main','footer') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `item_order` int NULL DEFAULT 0,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 11 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of menus
-- ----------------------------
INSERT INTO `menus` VALUES (1, 2, 'Beranda', 'internal', 1, NULL, 'main', 1);
INSERT INTO `menus` VALUES (2, 0, 'Tentang Kami', 'internal', 2, NULL, 'main', 0);
INSERT INTO `menus` VALUES (3, 5, 'Visi & Misi', 'internal', 3, NULL, 'main', 0);
INSERT INTO `menus` VALUES (5, 0, 'Kontak', 'internal', 5, NULL, 'main', 2);
INSERT INTO `menus` VALUES (6, 2, 'Google', 'external', NULL, 'https://google.com', 'main', 0);
INSERT INTO `menus` VALUES (7, 0, 'Bantuan', 'internal', 6, NULL, 'main', 1);
INSERT INTO `menus` VALUES (8, 0, 'Kebijakan Privasi', 'internal', 7, NULL, 'footer', 0);
INSERT INTO `menus` VALUES (10, 2, 'Media Sosial', 'external', NULL, 'https://medsos.com', 'main', 2);

SET FOREIGN_KEY_CHECKS = 1;

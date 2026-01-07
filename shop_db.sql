-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 07, 2026 at 03:27 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `shop_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `img` text DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `img`, `sort_order`) VALUES
(1, 'สินค้าตัวอย่าง', '', 0);

-- --------------------------------------------------------

--
-- Table structure for table `config`
--

CREATE TABLE `config` (
  `id` int(11) NOT NULL,
  `site_name` varchar(255) NOT NULL DEFAULT 'My Shop',
  `truewallet_phone` varchar(20) DEFAULT NULL,
  `marquee_text` text DEFAULT NULL,
  `contact_link` varchar(255) DEFAULT NULL,
  `enable_angpao` tinyint(1) DEFAULT 1,
  `angpao_api_url` varchar(255) DEFAULT '',
  `enable_promptpay` tinyint(1) DEFAULT 1,
  `promptpay_no` varchar(20) DEFAULT '',
  `banner_img` varchar(255) DEFAULT '',
  `facebook_url` varchar(255) DEFAULT '',
  `line_url` varchar(255) DEFAULT '',
  `discord_url` varchar(255) DEFAULT '',
  `site_logo` varchar(255) DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `coupons`
--

CREATE TABLE `coupons` (
  `id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `reward_amount` decimal(10,2) NOT NULL,
  `is_used` tinyint(1) DEFAULT 0,
  `user_used` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `gacha_items`
--

CREATE TABLE `gacha_items` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `chance` int(11) NOT NULL DEFAULT 0,
  `result_text` text DEFAULT NULL,
  `img` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `gacha_rewards`
--

CREATE TABLE `gacha_rewards` (
  `id` int(11) NOT NULL,
  `parent_product_id` int(11) NOT NULL COMMENT 'ID ของสินค้าที่เป็นกล่องสุ่ม',
  `reward_product_id` int(11) NOT NULL COMMENT 'ID ของสินค้าที่จะได้รับ',
  `chance` int(11) NOT NULL DEFAULT 1 COMMENT 'โอกาสออก (Weight)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `product_name` varchar(255) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `data_received` text DEFAULT NULL,
  `purchased_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` varchar(50) DEFAULT 'pending',
  `note` text DEFAULT NULL,
  `status_text` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `img` text DEFAULT NULL,
  `is_gacha` tinyint(1) DEFAULT 0,
  `gacha_chance` int(11) DEFAULT 100,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `category_id`, `name`, `description`, `price`, `img`, `is_gacha`, `gacha_chance`, `created_at`) VALUES
(2, 1, 'สินค้าตัวอย่าง', '', 99.00, 'https://media.giphy.com/media/v1.Y2lkPTc5MGI3NjExMWJ6bnp2OGliOXlyazVlOHExcHFxMzZsNXY0aGloNWtjbnFoM2NzNSZlcD12MV9naWZzX3RyZW5kaW5nJmN0PWc/FY8c5SKwiNf1EtZKGs/giphy.gif', 0, 100, '2026-01-07 13:51:54');

-- --------------------------------------------------------

--
-- Table structure for table `product_images`
--

CREATE TABLE `product_images` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `img_path` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `redeem_codes`
--

CREATE TABLE `redeem_codes` (
  `id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `reward` decimal(10,2) NOT NULL,
  `max_uses` int(11) NOT NULL DEFAULT 1,
  `used_count` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `redeem_history`
--

CREATE TABLE `redeem_history` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `code_id` int(11) NOT NULL,
  `redeemed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `image` text NOT NULL,
  `input_type` enum('uid','id_pass','uid_server') DEFAULT 'uid',
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `service_packages`
--

CREATE TABLE `service_packages` (
  `id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `site_name` varchar(255) DEFAULT 'MY SHOP',
  `site_logo` text DEFAULT NULL,
  `site_color` varchar(50) DEFAULT 'blue',
  `marquee_text` text DEFAULT NULL,
  `background_img` text DEFAULT NULL,
  `banner_img` longtext DEFAULT NULL,
  `truewallet_phone` varchar(20) DEFAULT NULL,
  `line_url` text DEFAULT NULL,
  `facebook_url` text DEFAULT NULL,
  `discord_url` text DEFAULT NULL,
  `youtube_url` varchar(255) DEFAULT NULL,
  `tiktok_url` varchar(255) DEFAULT NULL,
  `instagram_url` varchar(255) DEFAULT NULL,
  `angpao_api_key` varchar(255) DEFAULT NULL COMMENT 'Key ของเว็บรับซอง',
  `angpao_api_url` varchar(255) DEFAULT NULL COMMENT 'URL ของเว็บรับซอง',
  `promptpay_number` varchar(20) DEFAULT NULL COMMENT 'เบอร์หรือเลข ปชช รับเงิน',
  `slip_api_key` varchar(255) DEFAULT NULL COMMENT 'Key สำหรับเช็คสลิป (ถ้ามี)',
  `angpao_status` tinyint(1) DEFAULT 1 COMMENT '1=เปิด, 0=ปิด',
  `promptpay_status` tinyint(1) DEFAULT 1 COMMENT '1=เปิด, 0=ปิด',
  `slip_check_api_key` varchar(255) DEFAULT NULL COMMENT 'Key สำหรับ API เช็คสลิป (เช่น SlipOK)',
  `background_type` enum('image','video') DEFAULT 'image',
  `background_list` text DEFAULT NULL,
  `floating_emojis` text DEFAULT NULL,
  `payment_tm_phone` varchar(20) DEFAULT NULL COMMENT 'เบอร์วอลเล็ทรับเงิน',
  `payment_tm_api_url` varchar(255) DEFAULT NULL COMMENT 'URL ของ API เช็คซอง',
  `payment_bank_acc` varchar(20) DEFAULT NULL COMMENT 'เลขบัญชีธนาคาร/พร้อมเพย์',
  `payment_bank_name` varchar(50) DEFAULT NULL COMMENT 'ชื่อธนาคาร/ชื่อบัญชี',
  `slip_api_token` varchar(255) DEFAULT NULL,
  `discord_widget_id` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `site_name`, `site_logo`, `site_color`, `marquee_text`, `background_img`, `banner_img`, `truewallet_phone`, `line_url`, `facebook_url`, `discord_url`, `youtube_url`, `tiktok_url`, `instagram_url`, `angpao_api_key`, `angpao_api_url`, `promptpay_number`, `slip_api_key`, `angpao_status`, `promptpay_status`, `slip_check_api_key`, `background_type`, `background_list`, `floating_emojis`, `payment_tm_phone`, `payment_tm_api_url`, `payment_bank_acc`, `payment_bank_name`, `slip_api_token`, `discord_widget_id`) VALUES
(1, 'My Shop', '', 'purple', 'Welcome to My Shop', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, 'image', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `stocks`
--

CREATE TABLE `stocks` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `contents` text NOT NULL,
  `is_sold` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `stocks`
--

INSERT INTO `stocks` (`id`, `product_id`, `contents`, `is_sold`) VALUES
(1, 2, 'test', 0),
(2, 2, 'test', 0),
(3, 2, 'test', 0),
(4, 2, 'test', 0),
(5, 2, 'test', 0),
(6, 2, 'test', 0),
(7, 2, 'test', 0),
(8, 2, 'test', 0),
(9, 2, 'test', 0);

-- --------------------------------------------------------

--
-- Table structure for table `topups`
--

CREATE TABLE `topups` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `method` varchar(50) DEFAULT NULL,
  `reference_code` varchar(100) DEFAULT NULL,
  `status` enum('pending','success','failed') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `point` decimal(10,2) DEFAULT 0.00,
  `role` enum('member','admin') DEFAULT 'member',
  `profile_img` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `point`, `role`, `profile_img`, `created_at`) VALUES
(1, 'admin', '$2y$10$2Db3sJYp/qHnLp92GjEAPeK2ycGmFTGQmaOx5Rm1L2Ng1sv98VLgm', 0.00, 'admin', NULL, '2026-01-07 13:38:21');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `config`
--
ALTER TABLE `config`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `coupons`
--
ALTER TABLE `coupons`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `gacha_items`
--
ALTER TABLE `gacha_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_gacha_product` (`product_id`);

--
-- Indexes for table `gacha_rewards`
--
ALTER TABLE `gacha_rewards`
  ADD PRIMARY KEY (`id`),
  ADD KEY `parent_product_id` (`parent_product_id`),
  ADD KEY `reward_product_id` (`reward_product_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `redeem_codes`
--
ALTER TABLE `redeem_codes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `redeem_history`
--
ALTER TABLE `redeem_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `code_id` (`code_id`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `service_packages`
--
ALTER TABLE `service_packages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `service_id` (`service_id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `stocks`
--
ALTER TABLE `stocks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_stocks_product` (`product_id`);

--
-- Indexes for table `topups`
--
ALTER TABLE `topups`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_ref_code` (`reference_code`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `config`
--
ALTER TABLE `config`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `coupons`
--
ALTER TABLE `coupons`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `gacha_items`
--
ALTER TABLE `gacha_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `gacha_rewards`
--
ALTER TABLE `gacha_rewards`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `product_images`
--
ALTER TABLE `product_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `redeem_codes`
--
ALTER TABLE `redeem_codes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `redeem_history`
--
ALTER TABLE `redeem_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `service_packages`
--
ALTER TABLE `service_packages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `stocks`
--
ALTER TABLE `stocks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `topups`
--
ALTER TABLE `topups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `gacha_items`
--
ALTER TABLE `gacha_items`
  ADD CONSTRAINT `fk_gacha_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `gacha_rewards`
--
ALTER TABLE `gacha_rewards`
  ADD CONSTRAINT `gacha_rewards_ibfk_1` FOREIGN KEY (`parent_product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `gacha_rewards_ibfk_2` FOREIGN KEY (`reward_product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `product_images`
--
ALTER TABLE `product_images`
  ADD CONSTRAINT `product_images_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `redeem_history`
--
ALTER TABLE `redeem_history`
  ADD CONSTRAINT `redeem_history_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `redeem_history_ibfk_2` FOREIGN KEY (`code_id`) REFERENCES `redeem_codes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `service_packages`
--
ALTER TABLE `service_packages`
  ADD CONSTRAINT `service_packages_ibfk_1` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `stocks`
--
ALTER TABLE `stocks`
  ADD CONSTRAINT `fk_stocks_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `topups`
--
ALTER TABLE `topups`
  ADD CONSTRAINT `topups_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

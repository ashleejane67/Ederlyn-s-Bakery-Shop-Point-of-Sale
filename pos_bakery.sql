-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 08, 2025 at 11:45 AM
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
-- Database: `pos_bakery`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `admin_id` int(11) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`admin_id`, `username`, `password`) VALUES
(1, 'admin', '$2y$10$LMCWFepMUguagdnZCLUi3eXCQT4buqW1bv7crDKO23EH0erVRN9xS');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `order_date` date DEFAULT NULL,
  `total_amount` decimal(10,2) DEFAULT NULL,
  `payment_amount` decimal(10,2) DEFAULT NULL,
  `change_amount` decimal(10,2) DEFAULT NULL,
  `admin_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `order_date`, `total_amount`, `payment_amount`, `change_amount`, `admin_id`) VALUES
(1, '2025-04-29', 50.00, 50.00, 0.00, NULL),
(2, '2025-04-29', 5.00, 5.00, 0.00, NULL),
(3, '2025-04-29', 5.00, 5.00, 0.00, NULL),
(4, '2025-04-29', 45.00, 45.00, 0.00, NULL),
(5, '2025-04-30', 45.00, 45.00, 0.00, NULL),
(6, '2025-05-07', 10.00, 10.00, 0.00, 1),
(7, '2025-05-07', 15.00, 15.00, 0.00, 1),
(8, '2025-05-07', 5.00, 5.00, 0.00, 1),
(9, '2025-05-07', 15.00, 15.00, 0.00, 1),
(10, '2025-05-07', 15.00, 15.00, 0.00, 1),
(11, '2025-05-07', 45.00, 45.00, 0.00, 1),
(12, '2025-05-07', 10.00, 10.00, 0.00, 1),
(13, '2025-05-07', 10.00, 10.00, 0.00, 1),
(14, '2025-05-07', 10.00, 10.00, 0.00, 1),
(15, '2025-05-07', 5.00, 5.00, 0.00, 1),
(16, '2025-05-07', 17.00, 17.00, 0.00, 1),
(17, '2025-05-07', 15.00, 15.00, 0.00, 1),
(18, '2025-05-07', 10.00, 10.00, 0.00, 1),
(19, '2025-05-07', 10.00, 10.00, 0.00, 1),
(20, '2025-05-07', 10.00, 10.00, 0.00, 1),
(21, '2025-05-07', 10.00, 10.00, 0.00, 1),
(22, '2025-05-07', 10.00, 10.00, 0.00, 1),
(23, '2025-05-07', 10.00, 10.00, 0.00, 1),
(24, '2025-05-07', 10.00, 10.00, 0.00, 1),
(25, '2025-05-07', 10.00, 10.00, 0.00, 1),
(26, '2025-05-07', 10.00, 10.00, 0.00, 1),
(27, '2025-05-07', 10.00, 10.00, 0.00, 1),
(28, '2025-05-07', 10.00, 10.00, 0.00, 1),
(29, '2025-05-07', 10.00, 10.00, 0.00, 1),
(30, '2025-05-07', 17.00, 17.00, 0.00, 1),
(31, '2025-05-07', 10.00, 10.00, 0.00, 1),
(32, '2025-05-07', 10.00, 10.00, 0.00, 1),
(33, '2025-05-07', 10.00, 10.00, 0.00, 1),
(34, '2025-05-07', 17.00, 17.00, 0.00, 1),
(37, '2025-05-07', 10.00, 10.00, 0.00, 1),
(38, '2025-05-07', 10.00, 10.00, 0.00, 1),
(39, '2025-05-07', 10.00, 10.00, 0.00, 1),
(40, '2025-05-08', 22.00, 22.00, 0.00, 1),
(41, '2025-05-08', 17.00, 17.00, 0.00, 1),
(42, '2025-05-08', 15.00, 15.00, 0.00, 1),
(43, '2025-05-08', 20.00, 20.00, 0.00, 1),
(44, '2025-05-08', 10.00, 10.00, 0.00, 1),
(45, '2025-05-08', 5.00, 5.00, 0.00, 1),
(46, '2025-05-08', 10.00, 10.00, 0.00, 1),
(47, '2025-05-08', 10.00, 10.00, 0.00, 1),
(48, '2025-05-08', 10.00, 10.00, 0.00, 1),
(49, '2025-05-08', 50.00, 50.00, 0.00, 1),
(50, '2025-05-08', 10.00, 10.00, 0.00, 1),
(51, '2025-05-08', 10.00, 10.00, 0.00, 1),
(52, '2025-05-08', 10.00, 10.00, 0.00, 1),
(53, '2025-05-08', 15.00, 15.00, 0.00, 1),
(54, '2025-05-08', 50.00, 50.00, 0.00, 1),
(55, '2025-05-08', 15.00, 15.00, 0.00, 1),
(56, '2025-05-08', 10.00, 10.00, 0.00, 1),
(57, '2025-05-08', 22.00, 22.00, 0.00, 1),
(58, '2025-05-08', 10.00, 10.00, 0.00, 1),
(59, '2025-05-08', 55.00, 100.00, 45.00, 1),
(60, '2025-05-08', 55.00, 1000.00, 945.00, 1);

-- --------------------------------------------------------

--
-- Table structure for table `order_item`
--

CREATE TABLE `order_item` (
  `order_item_id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `price_at_sale` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_item`
--

INSERT INTO `order_item` (`order_item_id`, `order_id`, `product_id`, `quantity`, `price_at_sale`) VALUES
(1, 1, 7, 1, 45.00),
(2, 1, 1, 1, 5.00),
(3, 2, 1, 1, 5.00),
(4, 3, 1, 1, 5.00),
(5, 4, 7, 1, 45.00),
(6, 5, 7, 1, 45.00),
(7, 6, 2, 1, 5.00),
(8, 6, 4, 1, 5.00),
(9, 7, 2, 1, 5.00),
(10, 7, 3, 1, 5.00),
(11, 7, 4, 1, 5.00),
(12, 8, 3, 1, 5.00),
(13, 9, 4, 1, 5.00),
(14, 9, 2, 1, 5.00),
(15, 9, 1, 1, 5.00),
(16, 10, 4, 1, 5.00),
(17, 10, 2, 1, 5.00),
(18, 10, 1, 1, 5.00),
(19, 11, 7, 1, 45.00),
(20, 12, 4, 1, 5.00),
(21, 12, 3, 1, 5.00),
(22, 13, 2, 1, 5.00),
(23, 13, 1, 1, 5.00),
(24, 14, 1, 1, 5.00),
(25, 14, 2, 1, 5.00),
(26, 15, 1, 1, 5.00),
(27, 16, 6, 1, 5.00),
(28, 16, 5, 1, 12.00),
(29, 17, 3, 2, 5.00),
(30, 17, 4, 1, 5.00),
(31, 18, 3, 1, 5.00),
(32, 18, 2, 1, 5.00),
(33, 19, 1, 1, 5.00),
(34, 19, 2, 1, 5.00),
(35, 20, 4, 1, 5.00),
(36, 20, 3, 1, 5.00),
(37, 21, 4, 1, 5.00),
(38, 21, 3, 1, 5.00),
(39, 22, 4, 1, 5.00),
(40, 22, 3, 1, 5.00),
(41, 23, 1, 1, 5.00),
(42, 23, 2, 1, 5.00),
(43, 24, 4, 1, 5.00),
(44, 24, 3, 1, 5.00),
(45, 25, 4, 1, 5.00),
(46, 25, 3, 1, 5.00),
(47, 26, 2, 1, 5.00),
(48, 26, 1, 1, 5.00),
(49, 27, 2, 1, 5.00),
(50, 27, 1, 1, 5.00),
(51, 28, 1, 1, 5.00),
(52, 28, 2, 1, 5.00),
(53, 29, 2, 1, 5.00),
(54, 29, 1, 1, 5.00),
(55, 30, 6, 1, 5.00),
(56, 30, 5, 1, 12.00),
(57, 31, 2, 1, 5.00),
(58, 31, 1, 1, 5.00),
(59, 32, 2, 1, 5.00),
(60, 32, 1, 1, 5.00),
(61, 33, 1, 2, 5.00),
(62, 34, 1, 1, 5.00),
(63, 34, 5, 1, 12.00),
(64, 37, 1, 1, 5.00),
(65, 37, 3, 1, 5.00),
(66, 38, 1, 1, 5.00),
(67, 38, 3, 1, 5.00),
(68, 39, 4, 1, 5.00),
(69, 39, 3, 1, 5.00),
(70, 40, 1, 1, 5.00),
(71, 40, 5, 1, 12.00),
(72, 40, 6, 1, 5.00),
(73, 41, 4, 1, 5.00),
(74, 41, 5, 1, 12.00),
(75, 42, 2, 1, 5.00),
(76, 42, 1, 1, 5.00),
(77, 42, 4, 1, 5.00),
(78, 43, 1, 4, 5.00),
(79, 44, 3, 1, 5.00),
(80, 44, 2, 1, 5.00),
(81, 45, 1, 1, 5.00),
(82, 46, 3, 1, 5.00),
(83, 46, 2, 1, 5.00),
(84, 47, 2, 1, 5.00),
(85, 47, 1, 1, 5.00),
(86, 48, 2, 2, 5.00),
(87, 49, 7, 1, 45.00),
(88, 49, 2, 1, 5.00),
(89, 50, 3, 1, 5.00),
(90, 50, 2, 1, 5.00),
(91, 51, 2, 1, 5.00),
(92, 51, 4, 1, 5.00),
(93, 52, 2, 1, 5.00),
(94, 52, 1, 1, 5.00),
(95, 53, 2, 1, 5.00),
(96, 53, 1, 1, 5.00),
(97, 53, 4, 1, 5.00),
(98, 54, 7, 1, 45.00),
(99, 54, 1, 1, 5.00),
(100, 55, 1, 1, 5.00),
(101, 55, 2, 1, 5.00),
(102, 55, 3, 1, 5.00),
(103, 56, 3, 1, 5.00),
(104, 56, 4, 1, 5.00),
(105, 57, 2, 1, 5.00),
(106, 57, 1, 1, 5.00),
(107, 57, 5, 1, 12.00),
(108, 58, 2, 1, 5.00),
(109, 58, 3, 1, 5.00),
(110, 59, 4, 1, 5.00),
(111, 59, 2, 1, 5.00),
(112, 59, 7, 1, 45.00),
(113, 60, 4, 1, 5.00),
(114, 60, 2, 1, 5.00),
(115, 60, 7, 1, 45.00);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `quantity_in_stock` int(11) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `name`, `quantity_in_stock`, `price`, `image_path`) VALUES
(1, 'Monay', 18, 5.00, 'https://i.pinimg.com/originals/2e/a3/65/2ea365e4f24bbfbf5650751edbca7afa.jpg'),
(2, 'Hopia', 45, 5.00, 'https://almostnordic.com/wp-content/uploads/2021/12/ube-hopia-recipe.jpg'),
(3, 'Ensaymada', 0, 5.00, 'uploads/IMG-681b3742b503f0.01466458.png'),
(4, 'Pan de Coco', 1, 5.00, 'https://www.foxyfolksy.com/wp-content/uploads/2021/03/pan-de-coco-1200t.jpg'),
(5, 'Torta', 14, 12.00, 'https://www.pingdesserts.com/wp-content/uploads/2013/06/Cebu-Torta-Cake-Recipe-1.jpg'),
(6, 'Spanish Bread', 17, 5.00, 'https://1.bp.blogspot.com/-_tLK1fKcsV4/YBLBujMN6vI/AAAAAAAAGWM/f48FRKGP1iM-fJj4Cyehm1h4sy4VMnxzgCLcBGAsYHQ/s1818/Spanish%2BBread%2BLNN.jpg'),
(7, 'Slice Bread', 2, 45.00, 'https://mediaindia.eu/wp-content/uploads/2016/05/bread.jpg');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`admin_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indexes for table `order_item`
--
ALTER TABLE `order_item`
  ADD PRIMARY KEY (`order_item_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- AUTO_INCREMENT for table `order_item`
--
ALTER TABLE `order_item`
  MODIFY `order_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=116;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `admin` (`admin_id`),
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`admin_id`) REFERENCES `admin` (`admin_id`);

--
-- Constraints for table `order_item`
--
ALTER TABLE `order_item`
  ADD CONSTRAINT `order_item_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`),
  ADD CONSTRAINT `order_item_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

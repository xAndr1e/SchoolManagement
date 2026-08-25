-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 10, 2026 at 09:23 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `school_lab`
--

-- --------------------------------------------------------

--
-- Table structure for table `balistic_lab_borrow`
--

CREATE TABLE `balistic_lab_borrow` (
  `id` int(11) NOT NULL,
  `laboratory` varchar(100) NOT NULL,
  `borrower_name` varchar(100) NOT NULL,
  `student_id` varchar(255) NOT NULL,
  `section` varchar(255) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `quantity` int(11) NOT NULL,
  `borrowed_date` date NOT NULL DEFAULT current_timestamp(),
  `expected_return` date DEFAULT current_timestamp(),
  `returned_date` date DEFAULT current_timestamp(),
  `status` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `balistic_lab_borrow`
--

INSERT INTO `balistic_lab_borrow` (`id`, `laboratory`, `borrower_name`, `student_id`, `section`, `item_name`, `quantity`, `borrowed_date`, `expected_return`, `returned_date`, `status`) VALUES
(1, 'Ballistics Lab', 'asdfads', 'asdfads', 'Crim 1001', 'Ballistic Kit', 1, '2026-08-11', '2026-08-14', '2026-08-11', 'Returned');

-- --------------------------------------------------------

--
-- Table structure for table `balistic_lab_damage`
--

CREATE TABLE `balistic_lab_damage` (
  `id` int(11) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `laboratory` varchar(100) NOT NULL,
  `issue` varchar(100) NOT NULL,
  `reported_by` varchar(100) NOT NULL,
  `date_reported` date NOT NULL DEFAULT current_timestamp(),
  `status` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `balistic_lab_inventory`
--

CREATE TABLE `balistic_lab_inventory` (
  `id` int(11) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `category` varchar(100) NOT NULL,
  `laboratory` varchar(100) NOT NULL,
  `total_item` int(11) NOT NULL,
  `available_item` int(11) NOT NULL,
  `status` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `balistic_lab_inventory`
--

INSERT INTO `balistic_lab_inventory` (`id`, `item_name`, `category`, `laboratory`, `total_item`, `available_item`, `status`) VALUES
(1, 'Ballistic Kit', 'Ballistic kit', 'Crime Scene Laboratory', 12, 12, 'Under Maintenance');

-- --------------------------------------------------------

--
-- Table structure for table `balistic_lab_monitoring`
--

CREATE TABLE `balistic_lab_monitoring` (
  `id` int(11) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `laboratory` varchar(100) NOT NULL,
  `equipment_condition` varchar(100) NOT NULL,
  `last_checked` date NOT NULL DEFAULT current_timestamp(),
  `checked_by` varchar(100) NOT NULL,
  `remarks` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `chemistry_lab_borrow`
--

CREATE TABLE `chemistry_lab_borrow` (
  `id` int(11) NOT NULL,
  `laboratory` varchar(100) NOT NULL,
  `borrower_name` varchar(100) NOT NULL,
  `student_id` varchar(255) NOT NULL,
  `section` varchar(255) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `quantity` int(11) NOT NULL,
  `borrowed_date` date NOT NULL DEFAULT current_timestamp(),
  `expected_return` date DEFAULT current_timestamp(),
  `returned_date` date DEFAULT current_timestamp(),
  `status` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `chemistry_lab_borrow`
--

INSERT INTO `chemistry_lab_borrow` (`id`, `laboratory`, `borrower_name`, `student_id`, `section`, `item_name`, `quantity`, `borrowed_date`, `expected_return`, `returned_date`, `status`) VALUES
(1, 'Chemistry Lab', 'sdfad', 'asdf', 'dsffa', 'asdf', 12, '2026-08-11', '2026-08-14', '2026-08-11', 'Returned'),
(2, 'Chemistry Lab', 'Sim Doe', 'S1234564', 'Crim 1101', 'Chemistry Kit', 1, '2026-08-11', '2026-08-14', NULL, 'Borrowed');

-- --------------------------------------------------------

--
-- Table structure for table `chemistry_lab_damage`
--

CREATE TABLE `chemistry_lab_damage` (
  `id` int(11) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `laboratory` varchar(100) NOT NULL,
  `issue` varchar(100) NOT NULL,
  `reported_by` varchar(100) NOT NULL,
  `date_reported` date NOT NULL DEFAULT current_timestamp(),
  `status` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `chemistry_lab_inventory`
--

CREATE TABLE `chemistry_lab_inventory` (
  `id` int(11) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `category` varchar(100) NOT NULL,
  `laboratory` varchar(100) NOT NULL,
  `total_item` int(11) NOT NULL,
  `available_item` int(11) NOT NULL,
  `status` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `chemistry_lab_inventory`
--

INSERT INTO `chemistry_lab_inventory` (`id`, `item_name`, `category`, `laboratory`, `total_item`, `available_item`, `status`) VALUES
(1, 'dawdwaiuhjgt', 'dawdwa', 'Chemistry Laboratory', 21, 21, 'Damaged');

-- --------------------------------------------------------

--
-- Table structure for table `chemistry_lab_monitoring`
--

CREATE TABLE `chemistry_lab_monitoring` (
  `id` int(11) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `laboratory` varchar(100) NOT NULL,
  `equipment_condition` varchar(100) NOT NULL,
  `last_checked` date NOT NULL DEFAULT current_timestamp(),
  `checked_by` varchar(100) NOT NULL,
  `remarks` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `chemistry_lab_monitoring`
--

INSERT INTO `chemistry_lab_monitoring` (`id`, `item_name`, `laboratory`, `equipment_condition`, `last_checked`, `checked_by`, `remarks`) VALUES
(1, 'fgnhjgkljhd', 'Chemistry Laboratory', 'Working', '2026-08-05', 'ertheklrthjsdfads', 'oreitjuyioptyhitorehg'),
(2, 'asdfasd', 'Chemistry Laboratory', 'Unavailable', '2026-08-05', 'dffadscvzcx', 'sdfasdfads');

-- --------------------------------------------------------

--
-- Table structure for table `crime_lab_borrow`
--

CREATE TABLE `crime_lab_borrow` (
  `id` int(11) NOT NULL,
  `laboratory` varchar(100) NOT NULL,
  `borrower_name` varchar(100) NOT NULL,
  `student_id` varchar(255) NOT NULL,
  `section` varchar(255) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `quantity` int(11) NOT NULL,
  `borrowed_date` date NOT NULL DEFAULT current_timestamp(),
  `expected_return` date DEFAULT current_timestamp(),
  `returned_date` date DEFAULT current_timestamp(),
  `status` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `crime_lab_borrow`
--

INSERT INTO `crime_lab_borrow` (`id`, `laboratory`, `borrower_name`, `student_id`, `section`, `item_name`, `quantity`, `borrowed_date`, `expected_return`, `returned_date`, `status`) VALUES
(1, 'Crime Scene Lab', 'asdfasd', 'asdfas', 'asdf', 'adsfads', 1, '2026-08-11', '2026-08-14', '2026-08-11', 'Returned');

-- --------------------------------------------------------

--
-- Table structure for table `crime_lab_damage`
--

CREATE TABLE `crime_lab_damage` (
  `id` int(11) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `laboratory` varchar(100) NOT NULL,
  `issue` varchar(100) NOT NULL,
  `reported_by` varchar(100) NOT NULL,
  `date_reported` date NOT NULL DEFAULT current_timestamp(),
  `status` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `crime_lab_inventory`
--

CREATE TABLE `crime_lab_inventory` (
  `id` int(11) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `category` varchar(100) NOT NULL,
  `laboratory` varchar(100) NOT NULL,
  `total_item` int(11) NOT NULL,
  `available_item` int(11) NOT NULL,
  `status` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `crime_lab_inventory`
--

INSERT INTO `crime_lab_inventory` (`id`, `item_name`, `category`, `laboratory`, `total_item`, `available_item`, `status`) VALUES
(2, 'Brushes', 'Latent print kits', 'Crime Scene Laboratory', 10, 50, 'Working');

-- --------------------------------------------------------

--
-- Table structure for table `crime_lab_monitoring`
--

CREATE TABLE `crime_lab_monitoring` (
  `id` int(11) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `laboratory` varchar(100) NOT NULL,
  `equipment_condition` varchar(100) NOT NULL,
  `last_checked` date NOT NULL DEFAULT current_timestamp(),
  `checked_by` varchar(100) NOT NULL,
  `remarks` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `crime_lab_monitoring`
--

INSERT INTO `crime_lab_monitoring` (`id`, `item_name`, `laboratory`, `equipment_condition`, `last_checked`, `checked_by`, `remarks`) VALUES
(1, 'afdasdf', 'Crime Scene Laboratory', 'Working', '2026-08-06', 'asdfsda', 'try');

-- --------------------------------------------------------

--
-- Table structure for table `defense_lab_borrow`
--

CREATE TABLE `defense_lab_borrow` (
  `id` int(11) NOT NULL,
  `laboratory` varchar(100) NOT NULL,
  `borrower_name` varchar(100) NOT NULL,
  `student_id` varchar(255) NOT NULL,
  `section` varchar(255) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `quantity` int(11) NOT NULL,
  `borrowed_date` date NOT NULL DEFAULT current_timestamp(),
  `expected_return` date DEFAULT current_timestamp(),
  `returned_date` date DEFAULT current_timestamp(),
  `status` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `defense_lab_borrow`
--

INSERT INTO `defense_lab_borrow` (`id`, `laboratory`, `borrower_name`, `student_id`, `section`, `item_name`, `quantity`, `borrowed_date`, `expected_return`, `returned_date`, `status`) VALUES
(1, 'Defense & Tactics Lab', 'sdfas', 'asdfads', 'asdfasd', 'asdfads', 2, '2026-08-11', '2026-08-14', '2026-08-12', 'Returned');

-- --------------------------------------------------------

--
-- Table structure for table `defense_lab_damage`
--

CREATE TABLE `defense_lab_damage` (
  `id` int(11) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `laboratory` varchar(100) NOT NULL,
  `issue` varchar(100) NOT NULL,
  `reported_by` varchar(100) NOT NULL,
  `date_reported` date NOT NULL DEFAULT current_timestamp(),
  `status` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `defense_lab_damage`
--

INSERT INTO `defense_lab_damage` (`id`, `item_name`, `laboratory`, `issue`, `reported_by`, `date_reported`, `status`) VALUES
(1, 'asdfasd', 'Defense Tactics Laboratory', 'dfasd', 'sdfgfdsg', '2026-08-29', 'Working');

-- --------------------------------------------------------

--
-- Table structure for table `defense_lab_inventory`
--

CREATE TABLE `defense_lab_inventory` (
  `id` int(11) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `category` varchar(100) NOT NULL,
  `laboratory` varchar(100) NOT NULL,
  `total_item` int(11) NOT NULL,
  `available_item` int(11) NOT NULL,
  `status` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `defense_lab_monitoring`
--

CREATE TABLE `defense_lab_monitoring` (
  `id` int(11) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `laboratory` varchar(100) NOT NULL,
  `equipment_condition` varchar(100) NOT NULL,
  `last_checked` date NOT NULL DEFAULT current_timestamp(),
  `checked_by` varchar(100) NOT NULL,
  `remarks` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `defense_lab_monitoring`
--

INSERT INTO `defense_lab_monitoring` (`id`, `item_name`, `laboratory`, `equipment_condition`, `last_checked`, `checked_by`, `remarks`) VALUES
(1, 'sdfsda', 'Defense Tactics Laboratory', 'Working', '2026-08-06', 'asdfads', 'sdfgsfdg');

-- --------------------------------------------------------

--
-- Table structure for table `fingerprint_lab_borrow`
--

CREATE TABLE `fingerprint_lab_borrow` (
  `id` int(11) NOT NULL,
  `laboratory` varchar(100) NOT NULL,
  `borrower_name` varchar(100) NOT NULL,
  `student_id` int(12) NOT NULL,
  `section` int(12) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `quantity` int(11) NOT NULL,
  `borrowed_date` date NOT NULL DEFAULT current_timestamp(),
  `expected_return` date DEFAULT current_timestamp(),
  `returned_date` date DEFAULT current_timestamp(),
  `status` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `fingerprint_lab_borrow`
--

INSERT INTO `fingerprint_lab_borrow` (`id`, `laboratory`, `borrower_name`, `student_id`, `section`, `item_name`, `quantity`, `borrowed_date`, `expected_return`, `returned_date`, `status`) VALUES
(1, 'Fingerprint Lab', 'sdfads', 0, 0, 'asdfasd', 2, '2026-08-11', '2026-08-14', '2026-08-11', 'Returned');

-- --------------------------------------------------------

--
-- Table structure for table `fingerprint_lab_damage`
--

CREATE TABLE `fingerprint_lab_damage` (
  `id` int(11) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `laboratory` varchar(100) NOT NULL,
  `issue` varchar(100) NOT NULL,
  `reported_by` varchar(100) NOT NULL,
  `date_reported` date NOT NULL DEFAULT current_timestamp(),
  `status` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `fingerprint_lab_damage`
--

INSERT INTO `fingerprint_lab_damage` (`id`, `item_name`, `laboratory`, `issue`, `reported_by`, `date_reported`, `status`) VALUES
(1, 'asdfasd', 'Fingerprint Laboratory', 'asdfasedf', 'dsfsdaf', '2026-08-03', 'Working');

-- --------------------------------------------------------

--
-- Table structure for table `fingerprint_lab_inventory`
--

CREATE TABLE `fingerprint_lab_inventory` (
  `id` int(11) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `category` varchar(100) NOT NULL,
  `laboratory` varchar(100) NOT NULL,
  `total_item` int(11) NOT NULL,
  `available_item` int(11) NOT NULL,
  `status` varchar(250) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `fingerprint_lab_inventory`
--

INSERT INTO `fingerprint_lab_inventory` (`id`, `item_name`, `category`, `laboratory`, `total_item`, `available_item`, `status`) VALUES
(7, 'adwwa', 'dwadwa', 'Fingerprint Laboratory', 12, 123, 'Working'),
(8, 'okkk', 'mnjnjn', 'Fingerprint Laboratory', 989, 262, 'Damaged'),
(9, 'l;pll', 'jhiojo', 'Fingerprint Laboratory', 542, 2652165, 'Damaged'),
(10, '23', '511', 'Fingerprint Laboratory', 65565, 565, 'Damaged'),
(11, 'dawd', 'dawdwa', 'Fingerprint Laboratory', 12, 12, 'Unavailable');

-- --------------------------------------------------------

--
-- Table structure for table `fingerprint_lab_monitoring`
--

CREATE TABLE `fingerprint_lab_monitoring` (
  `id` int(11) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `laboratory` varchar(100) NOT NULL,
  `equipment_condition` varchar(100) NOT NULL,
  `last_checked` date NOT NULL DEFAULT current_timestamp(),
  `checked_by` varchar(100) NOT NULL,
  `remarks` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `fingerprint_lab_monitoring`
--

INSERT INTO `fingerprint_lab_monitoring` (`id`, `item_name`, `laboratory`, `equipment_condition`, `last_checked`, `checked_by`, `remarks`) VALUES
(1, 'saddfasd', 'Fingerprint Laboratory', 'Under Maintenance', '2026-08-06', 'sdfadsczdcascsac', 'asdfxcsdffEW'),
(2, 'ADSFADSCASD', 'Fingerprint Laboratory', 'Working', '2026-08-06', 'sadffsdfvxdv', 'dfsdcddvgrzs');

-- --------------------------------------------------------

--
-- Table structure for table `he_lab_borrow`
--

CREATE TABLE `he_lab_borrow` (
  `id` int(11) NOT NULL,
  `laboratory` varchar(100) NOT NULL,
  `borrower_name` varchar(100) NOT NULL,
  `student_id` varchar(255) NOT NULL,
  `section` varchar(255) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `quantity` int(11) NOT NULL,
  `borrowed_date` date NOT NULL DEFAULT current_timestamp(),
  `expected_return` date DEFAULT current_timestamp(),
  `returned_date` date DEFAULT current_timestamp(),
  `status` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `he_lab_borrow`
--

INSERT INTO `he_lab_borrow` (`id`, `laboratory`, `borrower_name`, `student_id`, `section`, `item_name`, `quantity`, `borrowed_date`, `expected_return`, `returned_date`, `status`) VALUES
(3, 'HE Lab', 'Test', '123456456', 'HE 101', 'Tongs', 1, '2026-08-10', '2026-08-12', NULL, 'Borrowed');

-- --------------------------------------------------------

--
-- Table structure for table `he_lab_damage`
--

CREATE TABLE `he_lab_damage` (
  `id` int(11) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `laboratory` varchar(100) NOT NULL,
  `issue` varchar(100) NOT NULL,
  `reported_by` varchar(100) NOT NULL,
  `date_reported` date NOT NULL DEFAULT current_timestamp(),
  `status` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `he_lab_damage`
--

INSERT INTO `he_lab_damage` (`id`, `item_name`, `laboratory`, `issue`, `reported_by`, `date_reported`, `status`) VALUES
(2, 'efadsfaa', 'Home Economics Laboratory', 'fasdfasd', 'asdfasdf', '2026-08-03', 'Damaged');

-- --------------------------------------------------------

--
-- Table structure for table `he_lab_inventory`
--

CREATE TABLE `he_lab_inventory` (
  `id` int(11) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `category` varchar(100) NOT NULL,
  `laboratory` varchar(100) NOT NULL,
  `total_item` int(11) NOT NULL,
  `available_item` int(11) NOT NULL,
  `status` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `he_lab_inventory`
--

INSERT INTO `he_lab_inventory` (`id`, `item_name`, `category`, `laboratory`, `total_item`, `available_item`, `status`) VALUES
(3, 'zssz', 'dawdwa', 'Home Economics Laboratory', 2, 2, 'Under Maintenance');

-- --------------------------------------------------------

--
-- Table structure for table `he_lab_monitoring`
--

CREATE TABLE `he_lab_monitoring` (
  `id` int(11) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `laboratory` varchar(100) NOT NULL,
  `equipment_condition` varchar(100) NOT NULL,
  `last_checked` date NOT NULL DEFAULT current_timestamp(),
  `checked_by` varchar(100) NOT NULL,
  `remarks` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `it_lab1_borrow`
--

CREATE TABLE `it_lab1_borrow` (
  `id` int(11) NOT NULL,
  `laboratory` varchar(100) NOT NULL,
  `borrower_name` varchar(100) NOT NULL,
  `student_id` varchar(255) NOT NULL,
  `section` varchar(255) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `quantity` int(11) NOT NULL,
  `borrowed_date` date NOT NULL DEFAULT current_timestamp(),
  `expected_return` date DEFAULT current_timestamp(),
  `returned_date` date DEFAULT current_timestamp(),
  `status` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `it_lab1_borrow`
--

INSERT INTO `it_lab1_borrow` (`id`, `laboratory`, `borrower_name`, `student_id`, `section`, `item_name`, `quantity`, `borrowed_date`, `expected_return`, `returned_date`, `status`) VALUES
(1, 'Ballistics Lab', 'sdfgdf', 'sdfgsfd', 'sdfgsfd', 'sdfgsdf', 3, '2026-08-11', '2026-08-14', '2026-08-11', 'Returned'),
(2, 'IT Lab 1', 'dfadsfa', 'afadsfa', 'asdfasdf', 'adsfadsf', 2, '2026-08-11', '2026-08-14', NULL, 'Borrowed');

-- --------------------------------------------------------

--
-- Table structure for table `it_lab1_damage`
--

CREATE TABLE `it_lab1_damage` (
  `id` int(11) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `laboratory` varchar(100) NOT NULL,
  `issue` varchar(100) NOT NULL,
  `reported_by` varchar(100) NOT NULL,
  `date_reported` date NOT NULL DEFAULT current_timestamp(),
  `status` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `it_lab1_damage`
--

INSERT INTO `it_lab1_damage` (`id`, `item_name`, `laboratory`, `issue`, `reported_by`, `date_reported`, `status`) VALUES
(1, 'sdfadsf', 'IT Laboratory 1', 'adwsfasdf', 'asdfasdf', '2026-08-02', 'Under Maintenance');

-- --------------------------------------------------------

--
-- Table structure for table `it_lab1_inventory`
--

CREATE TABLE `it_lab1_inventory` (
  `id` int(11) NOT NULL,
  `item_name` varchar(150) NOT NULL,
  `category` varchar(50) NOT NULL,
  `laboratory` varchar(100) NOT NULL,
  `total_item` int(11) NOT NULL,
  `available_item` int(11) NOT NULL,
  `status` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `it_lab1_inventory`
--

INSERT INTO `it_lab1_inventory` (`id`, `item_name`, `category`, `laboratory`, `total_item`, `available_item`, `status`) VALUES
(2, 'Mouse', 'Computer', 'IT Lab 1', 21, 21, 'Working'),
(3, 'Projector', 'PC', 'IT Lab 1', 2, 5, 'Working');

-- --------------------------------------------------------

--
-- Table structure for table `it_lab1_monitoring`
--

CREATE TABLE `it_lab1_monitoring` (
  `id` int(11) NOT NULL,
  `item` varchar(100) NOT NULL,
  `laboratory` varchar(100) NOT NULL,
  `equipment_condition` varchar(100) NOT NULL,
  `last_checked` date NOT NULL DEFAULT current_timestamp(),
  `checked_by` varchar(100) NOT NULL,
  `remarks` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `it_lab2_borrow`
--

CREATE TABLE `it_lab2_borrow` (
  `id` int(11) NOT NULL,
  `laboratory` varchar(100) NOT NULL,
  `borrower_name` varchar(100) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `student_id` varchar(255) NOT NULL,
  `section` varchar(255) NOT NULL,
  `quantity` int(11) NOT NULL,
  `borrowed_date` date NOT NULL DEFAULT current_timestamp(),
  `expected_return` date DEFAULT current_timestamp(),
  `returned_date` date DEFAULT current_timestamp(),
  `status` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `it_lab2_borrow`
--

INSERT INTO `it_lab2_borrow` (`id`, `laboratory`, `borrower_name`, `item_name`, `student_id`, `section`, `quantity`, `borrowed_date`, `expected_return`, `returned_date`, `status`) VALUES
(1, 'IT Lab 2', 'sdfasd', 'asdfa', 'sadfasd', 'asdfas', 2, '2026-08-11', '2026-08-14', '2026-08-11', 'Returned');

-- --------------------------------------------------------

--
-- Table structure for table `it_lab2_damage`
--

CREATE TABLE `it_lab2_damage` (
  `id` int(11) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `laboratory` varchar(100) NOT NULL,
  `issue` varchar(100) NOT NULL,
  `reported_by` varchar(100) NOT NULL,
  `date_reported` date NOT NULL DEFAULT current_timestamp(),
  `status` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `it_lab2_inventory`
--

CREATE TABLE `it_lab2_inventory` (
  `id` int(11) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `category` varchar(100) NOT NULL,
  `laboratory` varchar(100) NOT NULL,
  `total_item` int(11) NOT NULL,
  `available_item` int(11) NOT NULL,
  `status` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `it_lab2_inventory`
--

INSERT INTO `it_lab2_inventory` (`id`, `item_name`, `category`, `laboratory`, `total_item`, `available_item`, `status`) VALUES
(2, 'Monitor', 'PC', 'IT Lab 2', 32, 32, 'Working');

-- --------------------------------------------------------

--
-- Table structure for table `it_lab2_monitoring`
--

CREATE TABLE `it_lab2_monitoring` (
  `id` int(11) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `laboratory` varchar(100) NOT NULL,
  `equipment_condition` varchar(100) NOT NULL,
  `last_checked` date NOT NULL DEFAULT current_timestamp(),
  `checked_by` varchar(100) NOT NULL,
  `remarks` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `it_lab3_borrow`
--

CREATE TABLE `it_lab3_borrow` (
  `id` int(11) NOT NULL,
  `laboratory` varchar(100) NOT NULL,
  `borrower_name` varchar(100) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `student_id` varchar(255) NOT NULL,
  `section` varchar(255) NOT NULL,
  `quantity` int(11) NOT NULL,
  `borrowed_date` date NOT NULL DEFAULT current_timestamp(),
  `expected_return` date DEFAULT current_timestamp(),
  `returned_date` date DEFAULT current_timestamp(),
  `status` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `it_lab3_borrow`
--

INSERT INTO `it_lab3_borrow` (`id`, `laboratory`, `borrower_name`, `item_name`, `student_id`, `section`, `quantity`, `borrowed_date`, `expected_return`, `returned_date`, `status`) VALUES
(2, 'IT Lab 3', 'asdfads', 'asdf', 'sdfa', 'asdfa', 2, '2026-08-11', '2026-08-14', '2026-08-11', 'Returned');

-- --------------------------------------------------------

--
-- Table structure for table `it_lab3_damage`
--

CREATE TABLE `it_lab3_damage` (
  `id` int(11) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `laboratory` varchar(100) NOT NULL,
  `issue` varchar(100) NOT NULL,
  `reported_by` varchar(100) NOT NULL,
  `date_reported` date NOT NULL DEFAULT current_timestamp(),
  `status` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `it_lab3_inventory`
--

CREATE TABLE `it_lab3_inventory` (
  `id` int(11) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `category` varchar(100) NOT NULL,
  `laboratory` varchar(100) NOT NULL,
  `total_item` int(11) NOT NULL,
  `available_item` int(11) NOT NULL,
  `status` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `it_lab3_inventory`
--

INSERT INTO `it_lab3_inventory` (`id`, `item_name`, `category`, `laboratory`, `total_item`, `available_item`, `status`) VALUES
(4, 'jhafdg', 'dfada', 'IT Lab 3', 23, 23, 'Under Maintenance'),
(5, 'try', 'dawa', 'IT Lab 3', 43, 43, 'Damaged'),
(6, 'Mouse', 'Computer', 'IT Lab 3', 45, 45, 'Damaged'),
(7, 'Monitor', 'PC', 'IT Lab 3', 123, 123, 'Working'),
(8, 'Mouse', 'PC', 'IT Lab 3', 30, 12, 'Working');

-- --------------------------------------------------------

--
-- Table structure for table `it_lab3_monitoring`
--

CREATE TABLE `it_lab3_monitoring` (
  `id` int(11) NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `laboratory` varchar(255) NOT NULL,
  `equipment_condition` varchar(255) NOT NULL,
  `last_checked` date NOT NULL DEFAULT current_timestamp(),
  `checked_by` varchar(255) NOT NULL,
  `remarks` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `laboratories`
--

CREATE TABLE `laboratories` (
  `lab_id` int(11) NOT NULL,
  `laboratory_name` varchar(100) NOT NULL,
  `building` varchar(100) DEFAULT NULL,
  `floor` varchar(20) DEFAULT NULL,
  `room_number` varchar(20) DEFAULT NULL,
  `capacity` int(11) DEFAULT NULL,
  `status` enum('Available','Maintenance','Closed') DEFAULT 'Available',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `laboratories`
--

INSERT INTO `laboratories` (`lab_id`, `laboratory_name`, `building`, `floor`, `room_number`, `capacity`, `status`, `created_at`) VALUES
(1, 'IT Laboratory 1', NULL, '2nd Floor', NULL, NULL, 'Available', '2026-08-08 02:44:27'),
(2, 'Psychology Laboratory', NULL, NULL, NULL, NULL, 'Available', '2026-08-08 10:13:27'),
(3, 'HE Laboratory', NULL, NULL, NULL, NULL, 'Available', '2026-08-08 10:13:27'),
(4, 'Chemistry Laboratory', NULL, NULL, NULL, NULL, 'Available', '2026-08-08 10:13:27'),
(5, 'Fingerprint Laboratory', NULL, NULL, NULL, NULL, 'Available', '2026-08-08 10:13:27'),
(6, 'Crime Scene Laboratory', NULL, NULL, NULL, NULL, 'Available', '2026-08-08 10:13:27'),
(7, 'Ballistics Laboratory', NULL, NULL, NULL, NULL, 'Available', '2026-08-08 10:13:27'),
(8, 'Questioned Documents Laboratory', NULL, NULL, NULL, NULL, 'Available', '2026-08-08 10:13:27'),
(9, 'Defense and Tactics Laboratory', NULL, NULL, NULL, NULL, 'Available', '2026-08-08 10:13:27'),
(10, 'Physics Laboratory', NULL, NULL, NULL, NULL, 'Available', '2026-08-08 10:13:27'),
(11, 'IT Laboratory 2', NULL, NULL, NULL, NULL, 'Available', '2026-08-08 10:13:27'),
(12, 'IT Laboratory 3', NULL, NULL, NULL, NULL, 'Available', '2026-08-08 10:13:27');

-- --------------------------------------------------------

--
-- Table structure for table `laboratory_schedule`
--

CREATE TABLE `laboratory_schedule` (
  `schedule_id` int(11) NOT NULL,
  `lab_id` int(11) NOT NULL,
  `subject_code` varchar(20) DEFAULT NULL,
  `subject_name` varchar(100) DEFAULT NULL,
  `instructor` varchar(100) DEFAULT NULL,
  `section` varchar(50) DEFAULT NULL,
  `day` enum('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday') DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `semester` enum('1st','2nd','Summer') DEFAULT NULL,
  `school_year` varchar(20) DEFAULT NULL,
  `status` enum('Scheduled','Completed','Cancelled') DEFAULT 'Scheduled',
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `laboratory_schedule`
--

INSERT INTO `laboratory_schedule` (`schedule_id`, `lab_id`, `subject_code`, `subject_name`, `instructor`, `section`, `day`, `start_time`, `end_time`, `semester`, `school_year`, `status`, `remarks`, `created_at`) VALUES
(14, 2, 'asdfad', 'dfgadfs', 'asdfasd', 'asdfas', 'Monday', '06:00:00', '07:00:00', '1st', '2025-2026', 'Scheduled', 'sdfasfas', '2026-08-08 10:03:54'),
(17, 12, 'asdfasdf', 'sdfas', 'fasddfdasd', 'dafasd', 'Tuesday', '06:26:00', '07:26:00', '1st', '2025-2026', 'Completed', 'asdfasdf', '2026-08-08 10:26:15'),
(18, 10, 'asddfasd', 'fafsdgasd', 'sadfasd', 'sdfgadgfa', 'Monday', '06:42:00', '07:43:00', '2nd', '2025-2026', 'Scheduled', 'sdfSDFASF', '2026-08-08 10:43:11'),
(19, 1, 'DM', 'Data Mining', 'Johnny Doe', 'BSIS 4101', 'Monday', '13:00:00', '16:00:00', '1st', '2025-2026', 'Scheduled', '3rd Floor', '2026-08-10 15:01:44');

-- --------------------------------------------------------

--
-- Table structure for table `phys_lab_borrow`
--

CREATE TABLE `phys_lab_borrow` (
  `id` int(11) NOT NULL,
  `laboratory` varchar(255) NOT NULL,
  `borrower_name` varchar(100) NOT NULL,
  `student_id` varchar(255) NOT NULL,
  `section` varchar(255) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `quantity` int(11) NOT NULL,
  `borrowed_date` date NOT NULL DEFAULT current_timestamp(),
  `expected_return` date DEFAULT current_timestamp(),
  `returned_date` date DEFAULT current_timestamp(),
  `status` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `phys_lab_borrow`
--

INSERT INTO `phys_lab_borrow` (`id`, `laboratory`, `borrower_name`, `student_id`, `section`, `item_name`, `quantity`, `borrowed_date`, `expected_return`, `returned_date`, `status`) VALUES
(9, 'Physics Laboratory', 'sdfgadsfs', 'dasdfasdf', 'asdfasdf', 'asdfasd', 1, '2026-08-09', '2026-08-13', '2026-08-09', 'Returned'),
(10, 'Physics Laboratory', 'sdfgadsfs', 'adfasdf', 'asdfadf', 'waerfasd', 1, '2026-08-09', '2026-08-15', NULL, 'Borrowed');

-- --------------------------------------------------------

--
-- Table structure for table `phys_lab_damage`
--

CREATE TABLE `phys_lab_damage` (
  `id` int(100) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `laboratory` varchar(100) NOT NULL,
  `issue` varchar(100) NOT NULL,
  `reported_by` varchar(100) NOT NULL,
  `date_reported` date NOT NULL DEFAULT current_timestamp(),
  `status` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `phys_lab_damage`
--

INSERT INTO `phys_lab_damage` (`id`, `item_name`, `laboratory`, `issue`, `reported_by`, `date_reported`, `status`) VALUES
(10, 'wefasd', 'Physics Laboratory', 'asdfasd', 'asdfadsf', '2026-08-08', 'Damaged');

-- --------------------------------------------------------

--
-- Table structure for table `phys_lab_inventory`
--

CREATE TABLE `phys_lab_inventory` (
  `id` int(11) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `category` varchar(100) NOT NULL,
  `laboratory` varchar(100) NOT NULL,
  `total_item` int(11) NOT NULL,
  `available_item` int(11) NOT NULL,
  `status` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `phys_lab_inventory`
--

INSERT INTO `phys_lab_inventory` (`id`, `item_name`, `category`, `laboratory`, `total_item`, `available_item`, `status`) VALUES
(2, 'ddawdaw', 'dwdwa', 'wadaw', 12, 10, 'Damaged'),
(13, '232', 'rer', 'Physics Lab', 23, 12, 'Working'),
(15, 'dwaad', 'dawa', 'Physics Lab', 12, 12, 'Working'),
(16, 'fdfsd', '123', 'Physics Lab', 123, 123, 'Working'),
(18, 'adf', 'ffadf', 'Psychology Lab', 3, 14, 'Working'),
(19, 'afdaa', 'fada', 'Physics Lab', 142, 432, 'Damaged'),
(20, 'zssz', 'afdfzs', 'Physics Lab', 3, 3, 'Working'),
(21, 'TRRRRy', 'afdfzs', 'Psychology Lab', 10, 10, 'Working'),
(22, 'dfasdgf', 'dawdwa', 'Psychology Lab', 23, 23, 'Under Maintenance');

-- --------------------------------------------------------

--
-- Table structure for table `phys_lab_monitoring`
--

CREATE TABLE `phys_lab_monitoring` (
  `id` int(11) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `laboratory` varchar(100) NOT NULL,
  `equipment_condition` varchar(100) NOT NULL,
  `last_checked` date NOT NULL DEFAULT current_timestamp(),
  `checked_by` varchar(100) NOT NULL,
  `remarks` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `phys_lab_monitoring`
--

INSERT INTO `phys_lab_monitoring` (`id`, `item_name`, `laboratory`, `equipment_condition`, `last_checked`, `checked_by`, `remarks`) VALUES
(1, 'sdfasdf', 'Physics Lab', 'Damaged', '2026-08-08', 'asdfads', 'asdfasddsfasd');

-- --------------------------------------------------------

--
-- Table structure for table `psy_lab_borrow`
--

CREATE TABLE `psy_lab_borrow` (
  `id` int(11) NOT NULL,
  `laboratory` varchar(100) NOT NULL,
  `borrower_name` varchar(100) NOT NULL,
  `student_id` varchar(255) NOT NULL,
  `section` varchar(255) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `quantity` int(11) NOT NULL,
  `borrowed_date` date NOT NULL DEFAULT current_timestamp(),
  `expected_return` date DEFAULT current_timestamp(),
  `returned_date` date DEFAULT current_timestamp(),
  `status` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `psy_lab_borrow`
--

INSERT INTO `psy_lab_borrow` (`id`, `laboratory`, `borrower_name`, `student_id`, `section`, `item_name`, `quantity`, `borrowed_date`, `expected_return`, `returned_date`, `status`) VALUES
(6, 'Psychology Lab', 'John Doe', 'S12345667', 'HE 01', 'Tongs', 1, '2026-08-11', '2026-08-14', '2026-08-11', 'Returned');

-- --------------------------------------------------------

--
-- Table structure for table `psy_lab_damage`
--

CREATE TABLE `psy_lab_damage` (
  `id` int(11) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `laboratory` varchar(100) NOT NULL,
  `issue` varchar(11) NOT NULL,
  `reported_by` varchar(100) NOT NULL,
  `date_reported` date NOT NULL DEFAULT current_timestamp(),
  `status` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `psy_lab_damage`
--

INSERT INTO `psy_lab_damage` (`id`, `item_name`, `laboratory`, `issue`, `reported_by`, `date_reported`, `status`) VALUES
(1, 'asdfasd', 'Psychology Laboratory', 'asdfasd', 'asdfasdf', '2026-08-03', 'Damaged');

-- --------------------------------------------------------

--
-- Table structure for table `psy_lab_inventory`
--

CREATE TABLE `psy_lab_inventory` (
  `id` int(11) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `category` varchar(100) NOT NULL,
  `laboratory` varchar(100) NOT NULL,
  `total_item` int(11) NOT NULL,
  `available_item` varchar(11) NOT NULL,
  `status` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `psy_lab_inventory`
--

INSERT INTO `psy_lab_inventory` (`id`, `item_name`, `category`, `laboratory`, `total_item`, `available_item`, `status`) VALUES
(6, 'dawd', '12321', 'Psychology Laboratory', 12, '12', 'Under Maintenance'),
(7, '2gssdfgsdf', 'egsdfgsfg', 'Psychology Laboratory', 45354, '54354453', 'Under Maintenance');

-- --------------------------------------------------------

--
-- Table structure for table `psy_lab_monitoring`
--

CREATE TABLE `psy_lab_monitoring` (
  `id` int(11) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `laboratory` varchar(100) NOT NULL,
  `equipment_condition` varchar(100) NOT NULL,
  `last_checked` date NOT NULL DEFAULT current_timestamp(),
  `checked_by` varchar(100) NOT NULL,
  `remarks` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `question_lab_borrow`
--

CREATE TABLE `question_lab_borrow` (
  `id` int(11) NOT NULL,
  `laboratory` varchar(100) NOT NULL,
  `borrower_name` varchar(100) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `student_id` varchar(255) NOT NULL,
  `section` varchar(255) NOT NULL,
  `quantity` int(11) NOT NULL,
  `borrowed_date` date NOT NULL DEFAULT current_timestamp(),
  `expected_return` date DEFAULT current_timestamp(),
  `returned_date` date DEFAULT current_timestamp(),
  `status` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `question_lab_borrow`
--

INSERT INTO `question_lab_borrow` (`id`, `laboratory`, `borrower_name`, `item_name`, `student_id`, `section`, `quantity`, `borrowed_date`, `expected_return`, `returned_date`, `status`) VALUES
(1, 'Question Document Lab', 'asdfadsf', 'asdfads', 'sdfasd', 'asdfafds12', 23, '2026-08-11', '2026-08-14', '2026-08-11', 'Returned');

-- --------------------------------------------------------

--
-- Table structure for table `question_lab_damage`
--

CREATE TABLE `question_lab_damage` (
  `id` int(11) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `laboratory` varchar(100) NOT NULL,
  `issue` varchar(100) NOT NULL,
  `reported_by` varchar(100) NOT NULL,
  `date_reported` date NOT NULL DEFAULT current_timestamp(),
  `status` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `question_lab_damage`
--

INSERT INTO `question_lab_damage` (`id`, `item_name`, `laboratory`, `issue`, `reported_by`, `date_reported`, `status`) VALUES
(1, 'fasdf', 'Question Document Laboratory', 'ssfds', 'sdfs', '2026-08-03', 'Damaged'),
(2, 'sdafads', 'Question Document Laboratory', 'aswdfasdf', 'asdfasdf', '2026-08-03', 'Working');

-- --------------------------------------------------------

--
-- Table structure for table `question_lab_inventory`
--

CREATE TABLE `question_lab_inventory` (
  `id` int(11) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `category` varchar(100) NOT NULL,
  `laboratory` varchar(100) NOT NULL,
  `total_item` int(11) NOT NULL,
  `available_item` int(11) NOT NULL,
  `status` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `question_lab_inventory`
--

INSERT INTO `question_lab_inventory` (`id`, `item_name`, `category`, `laboratory`, `total_item`, `available_item`, `status`) VALUES
(1, 'dawd', 'ffadf', 'Questioned Documents Lab', 2321, 2343, 'Under Maintenance');

-- --------------------------------------------------------

--
-- Table structure for table `question_lab_monitoring`
--

CREATE TABLE `question_lab_monitoring` (
  `id` int(11) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `laboratory` varchar(100) NOT NULL,
  `equipment_condition` varchar(100) NOT NULL,
  `last_checked` date NOT NULL DEFAULT current_timestamp(),
  `checked_by` varchar(100) NOT NULL,
  `remarks` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `balistic_lab_borrow`
--
ALTER TABLE `balistic_lab_borrow`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `balistic_lab_damage`
--
ALTER TABLE `balistic_lab_damage`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `balistic_lab_inventory`
--
ALTER TABLE `balistic_lab_inventory`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `balistic_lab_monitoring`
--
ALTER TABLE `balistic_lab_monitoring`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `chemistry_lab_borrow`
--
ALTER TABLE `chemistry_lab_borrow`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `chemistry_lab_damage`
--
ALTER TABLE `chemistry_lab_damage`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `chemistry_lab_inventory`
--
ALTER TABLE `chemistry_lab_inventory`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `chemistry_lab_monitoring`
--
ALTER TABLE `chemistry_lab_monitoring`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `crime_lab_borrow`
--
ALTER TABLE `crime_lab_borrow`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `crime_lab_damage`
--
ALTER TABLE `crime_lab_damage`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `crime_lab_inventory`
--
ALTER TABLE `crime_lab_inventory`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `crime_lab_monitoring`
--
ALTER TABLE `crime_lab_monitoring`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `defense_lab_borrow`
--
ALTER TABLE `defense_lab_borrow`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `defense_lab_damage`
--
ALTER TABLE `defense_lab_damage`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `defense_lab_inventory`
--
ALTER TABLE `defense_lab_inventory`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `defense_lab_monitoring`
--
ALTER TABLE `defense_lab_monitoring`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `fingerprint_lab_borrow`
--
ALTER TABLE `fingerprint_lab_borrow`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `fingerprint_lab_damage`
--
ALTER TABLE `fingerprint_lab_damage`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `fingerprint_lab_inventory`
--
ALTER TABLE `fingerprint_lab_inventory`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `fingerprint_lab_monitoring`
--
ALTER TABLE `fingerprint_lab_monitoring`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `he_lab_borrow`
--
ALTER TABLE `he_lab_borrow`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `he_lab_damage`
--
ALTER TABLE `he_lab_damage`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `he_lab_inventory`
--
ALTER TABLE `he_lab_inventory`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `he_lab_monitoring`
--
ALTER TABLE `he_lab_monitoring`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `it_lab1_borrow`
--
ALTER TABLE `it_lab1_borrow`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `it_lab1_damage`
--
ALTER TABLE `it_lab1_damage`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `it_lab1_inventory`
--
ALTER TABLE `it_lab1_inventory`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `it_lab1_monitoring`
--
ALTER TABLE `it_lab1_monitoring`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `it_lab2_borrow`
--
ALTER TABLE `it_lab2_borrow`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `it_lab2_damage`
--
ALTER TABLE `it_lab2_damage`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `it_lab2_inventory`
--
ALTER TABLE `it_lab2_inventory`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `it_lab2_monitoring`
--
ALTER TABLE `it_lab2_monitoring`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `it_lab3_borrow`
--
ALTER TABLE `it_lab3_borrow`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `it_lab3_damage`
--
ALTER TABLE `it_lab3_damage`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `it_lab3_inventory`
--
ALTER TABLE `it_lab3_inventory`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `it_lab3_monitoring`
--
ALTER TABLE `it_lab3_monitoring`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `laboratories`
--
ALTER TABLE `laboratories`
  ADD PRIMARY KEY (`lab_id`);

--
-- Indexes for table `laboratory_schedule`
--
ALTER TABLE `laboratory_schedule`
  ADD PRIMARY KEY (`schedule_id`),
  ADD KEY `lab_id` (`lab_id`);

--
-- Indexes for table `phys_lab_borrow`
--
ALTER TABLE `phys_lab_borrow`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `phys_lab_damage`
--
ALTER TABLE `phys_lab_damage`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `phys_lab_inventory`
--
ALTER TABLE `phys_lab_inventory`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `phys_lab_monitoring`
--
ALTER TABLE `phys_lab_monitoring`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `psy_lab_borrow`
--
ALTER TABLE `psy_lab_borrow`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `psy_lab_damage`
--
ALTER TABLE `psy_lab_damage`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `psy_lab_inventory`
--
ALTER TABLE `psy_lab_inventory`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `psy_lab_monitoring`
--
ALTER TABLE `psy_lab_monitoring`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `question_lab_borrow`
--
ALTER TABLE `question_lab_borrow`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `question_lab_damage`
--
ALTER TABLE `question_lab_damage`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `question_lab_inventory`
--
ALTER TABLE `question_lab_inventory`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `question_lab_monitoring`
--
ALTER TABLE `question_lab_monitoring`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `balistic_lab_borrow`
--
ALTER TABLE `balistic_lab_borrow`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `balistic_lab_damage`
--
ALTER TABLE `balistic_lab_damage`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `balistic_lab_inventory`
--
ALTER TABLE `balistic_lab_inventory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `balistic_lab_monitoring`
--
ALTER TABLE `balistic_lab_monitoring`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `chemistry_lab_borrow`
--
ALTER TABLE `chemistry_lab_borrow`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `chemistry_lab_damage`
--
ALTER TABLE `chemistry_lab_damage`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `chemistry_lab_inventory`
--
ALTER TABLE `chemistry_lab_inventory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `chemistry_lab_monitoring`
--
ALTER TABLE `chemistry_lab_monitoring`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `crime_lab_borrow`
--
ALTER TABLE `crime_lab_borrow`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `crime_lab_damage`
--
ALTER TABLE `crime_lab_damage`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `crime_lab_inventory`
--
ALTER TABLE `crime_lab_inventory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `crime_lab_monitoring`
--
ALTER TABLE `crime_lab_monitoring`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `defense_lab_borrow`
--
ALTER TABLE `defense_lab_borrow`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `defense_lab_damage`
--
ALTER TABLE `defense_lab_damage`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `defense_lab_inventory`
--
ALTER TABLE `defense_lab_inventory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `defense_lab_monitoring`
--
ALTER TABLE `defense_lab_monitoring`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `fingerprint_lab_borrow`
--
ALTER TABLE `fingerprint_lab_borrow`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `fingerprint_lab_damage`
--
ALTER TABLE `fingerprint_lab_damage`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `fingerprint_lab_inventory`
--
ALTER TABLE `fingerprint_lab_inventory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `fingerprint_lab_monitoring`
--
ALTER TABLE `fingerprint_lab_monitoring`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `he_lab_borrow`
--
ALTER TABLE `he_lab_borrow`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `he_lab_damage`
--
ALTER TABLE `he_lab_damage`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `he_lab_inventory`
--
ALTER TABLE `he_lab_inventory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `he_lab_monitoring`
--
ALTER TABLE `he_lab_monitoring`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `it_lab1_borrow`
--
ALTER TABLE `it_lab1_borrow`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `it_lab1_damage`
--
ALTER TABLE `it_lab1_damage`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `it_lab1_inventory`
--
ALTER TABLE `it_lab1_inventory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `it_lab1_monitoring`
--
ALTER TABLE `it_lab1_monitoring`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `it_lab2_borrow`
--
ALTER TABLE `it_lab2_borrow`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `it_lab2_damage`
--
ALTER TABLE `it_lab2_damage`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `it_lab2_inventory`
--
ALTER TABLE `it_lab2_inventory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `it_lab2_monitoring`
--
ALTER TABLE `it_lab2_monitoring`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `it_lab3_borrow`
--
ALTER TABLE `it_lab3_borrow`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `it_lab3_damage`
--
ALTER TABLE `it_lab3_damage`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `it_lab3_inventory`
--
ALTER TABLE `it_lab3_inventory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `it_lab3_monitoring`
--
ALTER TABLE `it_lab3_monitoring`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `laboratories`
--
ALTER TABLE `laboratories`
  MODIFY `lab_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `laboratory_schedule`
--
ALTER TABLE `laboratory_schedule`
  MODIFY `schedule_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `phys_lab_borrow`
--
ALTER TABLE `phys_lab_borrow`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `phys_lab_damage`
--
ALTER TABLE `phys_lab_damage`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `phys_lab_inventory`
--
ALTER TABLE `phys_lab_inventory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `phys_lab_monitoring`
--
ALTER TABLE `phys_lab_monitoring`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `psy_lab_borrow`
--
ALTER TABLE `psy_lab_borrow`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `psy_lab_damage`
--
ALTER TABLE `psy_lab_damage`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `psy_lab_inventory`
--
ALTER TABLE `psy_lab_inventory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `psy_lab_monitoring`
--
ALTER TABLE `psy_lab_monitoring`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `question_lab_borrow`
--
ALTER TABLE `question_lab_borrow`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `question_lab_damage`
--
ALTER TABLE `question_lab_damage`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `question_lab_inventory`
--
ALTER TABLE `question_lab_inventory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `question_lab_monitoring`
--
ALTER TABLE `question_lab_monitoring`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `laboratory_schedule`
--
ALTER TABLE `laboratory_schedule`
  ADD CONSTRAINT `laboratory_schedule_ibfk_1` FOREIGN KEY (`lab_id`) REFERENCES `laboratories` (`lab_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

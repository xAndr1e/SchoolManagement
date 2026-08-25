-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 11, 2026 at 12:07 PM
-- Server version: 8.0.44
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sms`
--

-- --------------------------------------------------------

--
-- Table structure for table `cc_room`
--

CREATE TABLE `cc_room` (
  `id` int NOT NULL,
  `room_code` varchar(20) NOT NULL,
  `room_name` varchar(100) NOT NULL,
  `building` varchar(100) DEFAULT 'Main Building',
  `floor` enum('1st Floor','2nd Floor','3rd Floor','4th Floor') NOT NULL,
  `room_type` enum('Lecture Room','Computer Laboratory','Science Laboratory','Library','Office','AVR','Court Room','Other') NOT NULL,
  `capacity` int DEFAULT '40',
  `status` enum('Available','Maintenance','Unavailable') DEFAULT 'Available',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `cc_room`
--

INSERT INTO `cc_room` (`id`, `room_code`, `room_name`, `building`, `floor`, `room_type`, `capacity`, `status`, `created_at`) VALUES
(1, '101', 'Room 101', 'Main Building', '1st Floor', 'Lecture Room', 50, 'Available', '2026-08-07 16:03:33'),
(2, '102', 'Room 102', 'Main Building', '1st Floor', 'Lecture Room', 50, 'Available', '2026-08-07 16:03:33'),
(3, '103', 'Room 103', 'Main Building', '1st Floor', 'Lecture Room', 50, 'Available', '2026-08-07 16:03:33'),
(4, '104', 'Room 104', 'Main Building', '1st Floor', 'Lecture Room', 50, 'Available', '2026-08-07 16:03:33'),
(5, '105', 'Room 105', 'Main Building', '1st Floor', 'Lecture Room', 50, 'Available', '2026-08-07 16:03:33'),
(6, '106', 'Room 106', 'Main Building', '1st Floor', 'Lecture Room', 50, 'Available', '2026-08-07 16:03:33'),
(7, '107', 'Room 107', 'Main Building', '1st Floor', 'Lecture Room', 50, 'Available', '2026-08-07 16:03:33'),
(8, '108', 'Room 108', 'Main Building', '1st Floor', 'Lecture Room', 50, 'Available', '2026-08-07 16:03:33'),
(9, 'COMLAB1', 'Computer Laboratory 1', 'Main Building', '1st Floor', 'Computer Laboratory', 50, 'Available', '2026-08-07 16:03:33'),
(10, '202', 'Room 202', 'Main Building', '2nd Floor', 'Lecture Room', 50, 'Available', '2026-08-07 16:05:53'),
(11, '203', 'Room 203', 'Main Building', '2nd Floor', 'Lecture Room', 50, 'Available', '2026-08-07 16:05:53'),
(12, '204', 'Room 204', 'Main Building', '2nd Floor', 'Lecture Room', 50, 'Available', '2026-08-07 16:05:53'),
(13, '205', 'Room 205', 'Main Building', '2nd Floor', 'Lecture Room', 50, 'Available', '2026-08-07 16:05:53'),
(14, '206', 'Room 206', 'Main Building', '2nd Floor', 'Lecture Room', 50, 'Available', '2026-08-07 16:05:53'),
(15, '207', 'Room 207', 'Main Building', '2nd Floor', 'Lecture Room', 50, 'Available', '2026-08-07 16:05:53'),
(16, '208', 'Room 208', 'Main Building', '2nd Floor', 'Lecture Room', 50, 'Available', '2026-08-07 16:05:53'),
(17, '210', 'Room 210', 'Main Building', '2nd Floor', 'Lecture Room', 50, 'Available', '2026-08-07 16:05:53'),
(18, '211', 'Room 211', 'Main Building', '2nd Floor', 'Lecture Room', 50, 'Available', '2026-08-07 16:05:53'),
(19, '212', 'Room 212', 'Main Building', '2nd Floor', 'Lecture Room', 50, 'Available', '2026-08-07 16:05:53'),
(20, '213', 'Room 213', 'Main Building', '2nd Floor', 'Lecture Room', 50, 'Available', '2026-08-07 16:05:53'),
(21, '214', 'Room 214', 'Main Building', '2nd Floor', 'Lecture Room', 50, 'Available', '2026-08-07 16:05:53'),
(22, '215', 'Room 215', 'Main Building', '2nd Floor', 'Lecture Room', 50, 'Available', '2026-08-07 16:05:53'),
(23, '216', 'Room 216', 'Main Building', '2nd Floor', 'Lecture Room', 50, 'Available', '2026-08-07 16:05:53'),
(24, 'PSYCHLAB', 'Psychology Laboratory', 'Main Building', '2nd Floor', 'Science Laboratory', 50, 'Available', '2026-08-07 16:05:53'),
(25, 'ITLAB', 'IT Laboratory', 'Main Building', '2nd Floor', 'Computer Laboratory', 50, 'Available', '2026-08-07 16:05:53'),
(26, '302', 'Room 302', 'Main Building', '3rd Floor', 'Lecture Room', 50, 'Available', '2026-08-07 16:09:36'),
(27, '303', 'Room 303', 'Main Building', '3rd Floor', 'Lecture Room', 50, 'Available', '2026-08-07 16:09:36'),
(28, '304', 'Room 304', 'Main Building', '3rd Floor', 'Lecture Room', 50, 'Available', '2026-08-07 16:09:36'),
(29, '305', 'Room 305', 'Main Building', '3rd Floor', 'Lecture Room', 50, 'Available', '2026-08-07 16:09:36'),
(30, '306', 'Room 306', 'Main Building', '3rd Floor', 'Lecture Room', 50, 'Available', '2026-08-07 16:09:36'),
(31, '307', 'Room 307', 'Main Building', '3rd Floor', 'Lecture Room', 50, 'Available', '2026-08-07 16:09:36'),
(32, '308', 'Room 308', 'Main Building', '3rd Floor', 'Lecture Room', 50, 'Available', '2026-08-07 16:09:36'),
(33, '309', 'Room 309', 'Main Building', '3rd Floor', 'Lecture Room', 50, 'Available', '2026-08-07 16:09:36'),
(34, '310', 'Room 310', 'Main Building', '3rd Floor', 'Lecture Room', 50, 'Available', '2026-08-07 16:09:36'),
(35, '311', 'Room 311', 'Main Building', '3rd Floor', 'Lecture Room', 50, 'Available', '2026-08-07 16:09:36'),
(36, '312', 'Room 312', 'Main Building', '3rd Floor', 'Lecture Room', 50, 'Available', '2026-08-07 16:09:36'),
(37, '313', 'Room 313', 'Main Building', '3rd Floor', 'Lecture Room', 50, 'Available', '2026-08-07 16:09:36'),
(38, 'ETLAB1', 'Educational Technology Room 1', 'Main Building', '3rd Floor', 'Computer Laboratory', 50, 'Available', '2026-08-07 16:09:36'),
(39, 'ETLAB2', 'Educational Technology Room 2', 'Main Building', '3rd Floor', 'Computer Laboratory', 50, 'Available', '2026-08-07 16:09:36'),
(40, 'CHEMLAB', 'Chemistry Laboratory', 'Main Building', '3rd Floor', 'Science Laboratory', 50, 'Available', '2026-08-07 16:09:36'),
(41, '403', 'Room 403', 'Main Building', '4th Floor', 'Lecture Room', 50, 'Available', '2026-08-07 16:11:15'),
(42, '404', 'Room 404', 'Main Building', '4th Floor', 'Lecture Room', 50, 'Available', '2026-08-07 16:11:15'),
(43, '405', 'Room 405', 'Main Building', '4th Floor', 'Lecture Room', 50, 'Available', '2026-08-07 16:11:15'),
(44, '406', 'Room 406', 'Main Building', '4th Floor', 'Lecture Room', 50, 'Available', '2026-08-07 16:11:15'),
(45, '407', 'Room 407', 'Main Building', '4th Floor', 'Lecture Room', 50, 'Available', '2026-08-07 16:11:15'),
(46, '408', 'Room 408', 'Main Building', '4th Floor', 'Lecture Room', 50, 'Available', '2026-08-07 16:11:15'),
(47, '409', 'Room 409', 'Main Building', '4th Floor', 'Lecture Room', 50, 'Available', '2026-08-07 16:11:15'),
(48, '410', 'Room 410', 'Main Building', '4th Floor', 'Lecture Room', 50, 'Available', '2026-08-07 16:11:15'),
(49, '411', 'Room 411', 'Main Building', '4th Floor', 'Lecture Room', 50, 'Available', '2026-08-07 16:11:15'),
(50, '412', 'Room 412', 'Main Building', '4th Floor', 'Lecture Room', 50, 'Available', '2026-08-07 16:11:15'),
(51, '413', 'Room 413', 'Main Building', '4th Floor', 'Lecture Room', 50, 'Available', '2026-08-07 16:11:15'),
(52, 'PHYSICSLAB', 'Physics Laboratory', 'Main Building', '4th Floor', 'Science Laboratory', 50, 'Available', '2026-08-07 16:11:15'),
(53, 'FORENSICLAB', 'Forensics Laboratory', 'Main Building', '4th Floor', 'Science Laboratory', 50, 'Available', '2026-08-07 16:11:15'),
(54, 'QDLAB', 'Questioned Documents Laboratory', 'Main Building', '4th Floor', 'Science Laboratory', 50, 'Available', '2026-08-07 16:11:15'),
(55, 'COMLAB4', 'Computer Laboratory 4', 'Main Building', '4th Floor', 'Computer Laboratory', 50, 'Available', '2026-08-07 16:11:15');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cc_room`
--
ALTER TABLE `cc_room`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `room_code` (`room_code`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cc_room`
--
ALTER TABLE `cc_room`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

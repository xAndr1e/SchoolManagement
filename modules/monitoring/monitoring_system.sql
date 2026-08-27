-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 07, 2026 at 08:06 AM
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
-- Database: `monitoring_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `attendance_archive`
--

CREATE TABLE `attendance_archive` (
  `id` int(11) NOT NULL,
  `original_id` int(11) NOT NULL,
  `schedule_id` int(11) DEFAULT NULL,
  `faculty_name` varchar(100) DEFAULT NULL,
  `course_section` varchar(50) DEFAULT NULL,
  `subject_code` varchar(20) DEFAULT NULL,
  `room` varchar(50) DEFAULT NULL,
  `student_count` int(11) DEFAULT 0,
  `attendance_date` date DEFAULT NULL,
  `check_time` datetime DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL,
  `is_online` tinyint(1) DEFAULT NULL,
  `meeting_link` text DEFAULT NULL,
  `meeting_screenshot` varchar(255) DEFAULT NULL,
  `face_to_face_image` varchar(255) DEFAULT NULL,
  `verified_by` varchar(100) DEFAULT NULL,
  `verification_method` varchar(50) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `archived_by` varchar(100) DEFAULT NULL,
  `archived_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `attendance_records`
--

CREATE TABLE `attendance_records` (
  `id` int(11) NOT NULL,
  `schedule_id` int(11) DEFAULT NULL,
  `faculty_name` varchar(100) NOT NULL,
  `course_section` varchar(50) NOT NULL,
  `subject_code` varchar(20) NOT NULL,
  `room` varchar(50) NOT NULL,
  `student_count` int(11) DEFAULT 0,
  `attendance_date` date NOT NULL,
  `check_time` datetime NOT NULL,
  `status` enum('present','absent','late','excused','online') NOT NULL,
  `is_online` tinyint(1) DEFAULT 0,
  `meeting_link` text DEFAULT NULL,
  `meeting_screenshot` varchar(255) DEFAULT NULL,
  `face_to_face_image` varchar(255) DEFAULT NULL,
  `verified_by` varchar(100) NOT NULL,
  `verification_method` enum('physical_check','online_monitoring','room_visit') NOT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendance_records`
--

INSERT INTO `attendance_records` (`id`, `schedule_id`, `faculty_name`, `course_section`, `subject_code`, `room`, `student_count`, `attendance_date`, `check_time`, `status`, `is_online`, `meeting_link`, `meeting_screenshot`, `face_to_face_image`, `verified_by`, `verification_method`, `remarks`, `created_at`) VALUES
(1, 11, 'Dr. Maria Santos', 'BSIT-3B', 'IT302', 'Room 103', 0, '2026-07-27', '2026-07-28 06:19:51', 'present', 0, NULL, NULL, NULL, 'Administrator', 'physical_check', NULL, '2026-07-27 22:19:51'),
(2, 23, 'Dr. Maria Santos', 'BSIT-3A', 'IT301', 'Room 101', 0, '2026-08-04', '2026-08-04 18:39:00', 'present', 0, '', NULL, NULL, 'Administrator', 'room_visit', '', '2026-08-04 10:39:00'),
(3, 24, 'Prof. Juan Reyes', 'BSCS-2B', 'CS201', 'Room 202', 0, '2026-08-04', '2026-08-04 22:19:37', 'online', 1, 'http://192.168.100.10:8080/dashboard', 'uploads/meeting_screenshots/screenshot_20260804_221937_11d06d304194dde9.jpg', NULL, 'Administrator', 'room_visit', '', '2026-08-04 14:19:37'),
(4, 25, 'Dr. Ana Cruz', 'BSIT-2A', 'IT202', 'Lab 1', 0, '2026-08-04', '2026-08-04 22:27:24', 'online', 1, 'http://192.168.100.10:8080/dashboard', 'uploads/meeting_screenshots/screenshot_20260804_222724_271b9e1f5d5d0d3b.jpg', NULL, 'Administrator', 'room_visit', '', '2026-08-04 14:27:24'),
(5, 14, 'Dr. Maria Santos', 'BSIT-3A', 'IT301', 'Room 101', 20, '2026-08-05', '2026-08-05 09:04:25', 'online', 1, 'http://192.168.100.10:8080/dashboard', 'uploads/meeting_screenshots/screenshot_20260805_090425_0450fd38b5b44e4e.jpg', NULL, 'Administrator', 'room_visit', '', '2026-08-05 01:04:25');

-- --------------------------------------------------------

--
-- Table structure for table `facilities`
--

CREATE TABLE `facilities` (
  `id` int(11) NOT NULL,
  `room_number` varchar(50) NOT NULL,
  `equipment_type` enum('chair','switch','light','aircon','projector','computer','other') NOT NULL,
  `equipment_name` varchar(100) NOT NULL,
  `quantity` int(11) DEFAULT 1,
  `functional_quantity` int(11) DEFAULT 0,
  `damaged_quantity` int(11) DEFAULT 0,
  `status` enum('complete','incomplete','needs_repair') DEFAULT 'complete',
  `monitored_by` varchar(100) DEFAULT NULL,
  `last_checked` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `facility_reports`
--

CREATE TABLE `facility_reports` (
  `id` int(11) NOT NULL,
  `facility_id` int(11) DEFAULT NULL,
  `room_number` varchar(50) NOT NULL,
  `broken_equipment` varchar(100) NOT NULL,
  `equipment_type` enum('chair','switch','light','aircon','projector','computer','other') NOT NULL,
  `quantity_damaged` int(11) DEFAULT 1,
  `description` text DEFAULT NULL,
  `reported_by` varchar(100) NOT NULL,
  `report_date` date NOT NULL,
  `status` enum('reported','in_progress','resolved','replaced') DEFAULT 'reported',
  `resolved_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `facility_reports`
--

INSERT INTO `facility_reports` (`id`, `facility_id`, `room_number`, `broken_equipment`, `equipment_type`, `quantity_damaged`, `description`, `reported_by`, `report_date`, `status`, `resolved_date`, `created_at`) VALUES
(1, NULL, '101', 'arm', 'chair', 1, 'armcahir broken', 'Administrator', '2026-07-28', 'reported', NULL, '2026-07-27 22:25:35');

-- --------------------------------------------------------

--
-- Table structure for table `schedules`
--

CREATE TABLE `schedules` (
  `id` int(11) NOT NULL,
  `schedule_date` date NOT NULL,
  `schedule_time` time NOT NULL,
  `course_section` varchar(50) NOT NULL,
  `day` varchar(20) NOT NULL,
  `faculty_name` varchar(100) NOT NULL,
  `room` varchar(50) NOT NULL,
  `subject_code` varchar(20) NOT NULL,
  `status` enum('active','cancelled','rescheduled') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `schedules`
--

INSERT INTO `schedules` (`id`, `schedule_date`, `schedule_time`, `course_section`, `day`, `faculty_name`, `room`, `subject_code`, `status`, `created_at`) VALUES
(7, '2026-07-27', '08:00:00', 'BSIT-3A', 'Monday', 'Dr. Maria Santos', 'Room 101', 'IT301', 'active', '2026-07-26 13:54:46'),
(8, '2026-07-27', '10:00:00', 'BSCS-2B', 'Monday', 'Prof. Juan Reyes', 'Room 202', 'CS201', 'active', '2026-07-26 13:54:46'),
(9, '2026-07-27', '13:00:00', 'BSIT-2A', 'Monday', 'Dr. Ana Cruz', 'Lab 1', 'IT202', 'active', '2026-07-26 13:54:46'),
(10, '2026-07-27', '15:00:00', 'BSCS-3A', 'Monday', 'Prof. Pedro Santos', 'Room 305', 'CS301', 'active', '2026-07-26 13:54:46'),
(11, '2026-07-28', '09:00:00', 'BSIT-3B', 'Tuesday', 'Dr. Maria Santos', 'Room 103', 'IT302', 'active', '2026-07-26 13:54:46'),
(12, '2026-07-28', '11:00:00', 'BSCS-2A', 'Tuesday', 'Prof. Juan Reyes', 'Room 201', 'CS202', 'active', '2026-07-26 13:54:46'),
(13, '2026-07-28', '14:00:00', 'BSIT-1A', 'Tuesday', 'Dr. Ana Cruz', 'Room 102', 'IT101', 'active', '2026-07-26 13:54:46'),
(14, '2026-07-29', '08:00:00', 'BSIT-3A', 'Wednesday', 'Dr. Maria Santos', 'Room 101', 'IT301', 'active', '2026-07-26 13:54:46'),
(15, '2026-07-29', '10:30:00', 'BSCS-2B', 'Wednesday', 'Prof. Pedro Santos', 'Lab 1', 'CS203', 'active', '2026-07-26 13:54:46'),
(16, '2026-07-29', '13:00:00', 'BSIT-2B', 'Wednesday', 'Prof. Juan Reyes', 'Room 204', 'IT201', 'active', '2026-07-26 13:54:46'),
(17, '2026-07-30', '09:00:00', 'BSIT-3B', 'Thursday', 'Dr. Ana Cruz', 'Room 103', 'IT303', 'active', '2026-07-26 13:54:46'),
(18, '2026-07-30', '11:00:00', 'BSCS-3A', 'Thursday', 'Prof. Maria Santos', 'Room 305', 'CS302', 'active', '2026-07-26 13:54:46'),
(19, '2026-07-30', '14:00:00', 'BSIT-1B', 'Thursday', 'Dr. Juan Reyes', 'Room 102', 'IT102', 'active', '2026-07-26 13:54:46'),
(20, '2026-07-31', '08:00:00', 'BSIT-2A', 'Friday', 'Prof. Pedro Santos', 'Room 201', 'IT202', 'active', '2026-07-26 13:54:46'),
(21, '2026-07-31', '10:00:00', 'BSCS-2A', 'Friday', 'Dr. Ana Cruz', 'Room 202', 'CS204', 'active', '2026-07-26 13:54:46'),
(22, '2026-07-31', '13:00:00', 'BSIT-3A', 'Friday', 'Prof. Maria Santos', 'Room 101', 'IT304', 'active', '2026-07-26 13:54:46'),
(23, '2026-08-04', '08:00:00', 'BSIT-3A', 'Tuesday', 'Dr. Maria Santos', 'Room 101', 'IT301', 'active', '2026-08-04 10:31:59'),
(24, '2026-08-04', '09:30:00', 'BSCS-2B', 'Tuesday', 'Prof. Juan Reyes', 'Room 202', 'CS201', 'active', '2026-08-04 10:31:59'),
(25, '2026-08-04', '11:00:00', 'BSIT-2A', 'Tuesday', 'Dr. Ana Cruz', 'Lab 1', 'IT202', 'active', '2026-08-04 10:31:59'),
(26, '2026-08-04', '13:00:00', 'BSCS-3A', 'Tuesday', 'Prof. Pedro Santos', 'Room 305', 'CS301', 'active', '2026-08-04 10:31:59'),
(27, '2026-08-04', '14:30:00', 'BSIT-3B', 'Tuesday', 'Dr. Maria Santos', 'Room 103', 'IT302', 'active', '2026-08-04 10:31:59'),
(28, '2026-08-04', '16:00:00', 'BSCS-2A', 'Tuesday', 'Prof. Juan Reyes', 'Room 201', 'CS202', 'active', '2026-08-04 10:31:59');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `fullname` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `role` enum('admin','monitor','viewer') DEFAULT 'monitor',
  `status` enum('active','inactive') DEFAULT 'active',
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `fullname`, `email`, `role`, `status`, `last_login`, `created_at`) VALUES
(1, 'admin', '0192023a7bbd73250516f069df18b500', 'Administrator', 'admin@system.com', 'admin', 'active', '2026-08-07 14:01:53', '2026-07-26 13:32:35'),
(2, 'monitor', 'a808370b7b147e3533d54538bbcf13a9', 'Monitor User', 'monitor@system.com', 'monitor', 'active', NULL, '2026-07-26 13:32:35'),
(3, 'viewer', '49e5e739ea41d635246cd9cd21af17c4', 'Viewer User', 'viewer@system.com', 'viewer', 'active', NULL, '2026-07-26 13:32:35');

-- --------------------------------------------------------

--
-- Table structure for table `visitors`
--

CREATE TABLE `visitors` (
  `id` int(11) NOT NULL,
  `visitor_name` varchar(100) NOT NULL,
  `contact_number` varchar(20) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `home_address` text DEFAULT NULL,
  `id_type` enum('school_id','government_id','company_id','other') DEFAULT 'government_id',
  `id_number` varchar(50) DEFAULT NULL,
  `vehicle_type` enum('car','motorcycle','bicycle','none') DEFAULT 'none',
  `vehicle_plate` varchar(20) DEFAULT NULL,
  `id_attachment` varchar(255) DEFAULT NULL,
  `id_approved` tinyint(1) DEFAULT 0,
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `additional_notes` text DEFAULT NULL,
  `purpose_of_visit` text NOT NULL,
  `person_to_visit` varchar(100) DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `time_in` datetime NOT NULL,
  `time_out` datetime DEFAULT NULL,
  `visit_duration` varchar(20) DEFAULT NULL,
  `status` enum('inside','left','pending_approval','approved') DEFAULT 'pending_approval',
  `monitored_by` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `qr_code` varchar(100) DEFAULT NULL,
  `privacy_consent` tinyint(1) DEFAULT 0,
  `registration_ip` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `visitors`
--

INSERT INTO `visitors` (`id`, `visitor_name`, `contact_number`, `email`, `home_address`, `id_type`, `id_number`, `vehicle_type`, `vehicle_plate`, `id_attachment`, `id_approved`, `approved_by`, `approved_at`, `additional_notes`, `purpose_of_visit`, `person_to_visit`, `department`, `time_in`, `time_out`, `visit_duration`, `status`, `monitored_by`, `created_at`, `qr_code`, `privacy_consent`, `registration_ip`) VALUES
(1, 'Kevin Durant', '08423895', NULL, NULL, 'company_id', '4323534', 'car', '43212', NULL, 0, NULL, NULL, NULL, 'Monitoring', 'Maam rose', 'colloege coor', '2026-07-28 06:26:47', '2026-07-30 21:53:21', '15:26:34', 'left', 'Administrator', '2026-07-27 22:26:47', NULL, 0, NULL),
(2, 'juan pablo corre', '09354521330', 'correjuanpablo05@gmail.com', 'dfghhdfgh', 'government_id', NULL, 'none', '6456fg', 'uploads/visitor_ids/ID_6a6b5f8327a6c_20260730.png', 1, 1, '2026-07-31 06:47:37', 'dwad', 'official_business', 'gdfgdfg', 'dwaqda', '2026-07-30 22:28:19', NULL, NULL, 'inside', NULL, '2026-07-30 14:28:19', 'VIS-6A6B5F8327FD6-20260730', 1, '::1'),
(3, 'Stephen curry ', '09354521229', 'stephencurrey@gmail.com', 'brgy citrus uc2 lower', 'government_id', NULL, 'none', '3737y', 'uploads/visitor_ids/ID_6a6bd2f00d0f5_20260731.jpg', 1, 1, '2026-07-31 06:47:35', 'yeuwuw', 'enrollment', 'Paying', 'Registrar', '2026-07-31 06:40:48', NULL, NULL, 'inside', NULL, '2026-07-30 22:40:48', 'VIS-6A6BD2F00DA69-20260731', 1, '192.168.100.2'),
(4, 'Kevin durant', '09354521444', 'kevinduran@gmail.com', 'los Angeles ', 'government_id', NULL, 'none', 'hj78', 'uploads/visitor_ids/ID_6a6bd4c7c4089_20260731.jpg', 1, 1, '2026-07-31 06:48:59', 'hwjwjw', 'meeting', 'visit', 'Registrar', '2026-07-31 06:48:39', NULL, NULL, 'inside', NULL, '2026-07-30 22:48:39', 'VIS-6A6BD4C7C45A0-20260731', 1, '192.168.100.2'),
(5, 'Lebron James ', '09354521445', 'lebronjames@gmail.com', 'marikina', 'government_id', NULL, 'none', '7j88', 'uploads/visitor_ids/ID_6a6bd64b5dff9_20260731.jpg', 1, 1, '2026-07-31 06:55:25', 'hewjwj', 'enrollment', 'visit', 'Registrar', '2026-07-31 06:55:07', NULL, NULL, 'inside', NULL, '2026-07-30 22:55:07', 'VIS-6A6BD64B5E615-20260731', 1, '192.168.100.2'),
(6, 'Ja Morant ', '09354521665', 'jamirant@gmail.com', 'memphis', 'government_id', NULL, 'none', '6jrj7', 'uploads/visitor_ids/ID_6a6bda8b25a3c_20260731.jpg', 1, 1, '2026-07-31 07:13:35', 'hehw pi', 'official_business', 'visit', 'Registrar', '2026-07-31 07:13:15', NULL, NULL, 'inside', NULL, '2026-07-30 23:13:15', 'VIS-6A6BDA8B260B9-20260731', 1, '192.168.100.2'),
(7, 'Jason Tatum', '09354521222', 'jasontatum@gmail.com', 'Boston ', 'government_id', NULL, 'none', 'h6736', 'uploads/visitor_ids/ID_6a6bdccb7cd5b_20260731.jpg', 1, 1, '2026-07-31 07:23:07', 'hwhwhw', 'enrollment', 'Paying', 'Registrar', '2026-07-31 07:22:51', NULL, NULL, 'inside', NULL, '2026-07-30 23:22:51', 'VIS-6A6BDCCB7D31E-20260731', 1, '192.168.100.2'),
(8, 'Michael Jordan', '09354521223', 'michaeljordan@gmail.com', 'chicago', 'government_id', NULL, 'none', '6y78', 'uploads/visitor_ids/ID_6a6bddb35dbb3_20260731.jpg', 1, 1, '2026-07-31 07:27:05', '', 'official_business', 'Paying', 'Registrar', '2026-07-31 07:26:43', NULL, NULL, 'inside', NULL, '2026-07-30 23:26:43', 'VIS-6A6BDDB35E1A8-20260731', 1, '192.168.100.2'),
(9, 'Luka Doncic', '09354521111', 'lukadoncic@gmail.com', 'los angeles', 'government_id', NULL, 'none', 'hs789', 'uploads/visitor_ids/ID_6a6bdf5297c4f_20260731.jpg', 1, 1, '2026-07-31 07:33:56', '', 'interview', 'Paying', 'Registrar', '2026-07-31 07:33:38', NULL, NULL, 'inside', NULL, '2026-07-30 23:33:38', 'VIS-6A6BDF5298324-20260731', 1, '192.168.100.2'),
(14, 'Bronny James', '09354521555', 'bronnyjames@gmail.com', 'los angeles', 'government_id', NULL, 'none', '3737y', 'uploads/visitor_ids/ID_6a6be83606d9a_20260731.jpg', 1, 1, '2026-07-31 08:11:45', '3yy2h', 'interview', 'Paying', 'Registrar', '2026-07-31 08:11:34', NULL, NULL, 'inside', NULL, '2026-07-31 00:11:34', 'VIS-6A6BE836073CC-20260731', 1, '192.168.100.2'),
(16, 'Draymond Green', '09354521000', 'draymondgreen@gmail.com', 'California ', 'government_id', NULL, 'none', 'g67h', 'uploads/visitor_ids/ID_6a6bea2fa85af_20260731.jpg', 1, 1, '2026-07-31 08:20:13', 'wbhwjw', 'enrollment', 'Paying', 'Registrar', '2026-07-31 08:19:59', NULL, NULL, 'inside', NULL, '2026-07-31 00:19:59', 'VIS-6A6BEA2FA8FA7-20260731', 1, '192.168.100.2'),
(17, 'Mike Tyson', '09354521999', 'miketyson@gmail.com', 'new york', 'government_id', NULL, 'none', '3737y', 'uploads/visitor_ids/ID_6a6bebaa7090e_20260731.jpg', 1, 1, '2026-07-31 08:26:33', 'hwhwh', 'delivery', 'Paying', 'Registrar', '2026-07-31 08:26:18', NULL, NULL, 'inside', NULL, '2026-07-31 00:26:18', 'VIS-6A6BEBAA70F2D-20260731', 1, '192.168.100.2'),
(18, 'Kobe Bryant ', '09354521888', 'kobebryant@gmail.com', 'los Angeles ', 'government_id', NULL, 'none', 'h78d', 'uploads/visitor_ids/ID_6a6bed916dac9_20260731.jpg', 1, 1, '2026-07-31 08:34:40', 'hsjwjw', 'interview', 'Paying', 'Registrar', '2026-07-31 08:34:25', NULL, NULL, 'inside', NULL, '2026-07-31 00:34:25', 'VIS-6A6BED916E0D8-20260731', 1, '192.168.100.2'),
(19, 'Lionel Messi ', '09354521362', 'lionelmessi@gmail.com', 'Argentina ', 'government_id', NULL, 'none', '6547', 'uploads/visitor_ids/ID_6a706c06e71a4_20260803.jpg', 1, 1, '2026-08-03 18:23:22', '62y2uwuw', 'delivery', 'Paying', 'Registrar', '2026-08-03 18:23:02', NULL, NULL, 'inside', NULL, '2026-08-03 10:23:02', 'VIS-6A706C06E7A3E-20260803', 1, '192.168.100.2'),
(20, 'Lionel Messi I-9', '09354521362', 'lionelmessi@gmail.com', 'Argentina ', 'government_id', NULL, 'none', '6547', 'uploads/visitor_ids/ID_6a7073ffc9f19_20260803.jpg', 1, 1, '2026-08-03 18:57:15', '62y2uwuw', 'delivery', 'Paying', 'Registrar', '2026-08-03 18:57:03', NULL, NULL, 'inside', NULL, '2026-08-03 10:57:03', 'VIS-6A7073FFCA5CC-20260803', 1, '192.168.100.2'),
(21, 'monkey d Luffy ', '09354524444', 'monkeydluff@gmail.com', 'east blue', 'government_id', NULL, 'none', 'h6748', 'uploads/visitor_ids/ID_6a757292ba948_20260807.jpg', 1, 1, '2026-08-07 13:53:17', 'yeheh', 'meeting', 'Paying', 'Registrar', '2026-08-07 13:52:18', NULL, NULL, 'inside', NULL, '2026-08-07 05:52:18', 'VIS-6A757292BAFEB-20260807', 1, '192.168.100.2');

-- --------------------------------------------------------

--
-- Table structure for table `visitor_audit_logs`
--

CREATE TABLE `visitor_audit_logs` (
  `id` int(11) NOT NULL,
  `visitor_id` int(11) DEFAULT NULL,
  `action` varchar(50) DEFAULT NULL,
  `performed_by` int(11) DEFAULT NULL,
  `details` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `visitor_audit_logs`
--

INSERT INTO `visitor_audit_logs` (`id`, `visitor_id`, `action`, `performed_by`, `details`, `created_at`) VALUES
(1, 1, 'checkout', NULL, 'Visitor checked out', '2026-07-30 21:53:21'),
(2, 2, 'registration', NULL, 'Visitor registered via QR code', '2026-07-30 22:28:19'),
(3, 3, 'registration', NULL, 'Visitor registered via QR code', '2026-07-31 06:40:48'),
(4, 3, 'approve_id', NULL, 'ID approved by admin ID: 1', '2026-07-31 06:47:35'),
(5, 2, 'approve_id', NULL, 'ID approved by admin ID: 1', '2026-07-31 06:47:37'),
(6, 4, 'registration', NULL, 'Visitor registered via QR code', '2026-07-31 06:48:39'),
(7, 4, 'approve_id', NULL, 'ID approved by admin ID: 1', '2026-07-31 06:48:59'),
(8, 5, 'registration', NULL, 'Visitor registered via QR code', '2026-07-31 06:55:07'),
(9, 5, 'approve_id', NULL, 'ID approved by admin ID: 1', '2026-07-31 06:55:25'),
(10, 6, 'registration', NULL, 'Visitor registered via QR code', '2026-07-31 07:13:15'),
(11, 6, 'approve_id', NULL, 'ID approved by admin ID: 1', '2026-07-31 07:13:35'),
(12, 7, 'registration', NULL, 'Visitor registered via QR code', '2026-07-31 07:22:51'),
(13, 7, 'approve_id', NULL, 'ID approved by admin ID: 1', '2026-07-31 07:23:07'),
(14, 8, 'registration', NULL, 'Visitor registered via QR code', '2026-07-31 07:26:43'),
(15, 8, 'approve_id', NULL, 'ID approved by admin ID: 1', '2026-07-31 07:27:05'),
(16, 9, 'registration', NULL, 'Visitor registered via QR code', '2026-07-31 07:33:38'),
(17, 9, 'approve_id', NULL, 'ID approved by admin ID: 1', '2026-07-31 07:33:56'),
(26, 14, 'registration', NULL, 'Visitor registered via QR code', '2026-07-31 08:11:34'),
(27, 14, 'approve_id', NULL, 'ID approved by admin ID: 1', '2026-07-31 08:11:45'),
(30, 16, 'registration', NULL, 'Visitor registered via QR code', '2026-07-31 08:19:59'),
(31, 16, 'approve_id', NULL, 'ID approved by admin ID: 1', '2026-07-31 08:20:13'),
(32, 17, 'registration', NULL, 'Visitor registered via QR code', '2026-07-31 08:26:18'),
(33, 17, 'approve_id', NULL, 'ID approved by admin ID: 1', '2026-07-31 08:26:33'),
(34, 18, 'registration', NULL, 'Visitor registered via QR code', '2026-07-31 08:34:25'),
(35, 18, 'approve_id', NULL, 'ID approved by admin ID: 1', '2026-07-31 08:34:40'),
(36, 19, 'registration', NULL, 'Visitor registered via QR code', '2026-08-03 18:23:02'),
(37, 19, 'approve_id', NULL, 'ID approved by admin ID: 1', '2026-08-03 18:23:22'),
(38, 20, 'registration', NULL, 'Visitor registered via QR code', '2026-08-03 18:57:03'),
(39, 20, 'approve_id', NULL, 'ID approved by admin ID: 1', '2026-08-03 18:57:15'),
(40, 21, 'registration', NULL, 'Visitor registered via QR code', '2026-08-07 13:52:18'),
(41, 21, 'approve_id', NULL, 'ID approved by admin ID: 1', '2026-08-07 13:53:17');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `attendance_archive`
--
ALTER TABLE `attendance_archive`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `attendance_records`
--
ALTER TABLE `attendance_records`
  ADD PRIMARY KEY (`id`),
  ADD KEY `schedule_id` (`schedule_id`);

--
-- Indexes for table `facilities`
--
ALTER TABLE `facilities`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `facility_reports`
--
ALTER TABLE `facility_reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `facility_id` (`facility_id`);

--
-- Indexes for table `schedules`
--
ALTER TABLE `schedules`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `visitors`
--
ALTER TABLE `visitors`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `qr_code` (`qr_code`);

--
-- Indexes for table `visitor_audit_logs`
--
ALTER TABLE `visitor_audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `visitor_id` (`visitor_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `attendance_archive`
--
ALTER TABLE `attendance_archive`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `attendance_records`
--
ALTER TABLE `attendance_records`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `facilities`
--
ALTER TABLE `facilities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `facility_reports`
--
ALTER TABLE `facility_reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `schedules`
--
ALTER TABLE `schedules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `visitors`
--
ALTER TABLE `visitors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `visitor_audit_logs`
--
ALTER TABLE `visitor_audit_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attendance_records`
--
ALTER TABLE `attendance_records`
  ADD CONSTRAINT `attendance_records_ibfk_1` FOREIGN KEY (`schedule_id`) REFERENCES `schedules` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `facility_reports`
--
ALTER TABLE `facility_reports`
  ADD CONSTRAINT `facility_reports_ibfk_1` FOREIGN KEY (`facility_id`) REFERENCES `facilities` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `visitor_audit_logs`
--
ALTER TABLE `visitor_audit_logs`
  ADD CONSTRAINT `visitor_audit_logs_ibfk_1` FOREIGN KEY (`visitor_id`) REFERENCES `visitors` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

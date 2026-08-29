-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 29, 2026 at 02:15 PM
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
-- Database: `sms`
--

-- --------------------------------------------------------

--
-- Table structure for table `cc_schedule`
--

CREATE TABLE `cc_schedule` (
  `id` int(11) NOT NULL,
  `faculty_load_id` int(11) NOT NULL,
  `room_id` int(11) NOT NULL,
  `schedule_type` enum('Class','Break Time') DEFAULT 'Class',
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `status` enum('Scheduled','Completed','Cancelled') DEFAULT 'Scheduled',
  `day_of_week` enum('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday') NOT NULL,
  `section_id` int(11) NOT NULL,
  `faculty_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `school_year_id` int(11) NOT NULL,
  `semester_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cc_schedule`
--
ALTER TABLE `cc_schedule`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_faculty_schedule` (`faculty_id`,`day_of_week`,`start_time`,`end_time`,`semester_id`,`school_year_id`),
  ADD UNIQUE KEY `uk_section_schedule` (`section_id`,`day_of_week`,`start_time`,`end_time`,`semester_id`,`school_year_id`),
  ADD UNIQUE KEY `uk_room_schedule` (`room_id`,`day_of_week`,`start_time`,`end_time`,`semester_id`,`school_year_id`),
  ADD KEY `fk_schedule_load` (`faculty_load_id`),
  ADD KEY `fk_schedule_subject` (`subject_id`),
  ADD KEY `fk_schedule_sy` (`school_year_id`),
  ADD KEY `fk_schedule_sem` (`semester_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cc_schedule`
--
ALTER TABLE `cc_schedule`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cc_schedule`
--
ALTER TABLE `cc_schedule`
  ADD CONSTRAINT `fk_schedule_faculty` FOREIGN KEY (`faculty_id`) REFERENCES `cc_faculty` (`id`),
  ADD CONSTRAINT `fk_schedule_load` FOREIGN KEY (`faculty_load_id`) REFERENCES `cc_faculty_load` (`id`),
  ADD CONSTRAINT `fk_schedule_room` FOREIGN KEY (`room_id`) REFERENCES `cc_room` (`id`),
  ADD CONSTRAINT `fk_schedule_section` FOREIGN KEY (`section_id`) REFERENCES `cc_sections` (`id`),
  ADD CONSTRAINT `fk_schedule_sem` FOREIGN KEY (`semester_id`) REFERENCES `rgr_semesters` (`id`),
  ADD CONSTRAINT `fk_schedule_subject` FOREIGN KEY (`subject_id`) REFERENCES `rgr_subjects` (`id`),
  ADD CONSTRAINT `fk_schedule_sy` FOREIGN KEY (`school_year_id`) REFERENCES `rgr_school_years` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

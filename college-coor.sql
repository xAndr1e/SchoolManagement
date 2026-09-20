CREATE TABLE `cc_certification_engagements` (
  `id` int NOT NULL AUTO_INCREMENT,
  `employee_id` int NOT NULL,
  `engagement_type` enum('Part-time','OJT/Training') NOT NULL,
  `title` varchar(150) NOT NULL,
  `organization` varchar(150) DEFAULT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` enum('Pending','Ongoing','For Review','Approved','Completed','Archived') NOT NULL DEFAULT 'Pending',
  `outcome` enum('Continue','Regularize','End Engagement','Not Applicable') NOT NULL DEFAULT 'Not Applicable',
  `approved_by` int DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `certificate_generated_at` datetime DEFAULT NULL,
  `archived_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_employee_id` (`employee_id`),
  KEY `idx_status` (`status`),
  CONSTRAINT `fk_cc_certification_engagements_employee` FOREIGN KEY (`employee_id`) REFERENCES `em_employees` (`employee_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci

CREATE TABLE `cc_event_templates` (
  `template_id` int NOT NULL AUTO_INCREMENT,
  `template_name` varchar(100) NOT NULL,
  `event_type` enum('Academic','Meeting','Seminar','Institutional Event','Cultural Event','Sports Event','Orientation','Other') NOT NULL,
  `default_title` varchar(255) NOT NULL,
  `default_description` text,
  `default_location` varchar(255) DEFAULT NULL,
  `default_target_audience` varchar(100) DEFAULT NULL,
  `default_status` enum('upcoming','ongoing','completed','cancelled') DEFAULT 'upcoming',
  `priority` enum('Normal','High','Urgent') DEFAULT 'Normal',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`template_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci

CREATE TABLE `cc_events` (
  `event_id` int NOT NULL AUTO_INCREMENT,
  `template_id` int DEFAULT NULL,
  `event_title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `event_type` enum('Academic','Meeting','Seminar','Institutional Event','Cultural Event','Sports Event','Orientation','Other') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `event_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `location` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `target_audience` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'All Students',
  `status` enum('upcoming','ongoing','completed','cancelled') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'upcoming',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`event_id`),
  KEY `fk_event_template` (`template_id`),
  CONSTRAINT `fk_event_template` FOREIGN KEY (`template_id`) REFERENCES `cc_event_templates` (`template_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci

CREATE TABLE `cc_exam_proctor` (
  `id` int NOT NULL AUTO_INCREMENT,
  `exam_schedule_id` int NOT NULL,
  `faculty_id` int NOT NULL,
  `role` enum('Lead Proctor','Proctor','Reliever') DEFAULT 'Proctor',
  `status` enum('Assigned','Confirmed','Completed','Cancelled') DEFAULT 'Assigned',
  `assigned_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_exam_proctor` (`exam_schedule_id`,`faculty_id`),
  KEY `idx_exam_schedule` (`exam_schedule_id`),
  KEY `idx_faculty` (`faculty_id`)
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci

CREATE TABLE `cc_exam_schedule` (
  `id` int NOT NULL AUTO_INCREMENT,
  `exam_id` int NOT NULL,
  `schedule_type` enum('Exam','Break Time') NOT NULL DEFAULT 'Exam',
  `subject_id` int DEFAULT NULL,
  `section_id` int DEFAULT NULL,
  `room_id` int DEFAULT NULL,
  `exam_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `status` enum('Scheduled','Ongoing','Completed','Cancelled') DEFAULT 'Scheduled',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_exam` (`exam_id`),
  KEY `idx_subject` (`subject_id`),
  KEY `idx_section` (`section_id`),
  KEY `idx_room` (`room_id`),
  KEY `idx_exam_date` (`exam_date`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci

CREATE TABLE `cc_exams` (
  `id` int NOT NULL AUTO_INCREMENT,
  `exam_name` varchar(100) NOT NULL,
  `exam_type` enum('Preliminary','Midterm','Final','Special') NOT NULL,
  `school_year_id` int NOT NULL,
  `semester_id` int NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` enum('Draft','Scheduled','Ongoing','Completed','Cancelled') DEFAULT 'Draft',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_exam_sy` (`school_year_id`),
  KEY `idx_exam_sem` (`semester_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci

CREATE TABLE `cc_faculty` (
  `id` int NOT NULL AUTO_INCREMENT,
  `employee_id` int DEFAULT NULL,
  `faculty_code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `first_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `middle_name` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `last_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `department` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `max_load` int NOT NULL DEFAULT '15',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_cc_faculty_employee` (`employee_id`),
  CONSTRAINT `fk_cc_faculty_employee` FOREIGN KEY (`employee_id`) REFERENCES `em_employees` (`employee_id`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci

CREATE TABLE `cc_faculty_load` (
  `id` int NOT NULL AUTO_INCREMENT,
  `faculty_id` int NOT NULL,
  `section_id` int NOT NULL,
  `subject_id` int NOT NULL,
  `school_year_id` int DEFAULT NULL,
  `semester_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_section_subject` (`section_id`,`subject_id`),
  UNIQUE KEY `uq_faculty_load` (`section_id`,`subject_id`,`school_year_id`,`semester_id`),
  KEY `idx_faculty` (`faculty_id`),
  KEY `idx_section` (`section_id`),
  KEY `idx_subject` (`subject_id`),
  KEY `idx_school_year` (`school_year_id`),
  KEY `idx_semester` (`semester_id`),
  CONSTRAINT `fk_faculty` FOREIGN KEY (`faculty_id`) REFERENCES `cc_faculty` (`id`),
  CONSTRAINT `fk_faculty_load_school_year` FOREIGN KEY (`school_year_id`) REFERENCES `rgr_school_years` (`id`),
  CONSTRAINT `fk_faculty_load_semester` FOREIGN KEY (`semester_id`) REFERENCES `rgr_semesters` (`id`),
  CONSTRAINT `fk_section` FOREIGN KEY (`section_id`) REFERENCES `cc_sections` (`id`),
  CONSTRAINT `fk_subject` FOREIGN KEY (`subject_id`) REFERENCES `rgr_subjects` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=97 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci

CREATE TABLE `cc_faculty_load_summary` (
  `id` int NOT NULL AUTO_INCREMENT,
  `faculty_id` int NOT NULL,
  `school_year_id` int NOT NULL,
  `semester_id` int NOT NULL,
  `total_units` int NOT NULL DEFAULT '0',
  `max_load` int NOT NULL,
  `load_status` enum('Underloaded','Normal Load','Overloaded') COLLATE utf8mb4_general_ci NOT NULL,
  `computed_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_faculty_period` (`faculty_id`,`school_year_id`,`semester_id`),
  KEY `idx_faculty` (`faculty_id`),
  KEY `idx_school_year` (`school_year_id`),
  KEY `idx_semester` (`semester_id`),
  CONSTRAINT `fk_summary_faculty` FOREIGN KEY (`faculty_id`) REFERENCES `cc_faculty` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_summary_school_year` FOREIGN KEY (`school_year_id`) REFERENCES `rgr_school_years` (`id`),
  CONSTRAINT `fk_summary_semester` FOREIGN KEY (`semester_id`) REFERENCES `rgr_semesters` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci

CREATE TABLE `cc_room` (
  `id` int NOT NULL AUTO_INCREMENT,
  `room_code` varchar(20) NOT NULL,
  `room_name` varchar(100) NOT NULL,
  `building` varchar(100) DEFAULT 'Main Building',
  `floor` enum('1st Floor','2nd Floor','3rd Floor','4th Floor') NOT NULL,
  `room_type` enum('Lecture Room','Computer Laboratory','Science Laboratory','Library','Office','AVR','Court Room','Other') NOT NULL,
  `capacity` int DEFAULT '40',
  `status` enum('Available','Maintenance','Unavailable') DEFAULT 'Available',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `room_code` (`room_code`)
) ENGINE=InnoDB AUTO_INCREMENT=56 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci

CREATE TABLE `cc_schedule` (
  `id` int NOT NULL AUTO_INCREMENT,
  `faculty_load_id` int DEFAULT NULL,
  `room_id` int DEFAULT NULL,
  `schedule_type` enum('Class','Break Time') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'Class',
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `status` enum('Scheduled','Completed','Cancelled') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'Scheduled',
  `day_of_week` enum('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `section_id` int DEFAULT NULL,
  `faculty_id` int NOT NULL,
  `subject_id` int DEFAULT NULL,
  `school_year_id` int NOT NULL,
  `semester_id` int NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_faculty_schedule` (`faculty_id`,`day_of_week`,`start_time`,`end_time`,`semester_id`,`school_year_id`),
  UNIQUE KEY `uk_section_schedule` (`section_id`,`day_of_week`,`start_time`,`end_time`,`semester_id`,`school_year_id`),
  UNIQUE KEY `uk_room_schedule` (`room_id`,`day_of_week`,`start_time`,`end_time`,`semester_id`,`school_year_id`),
  KEY `fk_schedule_load` (`faculty_load_id`),
  KEY `fk_schedule_subject` (`subject_id`),
  KEY `fk_schedule_sy` (`school_year_id`),
  KEY `fk_schedule_sem` (`semester_id`),
  CONSTRAINT `fk_schedule_faculty` FOREIGN KEY (`faculty_id`) REFERENCES `cc_faculty` (`id`),
  CONSTRAINT `fk_schedule_load` FOREIGN KEY (`faculty_load_id`) REFERENCES `cc_faculty_load` (`id`),
  CONSTRAINT `fk_schedule_room` FOREIGN KEY (`room_id`) REFERENCES `cc_room` (`id`),
  CONSTRAINT `fk_schedule_section` FOREIGN KEY (`section_id`) REFERENCES `cc_sections` (`id`),
  CONSTRAINT `fk_schedule_sem` FOREIGN KEY (`semester_id`) REFERENCES `rgr_semesters` (`id`),
  CONSTRAINT `fk_schedule_subject` FOREIGN KEY (`subject_id`) REFERENCES `rgr_subjects` (`id`),
  CONSTRAINT `fk_schedule_sy` FOREIGN KEY (`school_year_id`) REFERENCES `rgr_school_years` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci

CREATE TABLE `cc_section_faculty` (
  `id` int NOT NULL AUTO_INCREMENT,
  `section_id` int NOT NULL,
  `faculty_id` int NOT NULL,
  `role` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `school_year_id` int NOT NULL,
  `semester_id` int NOT NULL,
  `status` enum('Active','Completed','Reassigned','Cancelled') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Active',
  `assigned_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ended_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_section_faculty_role` (`section_id`,`faculty_id`,`role`,`school_year_id`,`semester_id`),
  KEY `fk_sectionfaculty_sy` (`school_year_id`),
  KEY `fk_sectionfaculty_sem` (`semester_id`),
  KEY `fk_sectionfaculty_faculty` (`faculty_id`),
  CONSTRAINT `fk_sectionfaculty_faculty` FOREIGN KEY (`faculty_id`) REFERENCES `cc_faculty` (`id`),
  CONSTRAINT `fk_sectionfaculty_section` FOREIGN KEY (`section_id`) REFERENCES `cc_sections` (`id`),
  CONSTRAINT `fk_sectionfaculty_sem` FOREIGN KEY (`semester_id`) REFERENCES `rgr_semesters` (`id`),
  CONSTRAINT `fk_sectionfaculty_sy` FOREIGN KEY (`school_year_id`) REFERENCES `rgr_school_years` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci

CREATE TABLE `cc_sections` (
  `id` int NOT NULL AUTO_INCREMENT,
  `section_code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `grade_level` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `program_id` int DEFAULT NULL,
  `school_year_id` int DEFAULT NULL,
  `semester_id` int DEFAULT NULL,
  `adviser_id` int DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_section` (`section_code`,`school_year_id`,`semester_id`),
  KEY `fk_section_program` (`program_id`),
  CONSTRAINT `fk_section_program` FOREIGN KEY (`program_id`) REFERENCES `rgr_courses` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
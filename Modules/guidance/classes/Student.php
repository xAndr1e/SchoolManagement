<?php
include_once __DIR__ . '/../../../database/db.php';

Class Student {
    private $conn;
    private $student_number;
    private $first_name;
    private $middle_name;
    private $last_name;
    private $gender;
    private $birth_date;
    private $course;
    private $year_level;
    private $section;
    private $email;
    private $phone;
    private $address;
    private $academic_status;
    private $graduated_at;
    private $created_at;
    private $updated_at;

    public function __construct($pdo = null) {
        if ($pdo instanceof PDO) {
            $this->conn = $pdo;
        } else {
            $database = new Database();
            $this->conn = $database->getConnection();
        }
    }

    // Get all students
    public function getStudents() {
        $sql = "SELECT 
                    student_number,
                    first_name,
                    middle_name,
                    last_name,
                    gender,
                    birth_date,
                    course,
                    year_level,
                    section,
                    email,
                    phone,
                    academic_status
                FROM rgr_students
                ORDER BY student_number";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get single student by student number
    public function getStudentById($student_number) {
        $sql = "SELECT * FROM rgr_students WHERE student_number = :student_number LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':student_number', $student_number);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Add new student
    public function addStudent($data) {
        $sql = "INSERT INTO rgr_students (
                    student_number,
                    first_name,
                    middle_name,
                    last_name,
                    gender,
                    birth_date,
                    course,
                    year_level,
                    section,
                    email,
                    phone,
                    address,
                    academic_status,
                    created_at
                ) VALUES (
                    :student_number,
                    :first_name,
                    :middle_name,
                    :last_name,
                    :gender,
                    :birth_date,
                    :course,
                    :year_level,
                    :section,
                    :email,
                    :phone,
                    :address,
                    :academic_status,
                    NOW()
                )";

        $stmt = $this->conn->prepare($sql);
        return $stmt->execute($data);
    }

    // Update student
    public function updateStudent($student_number, $data) {
        $sql = "UPDATE rgr_students SET
                    first_name = :first_name,
                    middle_name = :middle_name,
                    last_name = :last_name,
                    gender = :gender,
                    birth_date = :birth_date,
                    course = :course,
                    year_level = :year_level,
                    section = :section,
                    email = :email,
                    phone = :phone,
                    address = :address,
                    academic_status = :academic_status,
                    updated_at = NOW()
                WHERE student_number = :student_number";

        $stmt = $this->conn->prepare($sql);
        $data['student_number'] = $student_number;
        return $stmt->execute($data);
    }

    // Delete student
    public function deleteStudent($student_number) {
        $sql = "DELETE FROM rgr_students WHERE student_number = :student_number";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':student_number', $student_number);
        return $stmt->execute();
    }

    // Get logged-in student number (if using session)
    public function getStudentNumber() {
        return $_SESSION['student_number'] ?? null;
    }

    // Get logged-in student name
    public function getStudentName() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $studentNumber = $_SESSION['student_number'] ?? null;

        if ($studentNumber) {
            $sql = "SELECT first_name, last_name 
                    FROM rgr_students 
                    WHERE student_number = :student_number 
                    LIMIT 1";

            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':student_number', $studentNumber);
            $stmt->execute();
            $student = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($student) {
                return htmlspecialchars($student['first_name'] . ' ' . $student['last_name']);
            }
        }
        return 'Unknown Student';
    }

    // Get student academic status
    public function getStudentStatus() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $studentNumber = $_SESSION['student_number'] ?? null;

        if ($studentNumber) {
            $sql = "SELECT academic_status 
                    FROM rgr_students 
                    WHERE student_number = :student_number 
                    LIMIT 1";

            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':student_number', $studentNumber);
            $stmt->execute();
            $student = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($student) {
                return htmlspecialchars($student['academic_status']);
            }
        }
        return 'Unknown Status';
    }
}
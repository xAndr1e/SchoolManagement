<?php
// DUMMY VERSION: walang DB. Ibalik ang original kapag ready na ang database.
// 50 na dummy employees. Ang department at position ay magkatugma (hal. School Nurse ay nasa Clinic).

class Employee {
    private $employees = [];

    // Same signature as the original: accepts a PDO (or null), pero hindi ginagamit.
    public function __construct($pdo = null) {
        $male   = ['Roberto', 'Miguel', 'Paolo', 'Carlo', 'Mark', 'Rafael', 'Gabriel', 'Daniel', 'Joshua', 'Angelo', 'Christian', 'Kenneth', 'Ramon', 'Eduardo', 'Ferdinand'];
        $female = ['Carla', 'Teresa', 'Angela', 'Jasmine', 'Katrina', 'Bianca', 'Patricia', 'Kristine', 'Nicole', 'Sofia', 'Rhea', 'Maricel', 'Josephine', 'Cecilia', 'Liza'];
        $last   = ['Villanueva', 'Bautista', 'Aquino', 'Navarro', 'Fernandez', 'Santos', 'Reyes', 'Cruz', 'Garcia', 'Mendoza', 'Ramos', 'Lopez', 'Torres', 'Castillo', 'Flores', 'Rivera', 'Gonzales', 'Domingo', 'Pascual', 'Manalo', 'Salazar', 'Tolentino', 'Magno', 'Soriano', 'Dela Cruz'];
        $middle = ['Cruz', 'Lopez', 'Ramos', 'Tan', 'Santos', 'Reyes', 'Aquino', 'Bautista', 'Garcia', 'Mercado', 'Del Rosario', 'Valdez'];

        // Unang 6 ay department heads (Regular lahat)
        $heads = [
            ['Human Resources', 'HR Manager'],
            ['Registrar',       'University Registrar'],
            ['Clinic',          'School Physician'],
            ['Finance',         'Finance Manager'],
            ['IT Department',   'IT Manager'],
            ['Faculty',         'Department Head'],
        ];
        // Ang iba ay umiikot dito
        $others = [
            ['Human Resources', 'HR Assistant'],
            ['Registrar',       'Registrar Staff'],
            ['Clinic',          'School Nurse'],
            ['Finance',         'Accountant'],
            ['IT Department',   'System Administrator'],
            ['Faculty',         'Instructor'],
            ['Human Resources', 'Recruitment Officer'],
            ['Registrar',       'Records Clerk'],
            ['Clinic',          'Clinic Aide'],
            ['Finance',         'Cashier'],
            ['IT Department',   'Software Developer'],
            ['Faculty',         'Assistant Professor'],
            ['IT Department',   'IT Support Specialist'],
            ['Faculty',         'Professor'],
        ];
        $statuses = ['Regular', 'Regular', 'Probationary', 'Contractual', 'Regular', 'Part-time'];

        for ($i = 0; $i < 50; $i++) {
            $isFemale = ($i % 2 === 1);
            $pool  = $isFemale ? $female : $male;
            $first = $pool[intdiv($i, 2) % 15];

            if ($i < 6) {
                [$dept, $pos] = $heads[$i];
                $status = 'Regular';
            } else {
                [$dept, $pos] = $others[($i - 6) % 14];
                $status = $statuses[$i % 6];
            }

            $this->employees[] = [
                'employee_id'     => $i + 1,
                'first_name'      => $first,
                'middle_name'     => ($i % 8 === 7) ? null : $middle[$i % 12], // may ilang walang middle name
                'last_name'       => $last[($i * 7 + 3) % 25],
                'department_name' => $dept,
                'position_name'   => $pos,
                'status'          => $status,
            ];
        }
    }

    public function getEmployees() {
        return $this->employees; // naka-order na by employee_id
    }

    public function getEmployeeId() {
        return $_SESSION['employee_id'] ?? null;
    }

    private function findCurrent() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $id = $_SESSION['employee_id'] ?? null;
        if ($id) {
            foreach ($this->employees as $e) {
                if ((int) $e['employee_id'] === (int) $id) {
                    return $e;
                }
            }
        }
        return null;
    }

    public function getEmployeeName() {
        $e = $this->findCurrent();
        return $e ? $e['first_name'] . ' ' . $e['last_name'] : 'Unknown User';
    }

    public function getEmployeePosition() {
        $e = $this->findCurrent();
        return ($e && $e['position_name']) ? $e['position_name'] : 'Unknown Position';
    }
}
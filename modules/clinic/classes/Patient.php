<?php
// DUMMY VERSION: walang DB. Ibalik ang original kapag ready na ang database.
// 50 na dummy patients. Ang create() ay sine-save sa $_SESSION lang (nawawala kapag nag-expire ang session).

if (!class_exists('DummyStatement')) {
    // Ginagaya ang PDOStatement para gumana pa rin ang $stmt->fetch(), fetchAll(), rowCount(), at foreach.
    class DummyStatement implements IteratorAggregate {
        private $rows;
        private $pos = 0;
        public function __construct(array $rows) { $this->rows = array_values($rows); }
        public function fetch($mode = null) { return $this->rows[$this->pos++] ?? false; }
        public function fetchAll($mode = null) { return $this->rows; }
        public function rowCount() { return count($this->rows); }
        public function getIterator(): Iterator { return new ArrayIterator($this->rows); }
    }
}

class Patient {
    private $conn;

    public $patient_id;
    public $first_name;
    public $last_name;
    public $gender;
    public $date_of_birth;
    public $phone;
    public $email;
    public $address;

    // Tinatanggap pa rin ang $db para hindi masira ang mga tumatawag, pero hindi ginagamit.
    public function __construct($db = null) {
        $this->conn = $db;
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
        if (!isset($_SESSION['dummy_patients'])) {
            $_SESSION['dummy_patients'] = $this->buildSeed();
        }
    }

    private function buildSeed() {
        $male   = ['Juan', 'Jose', 'Miguel', 'Paolo', 'Carlo', 'Mark', 'John Paul', 'Rafael', 'Gabriel', 'Daniel', 'Joshua', 'Angelo', 'Christian', 'Kenneth', 'Ramon'];
        $female = ['Maria', 'Ana', 'Liza', 'Carla', 'Teresa', 'Angela', 'Jasmine', 'Katrina', 'Bianca', 'Patricia', 'Kristine', 'Mary Grace', 'Nicole', 'Sofia', 'Rhea'];
        $last   = ['Santos', 'Reyes', 'Cruz', 'Bautista', 'Garcia', 'Mendoza', 'Ramos', 'Aquino', 'Villanueva', 'Navarro', 'Fernandez', 'Lopez', 'Dela Cruz', 'Torres', 'Castillo', 'Flores', 'Rivera', 'Gonzales', 'Domingo', 'Pascual', 'Manalo', 'Salazar', 'Tolentino', 'Magno', 'Soriano'];
        $prefix = ['0917', '0918', '0920', '0927', '0939', '0945', '0956', '0966', '0977', '0995'];
        $domain = ['gmail.com', 'yahoo.com', 'outlook.com'];
        $addr   = [
            'Blk 5 Lot 12, Brgy. Muzon, San Jose del Monte, Bulacan',
            '23 Mabini St., Brgy. Poblacion, Meycauayan, Bulacan',
            '101 Rizal Ave., Brgy. Lawang Bato, Valenzuela City',
            'Blk 8 Lot 3, Brgy. Tungkong Mangga, San Jose del Monte, Bulacan',
            '45 Sampaguita St., Brgy. Bagbaguin, Caloocan City',
            '78 Mabuhay St., Brgy. Santa Rosa I, Marilao, Bulacan',
            '12 Katipunan Rd., Brgy. Loyola Heights, Quezon City',
            '9 Del Pilar St., Brgy. Sapang Palay, San Jose del Monte, Bulacan',
            '56 MacArthur Hwy., Brgy. Karuhatan, Valenzuela City',
            '34 Bonifacio St., Brgy. Sta. Maria, Bocaue, Bulacan',
            '67 Quirino Hwy., Brgy. Gaya-gaya, San Jose del Monte, Bulacan',
            '88 Commonwealth Ave., Brgy. Holy Spirit, Quezon City',
        ];

        $rows = [];
        for ($i = 0; $i < 50; $i++) {
            $isFemale = ($i % 2 === 0);
            $pool  = $isFemale ? $female : $male;
            $first = $pool[intdiv($i, 2) % 15];
            $lastN = $last[($i * 7 + 3) % 25];

            $year  = 1965 + (($i * 7) % 43);   // 1965-2007
            $month = 1 + (($i * 5) % 12);
            $day   = 1 + (($i * 11) % 28);

            $digits = ((1234567 + $i * 987651) % 9000000) + 1000000;

            $rows[] = [
                'patient_id'    => $i + 1,
                'first_name'    => $first,
                'last_name'     => $lastN,
                'gender'        => $isFemale ? 'Female' : 'Male',
                'date_of_birth' => sprintf('%04d-%02d-%02d', $year, $month, $day),
                'phone'         => $prefix[$i % 10] . $digits,
                'email'         => strtolower(str_replace(' ', '', $first)) . '.' . strtolower(str_replace(' ', '', $lastN)) . '@' . $domain[$i % 3],
                'address'       => $addr[$i % 12],
            ];
        }
        return $rows;
    }

    // Create patient (session lang)
    public function create() {
        $rows = $_SESSION['dummy_patients'];
        $maxId = $rows ? max(array_column($rows, 'patient_id')) : 0;
        $this->patient_id = $maxId + 1;

        $rows[] = [
            'patient_id'    => $this->patient_id,
            'first_name'    => $this->first_name,
            'last_name'     => $this->last_name,
            'gender'        => $this->gender,
            'date_of_birth' => $this->date_of_birth,
            'phone'         => $this->phone,
            'email'         => $this->email,
            'address'       => $this->address,
        ];
        $_SESSION['dummy_patients'] = $rows;
        return true;
    }

    // Read all patients (patient_id DESC)
    public function readAll() {
        $rows = $_SESSION['dummy_patients'];
        usort($rows, fn($a, $b) => $b['patient_id'] <=> $a['patient_id']);
        return new DummyStatement($rows);
    }
}
?>
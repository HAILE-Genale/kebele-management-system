<?php
// models/Person.php
require_once __DIR__ . '/../config/database.php';

class Person {
    private $conn;
    private $table_name = "persons";

    public $id;
    public $first_name;
    public $father_name;
    public $grandfather_name;
    public $sex;
    public $date_of_birth;
    public $place_of_birth;
    public $nationality;
    public $marital_status;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Create a new Person
    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                  (first_name, father_name, grandfather_name, sex, date_of_birth, place_of_birth, nationality, marital_status)
                  VALUES (:first_name, :father_name, :grandfather_name, :sex, :date_of_birth, :place_of_birth, :nationality, :marital_status)";

        $stmt = $this->conn->prepare($query);

        // Sanitize data
        $this->first_name = htmlspecialchars(strip_tags($this->first_name));
        $this->father_name = htmlspecialchars(strip_tags($this->father_name));
        $this->grandfather_name = htmlspecialchars(strip_tags($this->grandfather_name));
        
        $stmt->bindParam(":first_name", $this->first_name);
        $stmt->bindParam(":father_name", $this->father_name);
        $stmt->bindParam(":grandfather_name", $this->grandfather_name);
        $stmt->bindParam(":sex", $this->sex);
        $stmt->bindParam(":date_of_birth", $this->date_of_birth);
        $stmt->bindParam(":place_of_birth", $this->place_of_birth);
        $stmt->bindParam(":nationality", $this->nationality);
        $stmt->bindParam(":marital_status", $this->marital_status);

        if($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    // Read all persons
    public function readAll() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Read single person
    public function readOne() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row) {
            $this->first_name = $row['first_name'];
            $this->father_name = $row['father_name'];
            $this->grandfather_name = $row['grandfather_name'];
            $this->sex = $row['sex'];
            $this->date_of_birth = $row['date_of_birth'];
            // Fill rest if needed
            return true;
        }
        return false;
    }
}
?>

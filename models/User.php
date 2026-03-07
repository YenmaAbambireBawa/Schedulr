<?php
/**
 * User Model
 * Handles all user-related database operations
 */

require_once __DIR__ . '/../config/database.php';

class User {
    private $conn;
    private $table_name = "users";

    // User properties
    public $id;
    public $full_name;
    public $email;
    public $student_id;
    public $password;
    public $password_hash;
    public $role;
    public $remember_token;
    public $email_verified_at;
    public $created_at;
    public $updated_at;
    public $last_login;
    public $is_active;

    /**
     * Constructor
     */
    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Create new user
     * @return bool
     */
    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                SET
                    full_name = :full_name,
                    email = :email,
                    student_id = :student_id,
                    password_hash = :password_hash,
                    role = :role,
                    created_at = NOW(),
                    updated_at = NOW()";

        $stmt = $this->conn->prepare($query);

        // Sanitize inputs
        $this->full_name = htmlspecialchars(strip_tags($this->full_name));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->student_id = htmlspecialchars(strip_tags($this->student_id));
        $this->role = htmlspecialchars(strip_tags($this->role));

        // Hash password
        $this->password_hash = password_hash($this->password, PASSWORD_BCRYPT);

        // Bind values
        $stmt->bindParam(":full_name", $this->full_name);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":student_id", $this->student_id);
        $stmt->bindParam(":password_hash", $this->password_hash);
        $stmt->bindParam(":role", $this->role);

        try {
            if($stmt->execute()) {
                $this->id = $this->conn->lastInsertId();
                return true;
            }
            return false;
        } catch(PDOException $e) {
            error_log("Error creating user: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if email already exists
     * @return bool
     */
    public function emailExists() {
        $query = "SELECT id, full_name, email, student_id, password_hash, role, is_active, last_login
                FROM " . $this->table_name . "
                WHERE email = :email
                LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email", $this->email);
        $stmt->execute();

        $row = $stmt->fetch();

        if($row) {
            $this->id = $row['id'];
            $this->full_name = $row['full_name'];
            $this->email = $row['email'];
            $this->student_id = $row['student_id'];
            $this->password_hash = $row['password_hash'];
            $this->role = $row['role'];
            $this->is_active = $row['is_active'];
            $this->last_login = $row['last_login'];
            return true;
        }

        return false;
    }

    /**
     * Verify password
     * @param string $password
     * @return bool
     */
    public function verifyPassword($password) {
        return password_verify($password, $this->password_hash);
    }

    /**
     * Update last login timestamp
     * @return bool
     */
    public function updateLastLogin() {
        $query = "UPDATE " . $this->table_name . "
                SET last_login = NOW()
                WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);

        return $stmt->execute();
    }

    /**
     * Generate and save remember token
     * @return string
     */
    public function generateRememberToken() {
        $token = bin2hex(random_bytes(32));
        
        $query = "UPDATE " . $this->table_name . "
                SET remember_token = :token
                WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":token", $token);
        $stmt->bindParam(":id", $this->id);

        if($stmt->execute()) {
            return $token;
        }
        return null;
    }

    /**
     * Verify remember token
     * @param string $token
     * @return bool
     */
    public function verifyRememberToken($token) {
        $query = "SELECT id, full_name, email, role, is_active
                FROM " . $this->table_name . "
                WHERE remember_token = :token AND is_active = 1
                LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":token", $token);
        $stmt->execute();

        $row = $stmt->fetch();

        if($row) {
            $this->id = $row['id'];
            $this->full_name = $row['full_name'];
            $this->email = $row['email'];
            $this->role = $row['role'];
            $this->is_active = $row['is_active'];
            return true;
        }

        return false;
    }

    /**
     * Get user by ID
     * @return bool
     */
    public function getById() {
        $query = "SELECT * FROM " . $this->table_name . "
                WHERE id = :id AND is_active = 1
                LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        $stmt->execute();

        $row = $stmt->fetch();

        if($row) {
            $this->full_name = $row['full_name'];
            $this->email = $row['email'];
            $this->student_id = $row['student_id'];
            $this->role = $row['role'];
            $this->created_at = $row['created_at'];
            $this->last_login = $row['last_login'];
            return true;
        }

        return false;
    }

    /**
     * Validate password strength
     * @param string $password
     * @return array
     */
    public static function validatePasswordStrength($password) {
        $errors = [];

        if (strlen($password) < 8) {
            $errors[] = "Password must be at least 8 characters long";
        }

        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = "Password must contain at least one uppercase letter";
        }

        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = "Password must contain at least one lowercase letter";
        }

        if (!preg_match('/\d/', $password)) {
            $errors[] = "Password must contain at least one number";
        }

        if (!preg_match('/[!@#$%^&*()_+\-=\[\]{};:\'"\\|,.<>\/?]/', $password)) {
            $errors[] = "Password must contain at least one special character";
        }

        return $errors;
    }

    /**
     * Validate email format
     * @param string $email
     * @return bool
     */
    public static function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
}
?>

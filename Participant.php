<?php
require_once 'User.php';

class Participant extends User {
    private $fullName;
    private $interests;
    private $contactNumber;
    private $countryCode;

    public function __construct($pdo, $email, $password, $fullName, $interests, $contactNumber, $countryCode) {
        parent::__construct($pdo, $email, $password);
        $this->fullName = $fullName;
        $this->interests = $interests;
        $this->contactNumber = $contactNumber;
        $this->countryCode = $countryCode;
    }

    public function register() {
        $stmt = $this->pdo->prepare(
            "INSERT INTO participants (email, password, full_name, interests, contact_number, country_code) VALUES (?, ?, ?, ?, ?, ?)"
        );
        return $stmt->execute([
            $this->email,
            password_hash($this->password, PASSWORD_DEFAULT),
            $this->fullName,
            $this->interests,
            $this->contactNumber,
            $this->countryCode
        ]);
    }
}
?>

<?php
require_once 'User.php';

class Organizer extends User {
    private $organizationName;
    private $address;
    private $website;
    private $contactNumber;
    private $countryCode;

    public function __construct($pdo, $email, $password, $organizationName, $address, $website, $contactNumber, $countryCode) {
        parent::__construct($pdo, $email, $password);
        $this->organizationName = $organizationName;
        $this->address = $address;
        $this->website = $website;
        $this->contactNumber = $contactNumber;
        $this->countryCode = $countryCode;
    }

    public function register() {
        $stmt = $this->pdo->prepare(
            "INSERT INTO organizers (email, password, organization_name, address, website, contact_number, country_code) VALUES (?, ?, ?, ?, ?, ?, ?)"
        );
        return $stmt->execute([
            $this->email,
            password_hash($this->password, PASSWORD_DEFAULT),
            $this->organizationName,
            $this->address,
            $this->website,
            $this->contactNumber,
            $this->countryCode
        ]);
    }
}
?>

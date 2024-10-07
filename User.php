<?php
class User {
    protected $pdo;
    protected $email;
    protected $password;

    public function __construct($pdo, $email, $password) {
        $this->pdo = $pdo;
        $this->email = $email;
        $this->password = $password;
    }

    public function login($table) {
        $stmt = $this->pdo->prepare("SELECT * FROM $table WHERE email = ?");
        $stmt->execute([$this->email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($this->password, $user['password'])) {
            return $user;
        }
        return false;
    }
}
?>

<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once 'db_init.php';

// Establish the database connection
$pdo = db_connect();
if (!$pdo) {
    echo json_encode(["success" => false, "message" => "Database connection failed"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($data->action)) {
        if ($data->action === 'login') {
            $email = $data->email;
            $password = $data->password;

            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC); // Use associative array

            if ($user && password_verify($password, $user['password'])) {
                echo json_encode(["success" => true, "message" => "Login successful", "user" => $user]);
            } else {
                echo json_encode(["success" => false, "message" => "Invalid email or password"]);
            }
        } elseif ($data->action === 'register') {
            $email = $data->email;
            $password = password_hash($data->password, PASSWORD_DEFAULT);
            $userType = $data->userType;

            // Prepare the insert statement
            $stmt = $pdo->prepare("INSERT INTO users (email, password, user_type, full_name, organization_name, address, contact_number, country_code, interests, website) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

            // Conditional fields based on user type
            $fullName = $userType === 'participant' ? $data->fullName : null;
            $organizationName = $userType === 'organizer' ? $data->organizationName : null;
            $address = $userType === 'organizer' ? $data->address : null;
            $interests = $userType === 'participant' ? $data->interests : null;
            $website = $userType === 'organizer' ? $data->website : null;

            // Execute the statement
            if ($stmt->execute([$email, $password, $userType, $fullName, $organizationName, $address, $data->contactNumber, $data->countryCode, $interests, $website])) {
                echo json_encode(["success" => true, "message" => "Registration successful"]);
            } else {
                echo json_encode(["success" => false, "message" => "Registration failed"]);
            }
        } else {
            echo json_encode(["success" => false, "message" => "Invalid action"]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "No action specified"]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}
?>

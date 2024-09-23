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
        
            // Check for participant first
            $stmt = $pdo->prepare("SELECT * FROM participants WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
            if (!$user) {
                // If not found in participants, check organizers
                $stmt = $pdo->prepare("SELECT * FROM organizers WHERE email = ?");
                $stmt->execute([$email]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
            }
        
            if ($user && password_verify($password, $user['password'])) {
                echo json_encode(["success" => true, "message" => "Login successful", "user" => $user]);
            } else {
                echo json_encode(["success" => false, "message" => "Invalid email or password"]);
            }
        }
         elseif ($data->action === 'register') {
            $email = $data->email;
            $password = password_hash($data->password, PASSWORD_DEFAULT);
            $userType = $data->userType;
        
            if ($userType === 'participant') {
                $fullName = $data->fullName;
                $interests = $data->interests;
        
                $stmt = $pdo->prepare("INSERT INTO participants (email, password, full_name, interests, contact_number, country_code) VALUES (?, ?, ?, ?, ?, ?)");
                if ($stmt->execute([$email, $password, $fullName, $interests, $data->contactNumber, $data->countryCode])) {
                    echo json_encode(["success" => true, "message" => "Participant registration successful"]);
                } else {
                    echo json_encode(["success" => false, "message" => "Participant registration failed"]);
                }
            } elseif ($userType === 'organizer') {
                $organizationName = $data->organizationName;
                $address = $data->address;
                $website = $data->website;
        
                $stmt = $pdo->prepare("INSERT INTO organizers (email, password, organization_name, address, website, contact_number, country_code) VALUES (?, ?, ?, ?, ?, ?, ?)");
                if ($stmt->execute([$email, $password, $organizationName, $address, $website, $data->contactNumber, $data->countryCode])) {
                    echo json_encode(["success" => true, "message" => "Organizer registration successful"]);
                } else {
                    echo json_encode(["success" => false, "message" => "Organizer registration failed"]);
                }
            } else {
                echo json_encode(["success" => false, "message" => "Invalid user type"]);
            }
        }
         else {
            echo json_encode(["success" => false, "message" => "Invalid action"]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "No action specified"]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}
?>

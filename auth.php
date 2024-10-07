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
include_once 'Participant.php';
include_once 'Organizer.php';
include_once 'db_util.php';  // Include the utility functions

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

            // Try fetching from the participants table first
            $user = get_data('participants', 'email', $email);

            // If not found in participants, try organizers
            if (!$user) {
                $user = get_data('organizers', 'email', $email);
            }

            if ($user && password_verify($password, $user['password'])) {
                echo json_encode(["success" => true, "message" => "Login successful", "user" => $user]);
            } else {
                echo json_encode(["success" => false, "message" => "Invalid email or password"]);
            }
        }
        elseif ($data->action === 'register') {
            $email = $data->email;
            $password = password_hash($data->password, PASSWORD_DEFAULT);  // Hash the password
            $userType = $data->userType;
        
            // Check if the user already exists using utility function
            if ($userType === 'participant') {
                // Use get_data to check for existing participant
                $existingUser = get_data('participants', 'email', $email);
                if ($existingUser) {
                    echo json_encode(["success" => false, "message" => "Email already registed as a Participant"]);
                    exit;  // Exit after sending the response
                }
        
                // If not registered, proceed with registration
                $fullName = $data->fullName;
                $interests = $data->interests;
                $contactNumber = $data->contactNumber;
                $countryCode = $data->countryCode;
        
                // Insert into participants table
                $sql = "INSERT INTO participants (email, password, full_name, interests, contact_number, country_code) VALUES (?, ?, ?, ?, ?, ?)";
                $params = [$email, $password, $fullName, $interests, $contactNumber, $countryCode];
        
                if (db_execute($sql, $params)) {
                    echo json_encode(["success" => true, "message" => "Participant registration successful"]);
                } else {
                    echo json_encode(["success" => false, "message" => "Participant registration failed"]);
                }
            } elseif ($userType === 'organizer') {
                // Use get_data to check for existing organizer
                $existingUser = get_data('organizers', 'email', $email);
                if ($existingUser) {
                    echo json_encode(["success" => false, "message" => "Email already registed as a Organizer"]);
                    exit;  // Exit after sending the response
                }
        
                // If not registered, proceed with registration
                $organizationName = $data->organizationName;
                $address = $data->address;
                $website = $data->website;
                $contactNumber = $data->contactNumber;
                $countryCode = $data->countryCode;
        
                // Insert into organizers table
                $sql = "INSERT INTO organizers (email, password, organization_name, address, website, contact_number, country_code) VALUES (?, ?, ?, ?, ?, ?, ?)";
                $params = [$email, $password, $organizationName, $address, $website, $contactNumber, $countryCode];
        
                if (db_execute($sql, $params)) {
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

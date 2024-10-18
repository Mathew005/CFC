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
include_once 'db_util.php';  // Include the utility functions

function standardizeName($name) {
    // Trim the name and split it into words
    $words = explode(' ', trim($name));
    
    // Capitalize the first letter of each word and lowercase the rest
    $standardizedWords = array_map(function($word) {
        return ucfirst(strtolower($word));
    }, $words);
    
    // Join the words back into a single string
    return implode(' ', $standardizedWords);
}

// Debugging: log the raw JSON input
$data_raw = file_get_contents("php://input");
error_log("Raw Data: " . $data_raw);  // Log raw input data
$data = json_decode($data_raw);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($data->action)) {
        // Debugging: log the action received
        error_log("Action: " . $data->action);

        if ($data->action === 'login') {
            $email = $data->email;
            $password = $data->password;
        
            // Debugging: log login attempt
            error_log("Login Attempt: Email = " . $email);
        
            // Try fetching from the participants table
            $user = get_data('Participants', 'PEmail', $email);
        
            // Log what was returned
            error_log("Fetched from Participants: " . json_encode($user));
        
            // If not found in participants, try fetching from the organizers table
            if (!$user) {
                error_log("Email not found in Participants: " . $email);
                $user = get_data('Organizers', 'OEmail', $email);
                
                // Log what was returned from organizers
                error_log("Fetched from Organizers: " . json_encode($user));
            }
        
            // Check if the user exists and verify the password
            if ($user) {
                $isParticipant = isset($user['PPassword']);
                $isOrganizer = isset($user['OPassword']);
        
                if (($isParticipant && $password === $user['PPassword']) || 
                    ($isOrganizer && $password === $user['OPassword'])) {
                    $response = [
                        "success" => true,
                        "message" => "Login successful",
                        "user" => [
                            "id" => $isParticipant ? $user['PID'] : $user['OID'],
                            "email" => $isParticipant ? $user['PEmail'] : $user['OEmail'],
                            "name" => $isParticipant ? $user['PName'] : $user['OName'],
                            "userType" => $isParticipant ? 'participant' : 'organizer'
                        ]
                    ];
                    echo json_encode($response);
                } else {
                    echo json_encode(["success" => false, "message" => "Invalid email or password"]);
                }
            } else {
                echo json_encode(["success" => false, "message" => "Invalid email or password"]);
            }
        }
        elseif ($data->action === 'register') {
            $email = $data->email;
            $password = $data->password;  // Store the password as plain text
            $userType = $data->userType;

            // Debugging: log registration attempt
            error_log("Registration Attempt: Email = " . $email . ", UserType = " . $userType);

            // Check if the email already exists in either table
            $existingParticipant = get_data('participants', 'PEmail', $email);
            $existingOrganizer = get_data('organizers', 'OEmail', $email);
            
            if ($existingParticipant || $existingOrganizer) {
                echo json_encode(["success" => false, "message" => "Email already registered as " . ($existingParticipant ? "a Participant" : "an Organizer")]);
                exit;
            }

            // Registration logic for participant
            if ($userType === 'participant') {
                // Participant-specific data
                $fullName = standardizeName($data->fullName);
                $contactNumber = $data->contactNumber;
                $countryCode = $data->countryCode;

                // Debugging: log SQL for participant registration
                $sql = "INSERT INTO participants (PEmail, PPassword, PName, PCode, PPhone) VALUES (?, ?, ?, ?, ?)";
                error_log("SQL: " . $sql);  // Log the SQL query
                error_log("Params: " . json_encode([$email, $password, $fullName, $countryCode, $contactNumber]));  // Log the parameters

                // Insert into participants table
                $newUserId = db_insert($sql, [$email, $password, $fullName, $countryCode, $contactNumber]);

                if ($newUserId) {
                    error_log("Db Insertion Success");
                    echo json_encode([ 
                        "success" => true, 
                        "message" => "Participant registration successful", 
                        "user" => [ 
                            "id" => $newUserId, 
                            "email" => $email, 
                            "name" => $fullName, 
                            "userType" => 'participant' 
                        ] 
                    ]);
                } else {
                    error_log("Db Insertion Failed");
                    echo json_encode(["success" => false, "message" => "Participant registration failed"]);
                }
            } elseif ($userType === 'organizer') {
                // Organizer-specific data
                $organizationName = $data->organizationName;
                $address = $data->address;
                $website = $data->website;
                $contactNumber = $data->contactNumber;
                $countryCode = $data->countryCode;

                // Debugging: log SQL for organizer registration
                $sql = "INSERT INTO organizers (OEmail, OPassword, OName, OAddress, OWebsite, OCode ,OPhone) VALUES (?, ?, ?, ?, ?, ?, ?)";
                error_log("SQL: " . $sql);  // Log the SQL query
                error_log("Params: " . json_encode([$email, $password, $organizationName, $address, $website, $countryCode, $contactNumber]));  // Log the parameters

                // Insert into organizers table
                $newUserId = db_insert($sql, [$email, $password, $organizationName, $address, $website, $countryCode, $contactNumber]);

                if ($newUserId) {
                    echo json_encode([ 
                        "success" => true, 
                        "message" => "Organizer registration successful", 
                        "user" => [ 
                            "id" => $newUserId, 
                            "email" => $email, 
                            "name" => $organizationName, 
                            "userType" => 'organizer' 
                        ] 
                    ]);
                } else {
                    echo json_encode(["success" => false, "message" => "Organizer registration failed"]);
                }
            } else {
                echo json_encode(["success" => false, "message" => "Invalid user type"]);
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

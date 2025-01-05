<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set HTTP headers for the response
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Include required files for database initialization and utilities
include_once 'db_init.php';
include_once 'db_util.php';

// Define the image host
$img_host = DB_PROTOCOL . DB_HOST . "/" . DB_NAME . "/";

// Log the start of the script
error_log("Starting script...");

// Log the value of the image host
error_log("Image host: " . $img_host);

// Define the subcategory-to-category mapping
$category_mapping = [
    "technology" => ["coding", "web-design", "ai", "cybersec", "blockchain", "data", "cloud", "iot", "ar/vr"],
    "culture" => ["art", "music", "dance", "books", "langs", "film", "theater", "food traditions", "heritage"],
    "commerce" => ["marketing", "finance", "startups", "e-commerce", "supply chain", "social biz"],
    "science" => ["physics", "chem", "bio", "space", "enviro science", "psych", "genetics", "geology"],
    "sports" => ["soccer", "basketball", "tennis", "swim", "yoga", "running", "martial arts", "extreme sports"],
    "lifestyle" => ["fashion", "cooking", "travel", "photo", "fitness", "home decor", "gardening", "mindfulness"],
    "health" => ["mental health", "nutrition", "holistic", "meditation", "wellness", "fitness trends"],
    "environment" => ["renewables", "conservation", "urban garden", "eco living", "waste reduction", "sustainable fashion"],
    "education" => ["workshops", "lifelong learning", "stem", "languages", "online courses", "skills"],
    "social" => ["service", "activism", "nonprofit", "social justice", "volunteering", "civic duty"],
    "gaming" => ["video games", "board games", "game dev", "vr", "streaming", "game design"],
    "food" => ["culinary", "wine", "food trucks", "global cuisine", "food fests", "sustainable eating"],
    "travel" => ["adventure", "cultural exchange", "travel photo", "eco tourism", "road trips", "backpacking"],
    "crafts" => ["handmade", "diy", "upcycling", "markets", "craft fairs", "sewing"],
    "film" => ["documentaries", "filmmaking", "animation", "storytelling", "film fests", "podcasting"],
    "history" => ["heritage", "reenactments", "genealogy", "local history", "preservation", "archaeology"],
    "themes" => ["innovation", "cultures", "future work", "digital nomads", "diversity", "nature", "art-tech fusion", "tradition", "mindfulness", "local talent"]
];

// Function to map subcategory to main category
function get_category_from_subcategory($subcategory, $category_mapping) {
    foreach ($category_mapping as $category => $subcategories) {
        if (in_array(strtolower($subcategory), $subcategories)) {
            return $category;
        }
    }
    return 'unknown'; // Default if no match is found
}

try {
    // SQL queries to fetch events and programs
    $events_sql = "SELECT 
                        EID as id,
                        EName as title,
                        CONCAT(:api_host, EImage) as image,
                        DATE_FORMAT(EStartDate, '%Y-%m-%d') as date,
                        DATE_FORMAT(EStartDate, '%l:%i %p') as time,
                        ELocation as location,
                        OName as institute,
                        EType as category
                   FROM Events
                   JOIN Organizers ON Events.OID = Organizers.OID
                   WHERE Published = 1 AND Cancelled = 0";

    $programs_sql = "SELECT 
                         PID as id,
                         Programs.EID as eventId,
                         PName as title,
                         CONCAT(:api_host, PImage) as image,
                         DATE_FORMAT(PStartDate, '%Y-%m-%d') as date,
                         DATE_FORMAT(PTime, '%l:%i %p') as time,
                         PLocation as venue,
                         PType as subcategory,
                         (SELECT EName FROM Events WHERE Events.EID = Programs.EID) as event
                    FROM Programs
                    WHERE Open = 1 AND (SELECT Published FROM Events WHERE Events.EID = Programs.EID) = 1 AND (SELECT Cancelled FROM Events WHERE Events.EID = Programs.EID) = 0";

    // Set parameters for queries
    $params = [':api_host' => $img_host];
    error_log("Parameters set for queries: " . json_encode($params));

    // Fetch data from the database
    $events = db_query($events_sql, $params);
    $programs = db_query($programs_sql, $params);

    // Log the result of queries
    error_log("Fetched events: " . json_encode($events));
    error_log("Fetched programs: " . json_encode($programs));

    // Utility function to format strings
    function format_string($string, $case = 'title') {
        switch (strtolower($case)) {
            case 'uppercase':
                return strtoupper($string);
            case 'lowercase':
                return strtolower($string);
            case 'title':
                return ucwords(strtolower($string));
            default:
                return $string;
        }
    }

    // Utility function to split category string into an array
    function split_categories($categories) {
        // Trim spaces and split the categories string into an array
        return array_map('strtolower', array_map('trim', explode(',', $categories)));
    }

    // Format events
    $formatted_events = array_map(function ($event) {
        return [
            'id' => (int) $event['id'],
            'type' => 'event',
            'title' => format_string($event['title'], 'title'),
            'image' => $event['image'],
            'date' => $event['date'],
            'time' => $event['time'],
            'location' => format_string($event['location'], 'lowercase'),
            'categories' => split_categories($event['category']), // Split and format event categories
            'institute' => format_string($event['institute'], 'title')
        ];
    }, $events);

    // Log formatted events
    error_log("Formatted events: " . json_encode($formatted_events));

    // Format programs
    $formatted_programs = array_map(function ($program) use ($category_mapping) {
        return [
            'id' => (int) $program['id'],
            'type' => 'program',
            'eventId' => (int) $program['eventId'],
            'title' => format_string($program['title'], 'title'),
            'image' => $program['image'],
            'date' => $program['date'],
            'time' => $program['time'],
            'venue' => format_string($program['venue'], 'title'),
            'subcategory' => format_string($program['subcategory'], 'lowercase'),
            'category' => get_category_from_subcategory($program['subcategory'], $category_mapping), // Main category
            'event' => format_string($program['event'], 'title')
        ];
    }, $programs);

    // Log formatted programs
    error_log("Formatted programs: " . json_encode($formatted_programs));

    // Merge and sort data
    $data = array_merge($formatted_events, $formatted_programs);
    usort($data, function ($a, $b) {
        return $a['id'] <=> $b['id'];
    });

    // Output the JSON response
    echo json_encode($data, JSON_PRETTY_PRINT);

} catch (Exception $e) {
    // Log and handle exceptions
    error_log("Error: " . $e->getMessage());
    echo json_encode(['error' => $e->getMessage()]);
}
?>

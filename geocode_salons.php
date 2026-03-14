<?php
require_once "../config/init.php";
require_once "../config/db.php";

echo "<h2>Geocoding Salons</h2>";

// Get all salons that are missing coordinates
$result = $conn->query("SELECT id, name, address FROM salons WHERE lat IS NULL OR lng IS NULL");

if ($result->num_rows == 0) {
    echo "<p style='color:green;'>✅ All salons already have coordinates!</p>";
    exit;
}

echo "<p>Found " . $result->num_rows . " salons needing coordinates.</p>";
echo "<ul>";

$api_key = 'AIzaSyC7tqW3rNNJ-VLdLsKlKM8Wh4qDAlyqhSA'; // Replace with your actual API key

while ($salon = $result->fetch_assoc()) {
    $address = urlencode($salon['address']);
    $url = "https://maps.googleapis.com/maps/api/geocode/json?address={$address}&key={$api_key}";
    
    $response = file_get_contents($url);
    $data = json_decode($response, true);
    
    if ($data['status'] == 'OK') {
        $lat = $data['results'][0]['geometry']['location']['lat'];
        $lng = $data['results'][0]['geometry']['location']['lng'];
        
        $stmt = $conn->prepare("UPDATE salons SET lat = ?, lng = ? WHERE id = ?");
        $stmt->bind_param("ddi", $lat, $lng, $salon['id']);
        
        if ($stmt->execute()) {
            echo "<li style='color:green;'>✅ {$salon['name']} - Lat: {$lat}, Lng: {$lng}</li>";
        }
    } else {
        echo "<li style='color:red;'>❌ Failed: {$salon['name']} - {$data['status']}</li>";
    }
    
    sleep(0.2); // Rate limiting
}

echo "</ul><p>Done!</p>";
?>
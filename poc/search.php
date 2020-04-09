<?php

require_once '/var/opt/webapp/nogit/creds.php';

function geocode($location) {
    global $geokey;
    $toronto = [43.6532, -79.3832];
    
    $location = str_replace(" ","+",$location);
    
    $gpsurl = "https://maps.googleapis.com/maps/api/geocode/json?address=$location&key=$geokey";
    $gpsres = file_get_contents($gpsurl);
    if (!empty($gpsres)){
        $gpsvar = json_decode($gpsres);
        if ($gpsvar->status=='OK'){
            $lat = isset($gpsvar->results[0]->geometry->location->lat) ? $gpsvar->results[0]->geometry->location->lat : 0;
            $long = isset($gpsvar->results[0]->geometry->location->lng) ? $gpsvar->results[0]->geometry->location->lng : 0;
            return [$lat, $long];
        }
    }
    return $toronto;
}

// if location is specified, then override the given lat/long
$location = filter_input(INPUT_POST, 'location');
if (strlen($location) > 0) {
    [$latitude, $longitude] = geocode($location);
} else {
    $latitude = filter_input(INPUT_POST, 'lat', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $longitude = filter_input(INPUT_POST, 'lng', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
}
$radius = filter_input(INPUT_POST, 'radius', FILTER_SANITIZE_NUMBER_INT);
$radius = $radius == NULL ? 100 : $radius;

try {
    $dbh = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage() . "\n");
}

$data = [
    'lat' => (float) $latitude,
    'lng' => (float) $longitude,
    'results' => []
];

$query = <<<EOT
SELECT *, (3959 * acos(cos(radians(?)) * cos(radians(lat)) * cos(radians(lng) - radians(?)) + sin(radians(?)) * sin(radians(lat)))) AS distance 
    FROM `facilities` WHERE approved = 1 HAVING distance < ? 
    ORDER BY distance LIMIT 0 , 50;
EOT;
$stmt = $dbh->prepare($query);
$result = $stmt->execute([$latitude, $longitude, $latitude, $radius]);
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $services = [];
    if ($row["diesel"] == 1) array_push($services, "Diesel");
    if ($row["washroom"] == 1) array_push($services, "Washroom");
    if ($row["shower"] == 1) array_push($services, "Shower");
    if ($row["reststop"] == 1) array_push($services, "Parking");
    if ($row["coffee"] == 1) array_push($services, "Coffee");
    if ($row["snacks"] == 1) array_push($services, "Snacks");
    if ($row["meal"] == 1) array_push($services, "Meals");
    if ($row["drivethrough"] == 1) array_push($services, "Drive-through");
    if ($row["walkthrough"] == 1) array_push($services, "Walk-through");
    if ($row["otherservices"] == 1) array_push($services, "$other");
    $services_list = implode(', ', $services);

    $data['results'][] = [
        'id' => $row['id'],
        'name' => $row['name'],
        'address' => $row['address'],
        'city' => $row['city'],
        'province_state' => $row['province_state'],
        'country' => $row['country'],
        'postal' => $row['postal'],
        'phone' => $row['phone'],
        'email' => $row['email'],
        'website' => $row['website'],
        'lat' => $row['lat'],
        'lng' => $row['lng'],
        'services_list' => $services_list,
        'distance' => sprintf("%0.1f", $row['distance'])
    ];
}

echo json_encode($data);
?>
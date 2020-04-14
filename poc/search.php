<?php

require_once '/var/opt/webapp/nogit/creds.php';

function sanitize($in) {
    return htmlentities(trim($in), ENT_QUOTES);
}

function geocode($location) {
    global $geokey;
    $toronto = [43.6532, -79.3832];
    $location = str_replace(" ","+",$location);
    
    // Make a call to the Google Geocode API, to convert location to latitude/longitude
    $gpsurl = "https://maps.googleapis.com/maps/api/geocode/json?address=$location&key=$geokey";
    $gpsres = file_get_contents($gpsurl);
    if (!empty($gpsres)) {
        $gpsvar = json_decode($gpsres);
        if ($gpsvar->status == 'OK') {
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

// convert the incoming search options to part of the where clause
$options = filter_input(INPUT_POST, 'options', FILTER_SANITIZE_STRING);
$optionList = [];
$useAllOptions = false;
if ($options === 'all') {
    $useAllOptions = true; 
} else {
    foreach (explode(',', $options) as $opt) {
        switch ($opt) {
            case 'washroom': $optionList[] = "washroom=1"; break;
            case 'shower': $optionList[] = "shower=1"; break;
            case 'reststop': $optionList[] = "reststop=1"; break;
            case 'coffee': $optionList[] = "coffee=1"; break;
            case 'snacks': $optionList[] = "snacks=1"; break;
            case 'meal': $optionList[] = "meal=1"; break;
            case 'drivethrough': $optionList[] = "drivethrough=1"; break;
            case 'walkthrough': $optionList[] = "walkthrough=1"; break;
        }
    }
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

$optionCount = count($optionList);

if ($useAllOptions || $optionCount > 0) {
    $whereOptions = ($optionCount > 0) ? ' AND (' . implode(' OR ', $optionList) . ')' : '';

    $query = <<<EOT
    SELECT *, (3959 * acos(cos(radians(?)) * cos(radians(lat)) * cos(radians(lng) - radians(?)) + sin(radians(?)) * sin(radians(lat)))) AS distance 
        FROM `facilities` WHERE approval_status='approved' AND active=1 $whereOptions HAVING distance < ? 
        ORDER BY distance LIMIT 0 , 50;
    EOT;
    $stmt = $dbh->prepare($query);
    $result = $stmt->execute([$latitude, $longitude, $latitude, $radius]);
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $services = [];
        if ($row["diesel"] == 1) $services[] = "Diesel";
        if ($row["washroom"] == 1) $services[] = "Washroom";
        if ($row["shower"] == 1) $services[] = "Shower";
        if ($row["reststop"] == 1) $services[] = "Parking";
        if ($row["coffee"] == 1) $services[] = "Coffee";
        if ($row["snacks"] == 1) $services[] = "Snacks";
        if ($row["meal"] == 1) $services[] = "Meals";
        if ($row["drivethrough"] == 1) $services[] = "Drive-through";
        if ($row["walkthrough"] == 1) $services[] = "Walk-through";
        if (!empty($row["otherservices"])) $services[] = $row["otherservices"];

        $data['results'][] = [
            'id'             => sanitize($row['id']),
            'name'           => sanitize($row['name']),
            'address'        => sanitize($row['address']),
            'city'           => sanitize($row['city']),
            'province_state' => sanitize($row['province_state']),
            'country'        => sanitize($row['country']),
            'postal'         => sanitize($row['postal']),
            'phone'          => sanitize($row['phone']),
            'email'          => sanitize($row['email']),
            'website'        => sanitize($row['website']),
            'lat'            => sanitize($row['lat']),
            'lng'            => sanitize($row['lng']),
            'services_list'  => sanitize(implode(', ', $services)),
            'distance'       => sprintf("%0.1f", $row['distance'])
        ];
    }
}

echo json_encode($data);
?>
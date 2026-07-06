<?php

$url = 'http://localhost:8000/debug-python';
$response = file_get_contents($url);
if ($response === false) {
    echo "Failed to fetch response from " . $url . PHP_EOL;
} else {
    echo "Response:" . PHP_EOL;
    $data = json_decode($response, true);
    print_r($data);
}

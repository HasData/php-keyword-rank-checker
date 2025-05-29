<?php
require 'vendor/autoload.php';
use Httpful\Request;

// Set your params
$location = 'Austin,Texas,United States';
$query = 'Coffee';
$filter = 1;
$domain = 'google.com';
$gl = 'us';
$hl = 'en';
$deviceType = 'desktop';

// Set your domain to check
$searchedDomain = 'wikipedia.org';

// Set your api-key
$headers = array(
    'x-api-key' => 'YOUR-API-KEY',
);

$url = sprintf(
    'https://api.hasdata.com/scrape/google?location=%s&q=%s&filter=%d&domain=%s&gl=%s&hl=%s&deviceType=%s',
    rawurlencode($location),
    rawurlencode($query),
    $filter,
    rawurlencode($domain),
    rawurlencode($gl),
    rawurlencode($hl),
    rawurlencode($deviceType)
);

$response = Request::get($url)
    ->addHeaders($headers)
    ->send();
$data = json_decode($response->raw_body, true);

if (!is_array($data) || empty($data)) {
    die('Error with JSON decoding or Empty data');
}

if (isset($data['organicResults']) && is_array($data['organicResults']) && !empty($data['organicResults'])) {
    $result = array();
    foreach ($data['organicResults'] as $item) {
        if (isset($item['link']) && strpos($item['link'], $searchedDomain) !== false) {
            $result[] = array(
                'position' => $item['position'],
                'domain' => $searchedDomain,
                'keyword'=> $query,
                'link' => $item['link'],
                'title' => $item['title'],
                'displayedLink' => $item['displayedLink'],
                'source' => $item['source'],
                'snippet' => $item['snippet'],
                'googleUrl' => $data['requestMetadata']['googleUrl'],
                'googleHtmlFile' => $data['requestMetadata']['googleHtmlFile'],
                'date' => date('Y-m-d H:i:s'),
            );
        }
    }
    $csvFilePath = 'rank_checker.csv';
    $fileExists = file_exists($csvFilePath);

    $file = fopen($csvFilePath, 'a');

    if (!$fileExists || filesize($csvFilePath) == 0) {
        fputcsv($file, array_keys($result[0]));
    }

    foreach ($result as $row) {
        fputcsv($file, $row);
    }

    fclose($file);

    echo 'Data saved to CSV file: ' . $csvFilePath;
} else {
    die('organicResults is empty');
}

?>

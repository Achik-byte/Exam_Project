<?php
define('API_BASE_URL', getenv('API_BASE_URL') ?: 'https://examproject-production-e573.up.railway.app');
define('PUBLIC_BASE_URL', getenv('PUBLIC_BASE_URL') ?: 'http://localhost');

function apiRequest($endpoint, $method = 'GET', $data = null, $token = null) {
    $url = API_BASE_URL . $endpoint;
    $ch  = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);

    $headers = ['Content-Type: application/json'];
    if ($token) $headers[] = 'Authorization: Bearer ' . $token;
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    if ($data !== null) curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error    = curl_error($ch);
    curl_close($ch);

    if ($error) {
        return ['status_code' => 0, 'body' => ['status' => 'error', 'message' => 'Connection error: ' . $error]];
    }
    return ['status_code' => $httpCode, 'body' => json_decode($response, true) ?? []];
}
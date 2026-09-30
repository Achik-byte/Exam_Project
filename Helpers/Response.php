<?php
namespace App\Helpers;

class Response {
    public static function json($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }

    public static function success($message, $data = null, $statusCode = 200) {
        $response = ['status' => 'success', 'message' => $message];
        if ($data !== null) $response['data'] = $data;
        self::json($response, $statusCode);
    }

    public static function error($message, $statusCode = 400, $errors = null) {
        $response = ['status' => 'error', 'message' => $message];
        if ($errors !== null) $response['errors'] = $errors;
        self::json($response, $statusCode);
    }
}
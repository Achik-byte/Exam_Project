<?php
// ===== CORS Headers =====
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// ===== Autoload =====
require_once __DIR__ . '/vendor/autoload.php';

use App\Helpers\Response;
use App\Middleware\AuthMiddleware;
use App\Middleware\RateLimitMiddleware;
use App\Middleware\LoggerMiddleware;
use App\Controllers\AuthController;
use App\Controllers\UserController;
use App\Controllers\CourseController;
use App\Controllers\SubjectController;
use App\Controllers\ExamController;
use App\Controllers\ResultController;
use App\Controllers\NotificationController;

// ===== Parse URL =====
$basePath = '/exam_api';
$uri      = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri      = str_replace($basePath, '', $uri);
$uri      = trim($uri, '/');

$segments = explode('/', $uri);
$resource = $segments[0] ?? '';
$id       = $segments[1] ?? null;

$method = $_SERVER['REQUEST_METHOD'];

// ===== Rate Limit =====
if ($resource !== 'login') {
    RateLimitMiddleware::check(100, 60);
}

// Log request
LoggerMiddleware::log($uri, $method);

// ===== Routing =====
try {
    switch ($resource) {

        // --- AUTH ---
        case 'login':
            (new AuthController())->login();
            break;

        case 'register':
            (new AuthController())->register();
            break;

        case 'profile':
            (new AuthController())->profile();
            break;

        // --- USERS ---
        case 'users':
            $ctrl = new UserController();
            match($method) {
                'GET'    => $ctrl->index($id),
                'POST'   => $ctrl->store(),
                'PUT'    => $ctrl->update($id),
                'DELETE' => $ctrl->destroy($id),
                default  => Response::error('Method not allowed', 405)
            };
            break;

        // --- STUDENTS ---
        case 'students':
            $ctrl = new UserController();
            match($method) {
                'GET'    => $ctrl->students($id),
                default  => Response::error('Method not allowed', 405)
            };
            break;

            // --- PUBLIC VERIFICATION (untuk QR code) ---
case 'verify':
    $ctrl = new UserController();
    match($method) {
        'GET'    => $ctrl->verify($id),
        default  => Response::error('Method not allowed', 405)
    };
    break;
        // --- COURSES ---
        case 'courses':
            $ctrl = new CourseController();
            match($method) {
                'GET'    => $ctrl->index($id),
                'POST'   => $ctrl->store(),
                'PUT'    => $ctrl->update($id),
                'DELETE' => $ctrl->destroy($id),
                default  => Response::error('Method not allowed', 405)
            };
            break;

        // --- SUBJECTS ---
        case 'subjects':
            $ctrl = new SubjectController();
            match($method) {
                'GET'    => $ctrl->index($id),
                default  => Response::error('Method not allowed', 405)
            };
            break;

        // --- EXAMINATIONS ---
        case 'examinations':
            $ctrl = new ExamController();
            match($method) {
                'GET'    => $ctrl->index($id),
                'POST'   => $ctrl->store(),
                'PUT'    => $ctrl->update($id),
                'DELETE' => $ctrl->destroy($id),
                default  => Response::error('Method not allowed', 405)
            };
            break;

        // --- RESULTS ---
        case 'results':
            $ctrl = new ResultController();
            match($method) {
                'GET'    => $ctrl->index($id),
                'POST'   => $ctrl->store(),
                'PUT'    => $ctrl->update($id),
                'DELETE' => $ctrl->destroy($id),
                default  => Response::error('Method not allowed', 405)
            };
            break;

        // --- NOTIFICATIONS ---
        case 'notifications':
            $ctrl = new NotificationController();
            match($method) {
                'GET'    => $ctrl->index($id),
                'POST'   => $ctrl->store(),
                'PUT'    => $ctrl->update($id),
                'DELETE' => $ctrl->destroy($id),
                default  => Response::error('Method not allowed', 405)
            };
            break;

        // --- QR CODE (Third-Party API) ---
        case 'generate-qr':
            (new \App\Controllers\QrController())->generate();
            break;

        // --- DEFAULT ---
        default:
            Response::json([
                'status'  => 'success',
                'message' => 'Exam Scheduling & Result Management API',
                'version' => '1.0',
                'endpoints' => [
                    'POST   /login',
                    'POST   /register',
                    'GET    /profile',
                    'GET    /users',
                    'GET    /students',
                    'GET    /courses',
                    'GET    /subjects',
                    'GET    /examinations',
                    'GET    /results',
                    'GET    /notifications',
                    'GET    /generate-qr',
                ]
            ], 200);
    }
} catch (\Throwable $e) {
    Response::error('Server Error: ' . $e->getMessage(), 500);
}
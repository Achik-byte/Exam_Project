<?php
namespace App\Controllers;

use App\Helpers\Response;
use App\Middleware\AuthMiddleware;

class QrController {
    
    // GET /generate-qr?data=CS101-ExamSlip
    public function generate() {
        AuthMiddleware::verifyToken();
        
        $data = $_GET['data'] ?? 'exam-slip';
        
        // Guna API percuma dari qrserver.com (Third-Party API)
        $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' . urlencode($data);
        
        Response::success('QR Code generated successfully', [
            'input'  => $data,
            'qr_url' => $qrUrl,
            'note'   => 'Access this URL to view the QR code image'
        ]);
    }
}
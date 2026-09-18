<?php

namespace App\Services;

use SendGrid;
use SendGrid\Mail\Mail;
use Illuminate\Support\Facades\Log;

class SendGridService
{
    protected $sendgrid;

    public function __construct()
    {
        $apiKey = env('SENDGRID_API_KEY');
        if (!$apiKey) {
            throw new \Exception('SENDGRID_API_KEY no configurada');
        }
        $this->sendgrid = new SendGrid($apiKey);
    }

    public function sendEmail($to, $subject, $htmlContent, $fromEmail = null, $fromName = null)
    {
        try {
            $fromEmail = $fromEmail ?? env('MAIL_FROM_ADDRESS', 'soportemecxihub@gmail.com');
            $fromName = $fromName ?? env('MAIL_FROM_NAME', 'MecxiHub');

            $email = new Mail();
            $email->setFrom($fromEmail, $fromName);
            $email->setSubject($subject);
            $email->addTo($to);
            $email->addContent("text/html", $htmlContent);

            $response = $this->sendgrid->send($email);

            if ($response->statusCode() >= 200 && $response->statusCode() < 300) {
                Log::info('Email enviado exitosamente a ' . $to);
                return ['success' => true, 'status' => $response->statusCode()];
            } else {
                Log::error('Error al enviar email: ' . $response->body());
                return ['success' => false, 'status' => $response->statusCode(), 'error' => $response->body()];
            }
        } catch (\Exception $e) {
            Log::error('Exception al enviar email: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}

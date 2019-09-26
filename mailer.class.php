<?php

require_once 'PHPMailer/src/Exception.php';
require_once 'PHPMailer/src/PHPMailer.php';
require_once 'PHPMailer/src/SMTP.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mailer {
    private $config;
    private $mail;

    public function __construct() {
        $this->config = Config::getConfig();

        $this->mail = new PHPMailer(true);
        $this->mail->isSMTP();
        $this->mail->SMTPAuth     = true;
        $this->mail->Host         = $this->config['SMTP_HOST'];
        $this->mail->Username     = $this->config['SMTP_USER'];
        $this->mail->Password     = $this->config['SMTP_PASS'];
        $this->mail->SMTPSecure   = 'tls';
        $this->mail->Port         = 587;
        $this->mail->setFrom($this->config['SMTP_FROM']);
        $this->mail->addAddress($this->config['SMTP_TO']);
    }

    public function send($subject, $body) {
        try {
            $this->mail->Subject = $subject;
            $this->mail->Body = $body;
            $this->mail->send();
        }
        catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$this->mail->ErrorInfo}\n";
        }
    }
}
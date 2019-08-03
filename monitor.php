<?php
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    
    require 'PHPMailer/src/Exception.php';
    require 'PHPMailer/src/PHPMailer.php';
    require 'PHPMailer/src/SMTP.php';
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;

    spl_autoload_register(function ($class_name) {
        include $class_name . '.class.php';
    });

    $config = Config::getConfig();
    
    $monitors = getMonitors();
    foreach($monitors as $monitor) {
        if(!$monitor->test()) {
            switch($monitor->type) {
                case "page":
                    $string = "Page %s failed to load correctly!\n";
                    $msg = sprintf($string, $monitor->url);
                    break;
                case "port":
                    $string = "Port %s:%d isn't accepting connections!\n";
                    $msg = sprintf($string, $monitor->host, $monitor->port);
                    break;
            }
            echo $msg;
            if(!$monitor->getFailed()) {
                sendMail($msg);
                $monitor->setFailed(true);
            }
        }
        else {
            printf("Monitor %d OK\n",$monitor->id);
            if($monitor->getFailed()) {
                sendMail("Restored");
                $monitor->setFailed(false);
            }
        }
    }

    function sendMail($message) {
        global $config;
        try {
            $mail = new PHPMailer(true);
            //$mail->SMTPDebug = 2;
            $mail->isSMTP();
            $mail->SMTPAuth     = true;
            $mail->Host         = $config['SMTP_HOST'];
            $mail->Username     = $config['SMTP_USER'];
            $mail->Password     = $config['SMTP_PASS'];
            $mail->SMTPSecure   = 'tls';
            $mail->Port         = 587;
            
            $mail->setFrom($config['SMTP_FROM']);
            $mail->addAddress($config['SMTP_TO']);
            $mail->Subject = "PnPMonitor failure";
            $mail->Body = $message;
            $mail->send();
            echo "Mail sent.\n";
        }
        catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}\n";
        }
    }

    function getMonitors() {
        $db = Database::getConnection();
        $rows = [];
        $result = $db->query("SELECT id FROM monitor");
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $rows[] = new Monitor($row['id']);
            }
            $result->close();
        }
        return $rows;
    }



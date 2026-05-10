<?php
/**
 * Eti-Osa Carnival - Manual Speech Form Processor
 * (Bypasses missing json_encode extension)
 */

// 1. Setup
header('Content-Type: application/json');
error_reporting(0); // Turn off errors now that we found the culprit

ob_start();

try {
    $formType = isset($_POST['form_type']) ? $_POST['form_type'] : 'Inquiry';

    // 2. Locate Library
    $mailerDir = is_dir(__DIR__ . '/PHPMailer/') ? __DIR__ . '/PHPMailer/' : __DIR__ . '/phpmailer/';
    if (!is_dir($mailerDir)) {
        echo '{"success": false, "message": "PHPMailer folder missing"}';
        exit;
    }

    require_once $mailerDir . 'Exception.php';
    require_once $mailerDir . 'PHPMailer.php';
    require_once $mailerDir . 'SMTP.php';

    // 3. Initialize
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    
    $mail->isSMTP();
    $mail->Host       = 'mail.etiosacarnival.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'mail@etiosacarnival.com';
    $mail->Password   = 'c=80L]xS27eM';
    $mail->SMTPSecure = 'ssl';
    $mail->Port       = 465;
    $mail->CharSet    = 'UTF-8';

    $mail->setFrom('mail@etiosacarnival.com', 'Eti-Osa Carnival');
    $mail->addAddress('ayomidegrace26@gmail.com');
    $mail->addAddress('support@etiosacarnival.com');
    $mail->addAddress('gloryglobalresources@gmail.com');

    $mail->isHTML(true);
    $mail->Subject = "New Website Submission: " . $formType;
    
    $table = "<table border='1' style='width:100%; border-collapse:collapse;'>";
    foreach ($_POST as $key => $value) {
        if ($key == 'form_type') continue;
        $table .= "<tr><td style='padding:10px;'><b>" . htmlspecialchars($key) . ":</b></td><td style='padding:10px;'>" . htmlspecialchars($value) . "</td></tr>";
    }
    $table .= "</table>";
    $mail->Body = "<h2>New Website Inquiry</h2>" . $table;

    if ($mail->send()) {
        if (ob_get_length()) ob_clean();
        // MANUAL JSON OUTPUT
        echo '{"success": true, "message": "Thank you! Your message has been sent."}';
    }

} catch (Exception $e) {
    // 4. Fallback to standard mail()
    $details = "Form Submission Received:\n\n";
    foreach ($_POST as $k => $v) { $details .= "$k: $v\n"; }
    
    if (@mail("ayomidegrace26@gmail.com", "Backup: " . $formType, $details, "From: mail@etiosacarnival.com")) {
        if (ob_get_length()) ob_clean();
        echo '{"success": true, "message": "Thank you! Your message has been sent."}';
    } else {
        if (ob_get_length()) ob_clean();
        echo '{"success": false, "message": "Server mailing error."}';
    }
}

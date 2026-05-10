<?php
/**
 * Eti-Osa Carnival - Full Robust Form Processor
 */

// 1. Setup Environment & Logging
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
header('Content-Type: application/json');

// 2. Check Method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid Request Method: ' . $_SERVER['REQUEST_METHOD'] . '. Please ensure you are submitting the form via POST.'
    ]);
    exit;
}

// 3. Setup Error Catcher
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== NULL && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        if (ob_get_length()) ob_clean();
        echo json_encode([
            'success' => false,
            'message' => 'Fatal Server Error: ' . $error['message']
        ]);
    }
});

ob_start();

try {
    // 4. Load PHPMailer
    $mailerDir = __DIR__ . '/PHPMailer/';
    require_once $mailerDir . 'Exception.php';
    require_once $mailerDir . 'PHPMailer.php';
    require_once $mailerDir . 'SMTP.php';

    $mail = new \PHPMailer\PHPMailer\PHPMailer(true);

    // 5. SMTP Configuration
    $mail->isSMTP();
    $mail->Host       = 'mail.etiosacarnival.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'mail@etiosacarnival.com';
    $mail->Password   = 'c=80L]xS27eM';
    $mail->SMTPSecure = 'ssl';
    $mail->Port       = 465;
    $mail->CharSet    = 'UTF-8';
    $mail->Timeout    = 25;

    // 6. Recipients
    $mail->setFrom('eti-osa@strideauto.co', 'Eti-Osa Carnival Website');
    $mail->addAddress('ayomidegrace26@gmail.com');
    $mail->addAddress('support@etiosacarnival.com');
    $mail->addAddress('gloryglobalresources@gmail.com');

    // 7. Content
    $formType = $_POST['form_type'] ?? 'General Inquiry';
    $fullname = $_POST['fullname'] ?? 'Valued Visitor';
    $userEmail = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    
    if ($userEmail) {
        $mail->addReplyTo($userEmail, $fullname);
    }

    $mail->isHTML(true);
    $mail->Subject = "New Website Inquiry: " . ucwords(str_replace('_', ' ', $formType));
    
    $tableRows = "";
    foreach ($_POST as $key => $value) {
        if ($key === 'form_type') continue;
        $label = ucwords(str_replace(['_', '-'], ' ', $key));
        $val = nl2br(htmlspecialchars($value));
        $tableRows .= "<tr><td style='padding:10px; border:1px solid #eee; background:#f9f9f9; width:150px;'><strong>$label:</strong></td><td style='padding:10px; border:1px solid #eee;'>$val</td></tr>";
    }

    $mail->Body = "
        <div style='font-family:sans-serif; color:#113521; max-width:600px; border:1px solid #D2A143; border-radius:12px; overflow:hidden;'>
            <div style='background:#113521; color:#D2A143; padding:20px; text-align:center;'>
                <h1 style='margin:0; font-size:20px;'>ETI-OSA CARNIVAL 2026</h1>
            </div>
            <div style='padding:30px;'>
                <h2 style='color:#113521; font-size:18px;'>New Submission Details</h2>
                <table style='width:100%; border-collapse:collapse; margin-top:15px;'>$tableRows</table>
            </div>
        </div>
    ";

    // 8. Send
    if ($mail->send()) {
        if (ob_get_length()) ob_clean();
        echo json_encode(['success' => true, 'message' => 'Thank you! Your message has been sent.']);
    } else {
        throw new Exception("Mailing system failure.");
    }

} catch (Exception $e) {
    // 9. Fallback to PHP mail()
    $to = "ayomidegrace26@gmail.com, support@etiosacarnival.com, gloryglobalresources@gmail.com";
    $subject = "Fallback Submission: " . $formType;
    $msg = "A submission was received but SMTP failed. Details:\n\n";
    foreach ($_POST as $k => $v) { $msg .= "$k: $v\n"; }
    $headers = "From: eti-osa@strideauto.co";
    
    if (mail($to, $subject, $msg, $headers)) {
        if (ob_get_length()) ob_clean();
        echo json_encode(['success' => true, 'message' => 'Message sent (Backup System).']);
    } else {
        if (ob_get_length()) ob_clean();
        echo json_encode(['success' => false, 'message' => 'The server was unable to send your message. Please try again later.']);
    }
}

<?php
/**
 * Eti-Osa Carnival - Ultra-Compatible Form Processor
 */

// 1. Force error reporting ON for one run to find the crash, but keep it JSON compatible
error_reporting(E_ALL);
ini_set('display_errors', 0); // Keep 0 to avoid breaking JSON, but we'll capture them
header('Content-Type: application/json');

// Catch fatal errors that don't throw exceptions
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== NULL && ($error['type'] === E_ERROR || $error['type'] === E_PARSE || $error['type'] === E_CORE_ERROR || $error['type'] === E_COMPILE_ERROR)) {
        if (ob_get_length()) ob_clean();
        echo json_encode([
            'success' => false,
            'message' => 'Fatal PHP Error: ' . $error['message'] . ' in ' . $error['file'] . ' on line ' . $error['line']
        ]);
    }
});

ob_start();

try {
    // 2. Load PHPMailer files manually for maximum compatibility
    $dir = dirname(__FILE__) . '/PHPMailer/';
    
    if (!file_exists($dir . 'Exception.php')) throw new Exception("Missing PHPMailer/Exception.php");
    if (!file_exists($dir . 'PHPMailer.php')) throw new Exception("Missing PHPMailer/PHPMailer.php");
    if (!file_exists($dir . 'SMTP.php')) throw new Exception("Missing PHPMailer/SMTP.php");

    require_once $dir . 'Exception.php';
    require_once $dir . 'PHPMailer.php';
    require_once $dir . 'SMTP.php';

    // Use namespaces after requiring
    $mail = new \PHPMailer\PHPMailer\PHPMailer(true);

    // 3. Configuration
    $smtpHost = 'strideauto.co';
    $smtpUsername = 'eti-osa@strideauto.co';
    $smtpPassword = 'X=kSAG}@^u#%';
    $smtpPort = 465;
    $smtpSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;

    $recipientEmailStr = 'ayomidegrace26@gmail.com, support@etiosacarnival.com, gloryglobalresources@gmail.com';
    $recipientName = 'Eti-Osa Carnival Support';

    // 4. Request Validation
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Please submit the form via the website.');
    }

    $formType = $_POST['form_type'] ?? 'contact';
    $userEmail = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $userName = htmlspecialchars($_POST['fullname'] ?? 'Website User');

    // 5. Setup Mail
    $mail->isSMTP();
    $mail->Host       = $smtpHost;
    $mail->SMTPAuth   = true;
    $mail->Username   = $smtpUsername;
    $mail->Password   = $smtpPassword;
    $mail->SMTPSecure = $smtpSecure;
    $mail->Port       = $smtpPort;
    $mail->CharSet    = 'UTF-8';
    $mail->Timeout    = 20;

    $mail->setFrom($smtpUsername, 'Eti-Osa Carnival');
    
    $emails = explode(',', $recipientEmailStr);
    foreach ($emails as $email) {
        $email = trim($email);
        if ($email) $mail->addAddress($email, $recipientName);
    }

    if ($userEmail) {
        $mail->addReplyTo($userEmail, $userName);
    }

    $mail->isHTML(true);

    // 6. Content Logic
    switch ($formType) {
        case 'sponsorship':
            $company = htmlspecialchars($_POST['company'] ?? 'N/A');
            $tier = htmlspecialchars($_POST['tier'] ?? 'N/A');
            $mail->Subject = "Sponsorship Inquiry: $company";
            $mail->Body = "<h2>Sponsorship</h2><p><strong>Name:</strong> $userName</p><p><strong>Company:</strong> $company</p><p><strong>Tier:</strong> $tier</p>";
            break;
        
        case 'band_registration':
            $band = htmlspecialchars($_POST['band'] ?? 'N/A');
            $mail->Subject = "Band Registration: $band";
            $mail->Body = "<h2>Band Registration</h2><p><strong>Name:</strong> $userName</p><p><strong>Band:</strong> $band</p>";
            break;

        default: // 'contact' or any other
            $subject = htmlspecialchars($_POST['subject'] ?? 'New Message');
            $phone = htmlspecialchars($_POST['phone'] ?? 'N/A');
            $msgContent = nl2br(htmlspecialchars($_POST['message'] ?? ''));
            $mail->Subject = "Contact: $subject";
            $mail->Body = "<h2>Message from Website</h2><p><strong>From:</strong> $userName ($userEmail)</p><p><strong>Phone:</strong> $phone</p><hr><p>$msgContent</p>";
            break;
    }

    // 7. Execute
    if (!$mail->send()) {
        throw new Exception($mail->ErrorInfo);
    }

    if (ob_get_length()) ob_clean();
    echo json_encode(['success' => true, 'message' => 'Message sent successfully!']);

} catch (Exception $e) {
    if (ob_get_length()) ob_clean();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} catch (\Throwable $t) {
    if (ob_get_length()) ob_clean();
    echo json_encode(['success' => false, 'message' => 'System Error: ' . $t->getMessage()]);
}

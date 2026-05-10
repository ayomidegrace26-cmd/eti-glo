<?php
/**
 * Eti-Osa Carnival - Ground Truth Debugger
 */
error_reporting(E_ALL);
ini_set('display_errors', 1); // FORCE errors to show for debugging

echo "<h1>Eti-Osa Mail Debugger</h1>";
echo "<strong>PHP Version:</strong> " . PHP_VERSION . "<br>";

$mailerDir = __DIR__ . '/PHPMailer/';
echo "<strong>Checking Directory:</strong> $mailerDir <br>";

if (is_dir($mailerDir)) {
    echo "<span style='color:green;'>✅ PHPMailer directory found.</span><br>";
    $files = ['Exception.php', 'PHPMailer.php', 'SMTP.php'];
    foreach ($files as $f) {
        if (file_exists($mailerDir . $f)) {
            echo " - <span style='color:green;'>✅ $f found.</span><br>";
        } else {
            echo " - <span style='color:red;'>❌ $f MISSING!</span><br>";
        }
    }
} else {
    echo "<span style='color:red;'>❌ PHPMailer directory NOT found. Checking lowercase...</span><br>";
    $mailerDir = __DIR__ . '/phpmailer/';
    if (is_dir($mailerDir)) {
        echo "<span style='color:green;'>✅ Found lowercase phpmailer directory.</span><br>";
    } else {
        echo "<span style='color:red;'>❌ Total Failure: No PHPMailer folder found in root.</span><br>";
    }
}

echo "<h2>Testing Library Load...</h2>";
try {
    require_once $mailerDir . 'Exception.php';
    require_once $mailerDir . 'PHPMailer.php';
    require_once $mailerDir . 'SMTP.php';
    echo "<span style='color:green;'>✅ Library files included successfully.</span><br>";
    
    $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
    echo "<span style='color:green;'>✅ PHPMailer Class initialized successfully.</span><br>";
} catch (Exception $e) {
    echo "<span style='color:red;'>❌ CRASH during load: " . $e->getMessage() . "</span><br>";
} catch (Error $err) {
    echo "<span style='color:red;'>❌ CRITICAL ERROR: " . $err->getMessage() . "</span><br>";
}

echo "<h2>Testing SMTP Connection...</h2>";
$host = 'mail.etiosacarnival.com';
$port = 465;
echo "Attempting to open connection to $host on $port...<br>";
$connection = @fsockopen($host, $port, $errno, $errstr, 10);
if ($connection) {
    echo "<span style='color:green;'>✅ Server can reach $host on port $port.</span><br>";
    fclose($connection);
} else {
    echo "<span style='color:red;'>❌ Server BLOCKED: Cannot reach $host ($errstr). Check your firewall.</span><br>";
}
?>

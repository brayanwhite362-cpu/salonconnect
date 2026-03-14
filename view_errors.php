<?php
echo "<h2>📋 PHP Error Log</h2>";
$logfile = 'C:\\xampp\\php\\logs\\php_error_log';
if (file_exists($logfile)) {
    $logs = file_get_contents($logfile);
    echo "<pre style='background:#1a1a2a; color:#f5f4ff; padding:15px; max-height:500px; overflow:auto;'>";
    echo htmlspecialchars($logs);
    echo "</pre>";
} else {
    echo "No log file found";
}
?>
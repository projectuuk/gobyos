<?php
/**
 * ErrorHandler Class
 * 
 * Provides secure error handling and logging functionality
 * to prevent information disclosure while maintaining detailed logs for debugging.
 * 
 * @author Manus AI
 * @version 1.0
 */

class ErrorHandler {
    private $logDir;
    private $isProduction;
    private $maxLogSize;
    private $logRetentionDays;
    
    public function __construct($logDir = null, $isProduction = true, $maxLogSize = 10485760, $logRetentionDays = 30) {
        $this->logDir = $logDir ?: __DIR__ . '/../logs';
        $this->isProduction = $isProduction;
        $this->maxLogSize = $maxLogSize; // 10MB default
        $this->logRetentionDays = $logRetentionDays;
        
        // Ensure log directory exists and is secure
        $this->setupLogDirectory();
        
        // Register error handlers
        $this->registerHandlers();
    }
    
    /**
     * Setup secure log directory
     */
    private function setupLogDirectory() {
        if (!is_dir($this->logDir)) {
            mkdir($this->logDir, 0755, true);
        }
        
        // Create .htaccess to prevent web access to logs
        $htaccessPath = $this->logDir . '/.htaccess';
        if (!file_exists($htaccessPath)) {
            $htaccessContent = "Order Deny,Allow\nDeny from all\n";
            file_put_contents($htaccessPath, $htaccessContent);
        }
        
        // Create index.php to prevent directory listing
        $indexPath = $this->logDir . '/index.php';
        if (!file_exists($indexPath)) {
            file_put_contents($indexPath, "<?php\n// Access denied\nhttp_response_code(403);\nexit;\n?>");
        }
    }
    
    /**
     * Register error and exception handlers
     */
    private function registerHandlers() {
        // Set error handler
        set_error_handler([$this, 'handleError']);
        
        // Set exception handler
        set_exception_handler([$this, 'handleException']);
        
        // Set shutdown handler for fatal errors
        register_shutdown_function([$this, 'handleShutdown']);
        
        // Configure error reporting
        if ($this->isProduction) {
            error_reporting(E_ALL);
            ini_set('display_errors', 0);
            ini_set('log_errors', 1);
        } else {
            error_reporting(E_ALL);
            ini_set('display_errors', 1);
            ini_set('log_errors', 1);
        }
    }
    
    /**
     * Handle PHP errors
     * 
     * @param int $severity Error severity
     * @param string $message Error message
     * @param string $file File where error occurred
     * @param int $line Line number where error occurred
     * @return bool
     */
    public function handleError($severity, $message, $file, $line) {
        // Don't handle errors that are suppressed with @
        if (!(error_reporting() & $severity)) {
            return false;
        }
        
        $errorType = $this->getErrorType($severity);
        
        $errorData = [
            'type' => 'PHP_ERROR',
            'severity' => $errorType,
            'message' => $message,
            'file' => $file,
            'line' => $line,
            'timestamp' => date('Y-m-d H:i:s'),
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'request_uri' => $_SERVER['REQUEST_URI'] ?? 'unknown',
            'request_method' => $_SERVER['REQUEST_METHOD'] ?? 'unknown',
            'stack_trace' => $this->getStackTrace()
        ];
        
        $this->logError($errorData);
        
        // For fatal errors, show user-friendly message
        if (in_array($severity, [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR])) {
            $this->showUserFriendlyError();
            exit;
        }
        
        return true;
    }
    
    /**
     * Handle uncaught exceptions
     * 
     * @param Throwable $exception
     */
    public function handleException($exception) {
        $errorData = [
            'type' => 'UNCAUGHT_EXCEPTION',
            'exception_class' => get_class($exception),
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'timestamp' => date('Y-m-d H:i:s'),
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'request_uri' => $_SERVER['REQUEST_URI'] ?? 'unknown',
            'request_method' => $_SERVER['REQUEST_METHOD'] ?? 'unknown',
            'stack_trace' => $exception->getTraceAsString()
        ];
        
        $this->logError($errorData);
        $this->showUserFriendlyError();
    }
    
    /**
     * Handle fatal errors during shutdown
     */
    public function handleShutdown() {
        $error = error_get_last();
        
        if ($error && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE])) {
            $errorData = [
                'type' => 'FATAL_ERROR',
                'severity' => $this->getErrorType($error['type']),
                'message' => $error['message'],
                'file' => $error['file'],
                'line' => $error['line'],
                'timestamp' => date('Y-m-d H:i:s'),
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
                'request_uri' => $_SERVER['REQUEST_URI'] ?? 'unknown',
                'request_method' => $_SERVER['REQUEST_METHOD'] ?? 'unknown'
            ];
            
            $this->logError($errorData);
            
            // Clean any output buffer
            if (ob_get_level()) {
                ob_clean();
            }
            
            $this->showUserFriendlyError();
        }
    }
    
    /**
     * Log custom application errors
     * 
     * @param string $message Error message
     * @param string $level Error level (INFO, WARNING, ERROR, CRITICAL)
     * @param array $context Additional context
     */
    public function logCustomError($message, $level = 'ERROR', $context = []) {
        $errorData = [
            'type' => 'APPLICATION_ERROR',
            'level' => $level,
            'message' => $message,
            'context' => $context,
            'timestamp' => date('Y-m-d H:i:s'),
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'request_uri' => $_SERVER['REQUEST_URI'] ?? 'unknown',
            'request_method' => $_SERVER['REQUEST_METHOD'] ?? 'unknown',
            'stack_trace' => $this->getStackTrace()
        ];
        
        $this->logError($errorData);
    }
    
    /**
     * Log security events
     * 
     * @param string $event Event type
     * @param string $message Event message
     * @param array $context Additional context
     */
    public function logSecurityEvent($event, $message, $context = []) {
        $eventData = [
            'type' => 'SECURITY_EVENT',
            'event' => $event,
            'message' => $message,
            'context' => $context,
            'timestamp' => date('Y-m-d H:i:s'),
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'request_uri' => $_SERVER['REQUEST_URI'] ?? 'unknown',
            'request_method' => $_SERVER['REQUEST_METHOD'] ?? 'unknown',
            'session_id' => session_id(),
            'user_id' => $_SESSION['user_id'] ?? null
        ];
        
        $this->logError($eventData, 'security');
        
        // For critical security events, also log to system log
        if (in_array($event, ['SQL_INJECTION_ATTEMPT', 'XSS_ATTEMPT', 'BRUTE_FORCE_ATTACK'])) {
            error_log("SECURITY ALERT: {$event} - {$message} from IP: " . ($eventData['ip']));
        }
    }
    
    /**
     * Write error data to log file
     * 
     * @param array $errorData Error data to log
     * @param string $logType Log type (error, security, etc.)
     */
    private function logError($errorData, $logType = 'error') {
        $logFile = $this->logDir . "/{$logType}_" . date('Y-m-d') . '.log';
        
        // Check log rotation
        $this->rotateLogIfNeeded($logFile);
        
        $logEntry = json_encode($errorData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL;
        
        // Use file locking to prevent corruption
        file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
        
        // Set secure permissions
        chmod($logFile, 0644);
    }
    
    /**
     * Rotate log file if it exceeds maximum size
     * 
     * @param string $logFile Log file path
     */
    private function rotateLogIfNeeded($logFile) {
        if (file_exists($logFile) && filesize($logFile) > $this->maxLogSize) {
            $rotatedFile = $logFile . '.' . time();
            rename($logFile, $rotatedFile);
            
            // Compress old log file
            if (function_exists('gzopen')) {
                $this->compressLogFile($rotatedFile);
            }
        }
        
        // Clean up old log files
        $this->cleanupOldLogs();
    }
    
    /**
     * Compress log file using gzip
     * 
     * @param string $filePath File to compress
     */
    private function compressLogFile($filePath) {
        $gzFile = $filePath . '.gz';
        
        $file = fopen($filePath, 'rb');
        $gz = gzopen($gzFile, 'wb9');
        
        if ($file && $gz) {
            while (!feof($file)) {
                gzwrite($gz, fread($file, 8192));
            }
            
            fclose($file);
            gzclose($gz);
            
            // Remove original file after compression
            unlink($filePath);
        }
    }
    
    /**
     * Clean up old log files
     */
    private function cleanupOldLogs() {
        $cutoffTime = time() - ($this->logRetentionDays * 24 * 60 * 60);
        
        $files = glob($this->logDir . '/*.log*');
        
        foreach ($files as $file) {
            if (filemtime($file) < $cutoffTime) {
                unlink($file);
            }
        }
    }
    
    /**
     * Show user-friendly error message
     */
    private function showUserFriendlyError() {
        // Generate unique error ID for tracking
        $errorId = uniqid('err_', true);
        
        // Log the error ID for correlation
        $this->logCustomError("User-friendly error displayed", 'INFO', ['error_id' => $errorId]);
        
        if (!headers_sent()) {
            http_response_code(500);
            header('Content-Type: application/json');
        }
        
        if ($this->isProduction) {
            $response = [
                'error' => true,
                'message' => 'An internal server error occurred. Please try again later.',
                'error_id' => $errorId
            ];
        } else {
            // In development, show more details
            $response = [
                'error' => true,
                'message' => 'An error occurred during processing.',
                'error_id' => $errorId,
                'debug' => 'Check error logs for details'
            ];
        }
        
        echo json_encode($response);
    }
    
    /**
     * Get error type string from error code
     * 
     * @param int $errorCode PHP error code
     * @return string Error type string
     */
    private function getErrorType($errorCode) {
        $errorTypes = [
            E_ERROR => 'E_ERROR',
            E_WARNING => 'E_WARNING',
            E_PARSE => 'E_PARSE',
            E_NOTICE => 'E_NOTICE',
            E_CORE_ERROR => 'E_CORE_ERROR',
            E_CORE_WARNING => 'E_CORE_WARNING',
            E_COMPILE_ERROR => 'E_COMPILE_ERROR',
            E_COMPILE_WARNING => 'E_COMPILE_WARNING',
            E_USER_ERROR => 'E_USER_ERROR',
            E_USER_WARNING => 'E_USER_WARNING',
            E_USER_NOTICE => 'E_USER_NOTICE',
            E_STRICT => 'E_STRICT',
            E_RECOVERABLE_ERROR => 'E_RECOVERABLE_ERROR',
            E_DEPRECATED => 'E_DEPRECATED',
            E_USER_DEPRECATED => 'E_USER_DEPRECATED'
        ];
        
        return $errorTypes[$errorCode] ?? 'UNKNOWN_ERROR';
    }
    
    /**
     * Get current stack trace
     * 
     * @return string Stack trace as string
     */
    private function getStackTrace() {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        $traceString = '';
        
        foreach ($trace as $i => $frame) {
            $file = $frame['file'] ?? 'unknown';
            $line = $frame['line'] ?? 'unknown';
            $function = $frame['function'] ?? 'unknown';
            $class = isset($frame['class']) ? $frame['class'] . '::' : '';
            
            $traceString .= "#{$i} {$file}({$line}): {$class}{$function}()\n";
        }
        
        return $traceString;
    }
    
    /**
     * Get error statistics
     * 
     * @param int $days Number of days to analyze
     * @return array Error statistics
     */
    public function getErrorStats($days = 7) {
        $stats = [
            'total_errors' => 0,
            'error_types' => [],
            'daily_counts' => [],
            'top_files' => [],
            'security_events' => 0
        ];
        
        $startDate = date('Y-m-d', strtotime("-{$days} days"));
        
        for ($i = 0; $i < $days; $i++) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $logFile = $this->logDir . "/error_{$date}.log";
            $securityFile = $this->logDir . "/security_{$date}.log";
            
            if (file_exists($logFile)) {
                $this->analyzeLogFile($logFile, $stats);
            }
            
            if (file_exists($securityFile)) {
                $this->analyzeSecurityLogFile($securityFile, $stats);
            }
        }
        
        // Sort and limit results
        arsort($stats['error_types']);
        arsort($stats['top_files']);
        $stats['top_files'] = array_slice($stats['top_files'], 0, 10, true);
        
        return $stats;
    }
    
    /**
     * Analyze log file for statistics
     * 
     * @param string $logFile Log file path
     * @param array &$stats Statistics array to update
     */
    private function analyzeLogFile($logFile, &$stats) {
        $handle = fopen($logFile, 'r');
        if (!$handle) return;
        
        while (($line = fgets($handle)) !== false) {
            $data = json_decode($line, true);
            if (!$data) continue;
            
            $stats['total_errors']++;
            
            $type = $data['type'] ?? 'UNKNOWN';
            $stats['error_types'][$type] = ($stats['error_types'][$type] ?? 0) + 1;
            
            $file = basename($data['file'] ?? 'unknown');
            $stats['top_files'][$file] = ($stats['top_files'][$file] ?? 0) + 1;
            
            $date = substr($data['timestamp'] ?? '', 0, 10);
            $stats['daily_counts'][$date] = ($stats['daily_counts'][$date] ?? 0) + 1;
        }
        
        fclose($handle);
    }
    
    /**
     * Analyze security log file for statistics
     * 
     * @param string $logFile Security log file path
     * @param array &$stats Statistics array to update
     */
    private function analyzeSecurityLogFile($logFile, &$stats) {
        $handle = fopen($logFile, 'r');
        if (!$handle) return;
        
        while (($line = fgets($handle)) !== false) {
            $data = json_decode($line, true);
            if (!$data) continue;
            
            $stats['security_events']++;
        }
        
        fclose($handle);
    }
}
?>


<?php

class Security {
    private static $sessionKeyCSRF = 'csrf_token';
    private static $sessionKeyAttempts = 'login_attempts';
    private static $sessionKeyLockout = 'lockout_time';
    private static $maxAttempts = 5;
    private static $lockoutTime = 900; // 15 minutos

    /**
     * Sanitiza input de dados
     */
    public static function sanitizeInput($input, $type = 'string') {
        if (is_array($input)) {
            return array_map(function($item) use ($type) {
                return self::sanitizeInput($item, $type);
            }, $input);
        }

        switch ($type) {
            case 'email':
                return filter_var(trim($input), FILTER_SANITIZE_EMAIL);
            case 'int':
                return filter_var($input, FILTER_SANITIZE_NUMBER_INT);
            case 'float':
                return filter_var($input, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            case 'url':
                return filter_var($input, FILTER_SANITIZE_URL);
            case 'string':
            default:
                return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
        }
    }

    /**
     * Gera token CSRF
     */
    public static function generateCSRFToken() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION[self::$sessionKeyCSRF])) {
            $_SESSION[self::$sessionKeyCSRF] = bin2hex(random_bytes(32));
        }
        
        return $_SESSION[self::$sessionKeyCSRF];
    }

    /**
     * Verifica token CSRF
     */
    public static function verifyCSRFToken($token) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        return isset($_SESSION[self::$sessionKeyCSRF]) && 
               hash_equals($_SESSION[self::$sessionKeyCSRF], $token);
    }

    /**
     * Rate limiting para login
     */
    public static function checkLoginAttempts() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION[self::$sessionKeyAttempts])) {
            $_SESSION[self::$sessionKeyAttempts] = 0;
        }

        if ($_SESSION[self::$sessionKeyAttempts] >= self::$maxAttempts) {
            if (!isset($_SESSION[self::$sessionKeyLockout])) {
                $_SESSION[self::$sessionKeyLockout] = time();
            }
            
            if (time() - $_SESSION[self::$sessionKeyLockout] < self::$lockoutTime) {
                $remainingTime = self::$lockoutTime - (time() - $_SESSION[self::$sessionKeyLockout]);
                return [
                    'blocked' => true,
                    'remainingTime' => $remainingTime,
                    'message' => "Muitas tentativas de login. Tente novamente em " . ceil($remainingTime / 60) . " minutos."
                ];
            } else {
                // Reset após lockout
                $_SESSION[self::$sessionKeyAttempts] = 0;
                unset($_SESSION[self::$sessionKeyLockout]);
            }
        }

        return ['blocked' => false];
    }

    /**
     * Incrementa tentativas de login
     */
    public static function incrementLoginAttempts() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION[self::$sessionKeyAttempts])) {
            $_SESSION[self::$sessionKeyAttempts] = 0;
        }
        
        $_SESSION[self::$sessionKeyAttempts]++;
    }

    /**
     * Reset tentativas após login bem-sucedido
     */
    public static function resetLoginAttempts() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $_SESSION[self::$sessionKeyAttempts] = 0;
        unset($_SESSION[self::$sessionKeyLockout]);
    }

    /**
     * Headers de segurança
     */
    public static function setSecurityHeaders() {
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        header('X-XSS-Protection: 1; mode=block');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Content-Security-Policy: default-src \'self\' https:; script-src \'self\' \'unsafe-inline\' https://cdnjs.cloudflare.com https://fonts.googleapis.com; style-src \'self\' \'unsafe-inline\' https://cdnjs.cloudflare.com https://fonts.googleapis.com; img-src \'self\' data: https:; font-src \'self\' https://fonts.gstatic.com;');
    }

    /**
     * Log de auditoria
     */
    public static function logActivity($action, $details = '', $userId = null) {
        $logFile = __DIR__ . '/../logs/audit.log';
        $logDir = dirname($logFile);
        
        if (!file_exists($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        $timestamp = date('Y-m-d H:i:s');
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        $userId = $userId ?? ($_SESSION['user_id'] ?? 'anonymous');
        
        $logEntry = "[$timestamp] User: $userId | IP: $ip | Action: $action | Details: $details | UserAgent: $userAgent" . PHP_EOL;
        
        file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }
}

?>
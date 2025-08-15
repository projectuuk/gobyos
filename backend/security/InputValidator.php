<?php
/**
 * InputValidator Class
 * 
 * Provides comprehensive input validation and sanitization methods
 * to protect against SQL injection, XSS, and other input-based attacks.
 * 
 * @author Manus AI
 * @version 1.0
 */

class InputValidator {
    
    /**
     * Sanitize string input to prevent XSS attacks
     * 
     * @param string $input The input string to sanitize
     * @param bool $allowHtml Whether to allow basic HTML tags (default: false)
     * @return string Sanitized string
     */
    public static function sanitizeString($input, $allowHtml = false) {
        if (!is_string($input)) {
            return '';
        }
        
        // Remove null bytes
        $input = str_replace(chr(0), '', $input);
        
        // Trim whitespace
        $input = trim($input);
        
        if ($allowHtml) {
            // Allow only safe HTML tags
            $allowedTags = '<p><br><strong><em><u><h1><h2><h3><h4><h5><h6><ul><ol><li><a><img>';
            $input = strip_tags($input, $allowedTags);
            
            // Remove dangerous attributes
            $input = preg_replace('/(<[^>]+)(on\w+\s*=\s*["\'][^"\']*["\'])/i', '$1', $input);
            $input = preg_replace('/(<[^>]+)(javascript\s*:)/i', '$1', $input);
        } else {
            // Convert special characters to HTML entities
            $input = htmlspecialchars($input, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }
        
        return $input;
    }
    
    /**
     * Validate and sanitize email address
     * 
     * @param string $email Email address to validate
     * @return string|false Sanitized email or false if invalid
     */
    public static function validateEmail($email) {
        $email = filter_var($email, FILTER_SANITIZE_EMAIL);
        
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $email;
        }
        
        return false;
    }
    
    /**
     * Validate and sanitize integer input
     * 
     * @param mixed $input Input to validate as integer
     * @param int $min Minimum allowed value (optional)
     * @param int $max Maximum allowed value (optional)
     * @return int|false Validated integer or false if invalid
     */
    public static function validateInteger($input, $min = null, $max = null) {
        $int = filter_var($input, FILTER_VALIDATE_INT);
        
        if ($int === false) {
            return false;
        }
        
        if ($min !== null && $int < $min) {
            return false;
        }
        
        if ($max !== null && $int > $max) {
            return false;
        }
        
        return $int;
    }
    
    /**
     * Validate URL
     * 
     * @param string $url URL to validate
     * @return string|false Validated URL or false if invalid
     */
    public static function validateUrl($url) {
        $url = filter_var($url, FILTER_SANITIZE_URL);
        
        if (filter_var($url, FILTER_VALIDATE_URL)) {
            return $url;
        }
        
        return false;
    }
    
    /**
     * Validate slug (URL-friendly string)
     * 
     * @param string $slug Slug to validate
     * @return string|false Validated slug or false if invalid
     */
    public static function validateSlug($slug) {
        $slug = trim($slug);
        $slug = strtolower($slug);
        
        // Remove special characters and replace spaces with hyphens
        $slug = preg_replace('/[^a-z0-9\-_]/', '', $slug);
        $slug = preg_replace('/[\-_]+/', '-', $slug);
        $slug = trim($slug, '-_');
        
        if (empty($slug) || strlen($slug) > 200) {
            return false;
        }
        
        return $slug;
    }
    
    /**
     * Validate phone number
     * 
     * @param string $phone Phone number to validate
     * @return string|false Validated phone number or false if invalid
     */
    public static function validatePhone($phone) {
        // Remove all non-numeric characters except + and spaces
        $phone = preg_replace('/[^0-9+\s\-\(\)]/', '', $phone);
        $phone = trim($phone);
        
        // Basic phone number validation (10-15 digits)
        if (preg_match('/^[\+]?[0-9\s\-\(\)]{10,15}$/', $phone)) {
            return $phone;
        }
        
        return false;
    }
    
    /**
     * Validate date in Y-m-d format
     * 
     * @param string $date Date string to validate
     * @return string|false Validated date or false if invalid
     */
    public static function validateDate($date) {
        $dateTime = DateTime::createFromFormat('Y-m-d', $date);
        
        if ($dateTime && $dateTime->format('Y-m-d') === $date) {
            return $date;
        }
        
        return false;
    }
    
    /**
     * Validate and sanitize textarea content
     * 
     * @param string $content Content to validate
     * @param int $maxLength Maximum allowed length
     * @param bool $allowHtml Whether to allow HTML tags
     * @return string|false Validated content or false if invalid
     */
    public static function validateTextarea($content, $maxLength = 10000, $allowHtml = true) {
        if (!is_string($content)) {
            return false;
        }
        
        $content = self::sanitizeString($content, $allowHtml);
        
        if (strlen($content) > $maxLength) {
            return false;
        }
        
        return $content;
    }
    
    /**
     * Validate file upload
     * 
     * @param array $file $_FILES array element
     * @param array $allowedTypes Allowed MIME types
     * @param int $maxSize Maximum file size in bytes
     * @return array|false File info array or false if invalid
     */
    public static function validateFileUpload($file, $allowedTypes = [], $maxSize = 5242880) { // 5MB default
        if (!isset($file['error']) || is_array($file['error'])) {
            return false;
        }
        
        // Check for upload errors
        switch ($file['error']) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_NO_FILE:
                return false;
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                return false;
            default:
                return false;
        }
        
        // Check file size
        if ($file['size'] > $maxSize) {
            return false;
        }
        
        // Check MIME type
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);
        
        if (!empty($allowedTypes) && !in_array($mimeType, $allowedTypes)) {
            return false;
        }
        
        return [
            'name' => self::sanitizeString($file['name']),
            'type' => $mimeType,
            'size' => $file['size'],
            'tmp_name' => $file['tmp_name']
        ];
    }
    
    /**
     * Generate secure random token
     * 
     * @param int $length Token length
     * @return string Random token
     */
    public static function generateSecureToken($length = 32) {
        return bin2hex(random_bytes($length));
    }
    
    /**
     * Validate CSRF token
     * 
     * @param string $token Token to validate
     * @param string $sessionToken Token from session
     * @return bool True if valid, false otherwise
     */
    public static function validateCSRFToken($token, $sessionToken) {
        return hash_equals($sessionToken, $token);
    }
    
    /**
     * Rate limiting check
     * 
     * @param string $identifier Unique identifier (IP, user ID, etc.)
     * @param int $maxAttempts Maximum attempts allowed
     * @param int $timeWindow Time window in seconds
     * @return bool True if within limits, false if exceeded
     */
    public static function checkRateLimit($identifier, $maxAttempts = 5, $timeWindow = 300) {
        $cacheFile = sys_get_temp_dir() . '/rate_limit_' . md5($identifier);
        $currentTime = time();
        
        if (file_exists($cacheFile)) {
            $data = json_decode(file_get_contents($cacheFile), true);
            
            // Clean old attempts
            $data['attempts'] = array_filter($data['attempts'], function($timestamp) use ($currentTime, $timeWindow) {
                return ($currentTime - $timestamp) < $timeWindow;
            });
            
            if (count($data['attempts']) >= $maxAttempts) {
                return false;
            }
            
            $data['attempts'][] = $currentTime;
        } else {
            $data = ['attempts' => [$currentTime]];
        }
        
        file_put_contents($cacheFile, json_encode($data));
        return true;
    }
}
?>


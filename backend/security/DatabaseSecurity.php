<?php
/**
 * DatabaseSecurity Class
 * 
 * Provides secure database operations using prepared statements
 * and other security best practices to prevent SQL injection.
 * 
 * @author Manus AI
 * @version 1.0
 */

class DatabaseSecurity {
    private $pdo;
    private $logFile;
    
    public function __construct($pdo, $logFile = null) {
        $this->pdo = $pdo;
        $this->logFile = $logFile ?: __DIR__ . '/../logs/database_security.log';
        
        // Ensure log directory exists
        $logDir = dirname($this->logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
    }
    
    /**
     * Execute a secure SELECT query with prepared statements
     * 
     * @param string $query SQL query with placeholders
     * @param array $params Parameters to bind
     * @return array|false Query results or false on failure
     */
    public function secureSelect($query, $params = []) {
        try {
            $stmt = $this->pdo->prepare($query);
            
            if (!$stmt) {
                $this->logSecurityEvent('PREPARE_FAILED', $query);
                return false;
            }
            
            $result = $stmt->execute($params);
            
            if (!$result) {
                $this->logSecurityEvent('EXECUTE_FAILED', $query, $params);
                return false;
            }
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            $this->logSecurityEvent('SQL_ERROR', $query, $params, $e->getMessage());
            return false;
        }
    }
    
    /**
     * Execute a secure INSERT query with prepared statements
     * 
     * @param string $table Table name
     * @param array $data Associative array of column => value
     * @return int|false Last insert ID or false on failure
     */
    public function secureInsert($table, $data) {
        try {
            // Validate table name (prevent SQL injection in table name)
            if (!$this->isValidTableName($table)) {
                $this->logSecurityEvent('INVALID_TABLE_NAME', $table);
                return false;
            }
            
            $columns = array_keys($data);
            $placeholders = ':' . implode(', :', $columns);
            $columnList = implode(', ', $columns);
            
            $query = "INSERT INTO `{$table}` ({$columnList}) VALUES ({$placeholders})";
            
            $stmt = $this->pdo->prepare($query);
            
            if (!$stmt) {
                $this->logSecurityEvent('PREPARE_FAILED', $query);
                return false;
            }
            
            // Bind parameters with proper data types
            foreach ($data as $column => $value) {
                $paramType = $this->getParamType($value);
                $stmt->bindValue(":{$column}", $value, $paramType);
            }
            
            $result = $stmt->execute();
            
            if (!$result) {
                $this->logSecurityEvent('INSERT_FAILED', $query, $data);
                return false;
            }
            
            return $this->pdo->lastInsertId();
            
        } catch (PDOException $e) {
            $this->logSecurityEvent('SQL_ERROR', $query ?? '', $data, $e->getMessage());
            return false;
        }
    }
    
    /**
     * Execute a secure UPDATE query with prepared statements
     * 
     * @param string $table Table name
     * @param array $data Associative array of column => value
     * @param array $where WHERE conditions as associative array
     * @return bool True on success, false on failure
     */
    public function secureUpdate($table, $data, $where) {
        try {
            // Validate table name
            if (!$this->isValidTableName($table)) {
                $this->logSecurityEvent('INVALID_TABLE_NAME', $table);
                return false;
            }
            
            if (empty($where)) {
                $this->logSecurityEvent('UPDATE_WITHOUT_WHERE', $table);
                return false;
            }
            
            $setClause = [];
            foreach (array_keys($data) as $column) {
                $setClause[] = "`{$column}` = :{$column}";
            }
            
            $whereClause = [];
            foreach (array_keys($where) as $column) {
                $whereClause[] = "`{$column}` = :where_{$column}";
            }
            
            $query = "UPDATE `{$table}` SET " . implode(', ', $setClause) . 
                     " WHERE " . implode(' AND ', $whereClause);
            
            $stmt = $this->pdo->prepare($query);
            
            if (!$stmt) {
                $this->logSecurityEvent('PREPARE_FAILED', $query);
                return false;
            }
            
            // Bind data parameters
            foreach ($data as $column => $value) {
                $paramType = $this->getParamType($value);
                $stmt->bindValue(":{$column}", $value, $paramType);
            }
            
            // Bind where parameters
            foreach ($where as $column => $value) {
                $paramType = $this->getParamType($value);
                $stmt->bindValue(":where_{$column}", $value, $paramType);
            }
            
            $result = $stmt->execute();
            
            if (!$result) {
                $this->logSecurityEvent('UPDATE_FAILED', $query, array_merge($data, $where));
                return false;
            }
            
            return true;
            
        } catch (PDOException $e) {
            $this->logSecurityEvent('SQL_ERROR', $query ?? '', array_merge($data, $where), $e->getMessage());
            return false;
        }
    }
    
    /**
     * Execute a secure DELETE query with prepared statements
     * 
     * @param string $table Table name
     * @param array $where WHERE conditions as associative array
     * @return bool True on success, false on failure
     */
    public function secureDelete($table, $where) {
        try {
            // Validate table name
            if (!$this->isValidTableName($table)) {
                $this->logSecurityEvent('INVALID_TABLE_NAME', $table);
                return false;
            }
            
            if (empty($where)) {
                $this->logSecurityEvent('DELETE_WITHOUT_WHERE', $table);
                return false;
            }
            
            $whereClause = [];
            foreach (array_keys($where) as $column) {
                $whereClause[] = "`{$column}` = :{$column}";
            }
            
            $query = "DELETE FROM `{$table}` WHERE " . implode(' AND ', $whereClause);
            
            $stmt = $this->pdo->prepare($query);
            
            if (!$stmt) {
                $this->logSecurityEvent('PREPARE_FAILED', $query);
                return false;
            }
            
            // Bind parameters
            foreach ($where as $column => $value) {
                $paramType = $this->getParamType($value);
                $stmt->bindValue(":{$column}", $value, $paramType);
            }
            
            $result = $stmt->execute();
            
            if (!$result) {
                $this->logSecurityEvent('DELETE_FAILED', $query, $where);
                return false;
            }
            
            return true;
            
        } catch (PDOException $e) {
            $this->logSecurityEvent('SQL_ERROR', $query ?? '', $where, $e->getMessage());
            return false;
        }
    }
    
    /**
     * Validate table name to prevent SQL injection
     * 
     * @param string $tableName Table name to validate
     * @return bool True if valid, false otherwise
     */
    private function isValidTableName($tableName) {
        // Allow only alphanumeric characters and underscores
        return preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $tableName);
    }
    
    /**
     * Get appropriate PDO parameter type for a value
     * 
     * @param mixed $value Value to check
     * @return int PDO parameter type
     */
    private function getParamType($value) {
        if (is_int($value)) {
            return PDO::PARAM_INT;
        } elseif (is_bool($value)) {
            return PDO::PARAM_BOOL;
        } elseif (is_null($value)) {
            return PDO::PARAM_NULL;
        } else {
            return PDO::PARAM_STR;
        }
    }
    
    /**
     * Log security events
     * 
     * @param string $event Event type
     * @param string $query SQL query
     * @param array $params Parameters
     * @param string $error Error message
     */
    private function logSecurityEvent($event, $query = '', $params = [], $error = '') {
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'event' => $event,
            'query' => $query,
            'params' => $params,
            'error' => $error,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ];
        
        $logLine = json_encode($logEntry) . PHP_EOL;
        file_put_contents($this->logFile, $logLine, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Escape string for LIKE queries
     * 
     * @param string $string String to escape
     * @return string Escaped string
     */
    public function escapeLikeString($string) {
        return str_replace(['%', '_'], ['\%', '\_'], $string);
    }
    
    /**
     * Begin transaction
     * 
     * @return bool True on success, false on failure
     */
    public function beginTransaction() {
        try {
            return $this->pdo->beginTransaction();
        } catch (PDOException $e) {
            $this->logSecurityEvent('TRANSACTION_BEGIN_FAILED', '', [], $e->getMessage());
            return false;
        }
    }
    
    /**
     * Commit transaction
     * 
     * @return bool True on success, false on failure
     */
    public function commit() {
        try {
            return $this->pdo->commit();
        } catch (PDOException $e) {
            $this->logSecurityEvent('TRANSACTION_COMMIT_FAILED', '', [], $e->getMessage());
            return false;
        }
    }
    
    /**
     * Rollback transaction
     * 
     * @return bool True on success, false on failure
     */
    public function rollback() {
        try {
            return $this->pdo->rollback();
        } catch (PDOException $e) {
            $this->logSecurityEvent('TRANSACTION_ROLLBACK_FAILED', '', [], $e->getMessage());
            return false;
        }
    }
}
?>


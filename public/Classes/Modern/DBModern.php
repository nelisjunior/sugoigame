<?php

declare(strict_types=1);

namespace SugoiGame;

use mysqli;
use Exception;
use SugoiGame\DB_Result;

/**
 * Class DBModern - Modernized for PHP 8.x
 * 
 * Modern database wrapper with improved error handling, type safety, and prepared statements
 * 
 * @package SugoiGame
 */
class DBModern
{
    private mysqli $instance;

    /**
     * Database connection configuration
     */
    public function __construct(
        private readonly string $host = DB_HOST,
        private readonly string $user = DB_USER,
        private readonly string $password = DB_PASS,
        private readonly string $database = DB_NAME,
        private readonly string $charset = 'utf8mb4'
    ) {
        $this->connect();
    }

    /**
     * Establish database connection with proper error handling
     */
    private function connect(): void
    {
        if (!extension_loaded("mysqli")) {
            throw new Exception("MySQLi extension is required but not loaded.");
        }

        // Enable error reporting for MySQLi
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        try {
            $this->instance = new mysqli(
                $this->host, 
                $this->user, 
                $this->password, 
                $this->database
            );
            
            // Set charset to utf8mb4 for full UTF-8 support
            $this->instance->set_charset($this->charset);
            
            // Set strict mode for better data integrity
            $this->instance->query("SET sql_mode = 'STRICT_TRANS_TABLES,NO_ZERO_DATE,NO_ZERO_IN_DATE,ERROR_FOR_DIVISION_BY_ZERO'");
            
        } catch (\mysqli_sql_exception $e) {
            throw new Exception(
                sprintf("Database connection failed: %s", $e->getMessage()),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Execute a simple query (SELECT, INSERT, UPDATE, DELETE)
     */
    public function query(string $sql): \mysqli_result|bool
    {
        try {
            $result = $this->instance->query($sql);
            if ($result === false) {
                throw new Exception("Query failed: " . $this->instance->error);
            }
            return $result;
        } catch (\mysqli_sql_exception $e) {
            throw new Exception(
                sprintf("Query execution failed: %s. SQL: %s", $e->getMessage(), $sql),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Execute prepared statement with parameters
     */
    public function run(string $sql, ?string $types = null, array|string|null $params = null): DB_Result
    {
        try {
            $stmt = $this->instance->prepare($sql);
            if ($stmt === false) {
                throw new Exception("Prepare failed: " . $this->instance->error);
            }

            // Bind parameters if provided
            if ($types !== null && $params !== null) {
                // Ensure params is an array
                $paramArray = is_array($params) ? $params : [$params];
                
                // Validate parameter count matches type string length
                if (strlen($types) !== count($paramArray)) {
                    throw new Exception(
                        sprintf("Parameter count mismatch. Expected %d, got %d", 
                        strlen($types), count($paramArray))
                    );
                }

                $stmt->bind_param($types, ...$paramArray);
            }

            $stmt->execute();
            $stmt->store_result();
            
            return new DB_Result($stmt);
            
        } catch (\mysqli_sql_exception $e) {
            throw new Exception(
                sprintf("Prepared statement execution failed: %s. SQL: %s", $e->getMessage(), $sql),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Execute multiple queries in a transaction
     */
    public function transaction(callable $callback): mixed
    {
        $this->instance->begin_transaction();
        
        try {
            $result = $callback($this);
            $this->instance->commit();
            return $result;
        } catch (Exception $e) {
            $this->instance->rollback();
            throw $e;
        }
    }

    /**
     * Get last inserted ID
     */
    public function insertId(): int
    {
        return $this->instance->insert_id;
    }

    /**
     * Get number of affected rows from last operation
     */
    public function affectedRows(): int
    {
        return $this->instance->affected_rows;
    }

    /**
     * Escape string for safe SQL usage (prefer prepared statements)
     */
    public function escape(string $string): string
    {
        return $this->instance->real_escape_string($string);
    }

    /**
     * Get database connection info
     */
    public function getConnectionInfo(): array
    {
        return [
            'host_info' => $this->instance->host_info,
            'server_info' => $this->instance->server_info,
            'client_info' => $this->instance->client_info,
            'character_set' => $this->instance->character_set_name(),
            'protocol_version' => $this->instance->protocol_version,
        ];
    }

    /**
     * Check if connection is still alive
     */
    public function ping(): bool
    {
        return $this->instance->ping();
    }

    /**
     * Close database connection
     */
    public function close(): void
    {
        $this->instance->close();
    }

    /**
     * Get underlying MySQLi instance for advanced operations
     */
    public function getInstance(): mysqli
    {
        return $this->instance;
    }

    /**
     * Magic method to proxy calls to underlying MySQLi instance
     */
    public function __call(string $name, array $args): mixed
    {
        if (method_exists($this->instance, $name)) {
            return $this->instance->$name(...$args);
        }
        
        throw new Exception("Method '{$name}' does not exist on MySQLi instance");
    }

    /**
     * Destructor to ensure connection is closed
     */
    public function __destruct()
    {
        if (isset($this->instance)) {
            $this->instance->close();
        }
    }
}
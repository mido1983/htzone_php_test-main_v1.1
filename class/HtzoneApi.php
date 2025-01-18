<?php

class HtzoneApi {
    private $db;
    private $base_url = 'https://storeapi.htzone.co.il/ext/O2zfcVu2t8gOB6nzSfFBu4joDYPH7s';
    private $cache_duration = 3600; // 1 hour cache duration

    public function __construct() {
        try {
            $this->db = new SQLite3(__DIR__ . '/../database/database.sqlite');
            $this->db->exec('PRAGMA foreign_keys = ON');
            $this->initDatabase();
        } catch (Exception $e) {
            $this->logError('Database initialization failed: ' . $e->getMessage());
            throw new Exception("Database connection failed: " . $e->getMessage());
        }
    }

    private function initDatabase() {
        // Create categories table
        $this->db->exec('
            CREATE TABLE IF NOT EXISTS categories (
                id INTEGER PRIMARY KEY,
                name TEXT NOT NULL,
                description TEXT,
                parent_id INTEGER,
                last_updated INTEGER,
                FOREIGN KEY (parent_id) REFERENCES categories(id)
            )
        ');

        // Create items table
        $this->db->exec('
            CREATE TABLE IF NOT EXISTS items (
                id INTEGER PRIMARY KEY,
                name TEXT NOT NULL,
                description TEXT,
                price REAL NOT NULL,
                brand TEXT,
                category_id INTEGER,
                image_url TEXT,
                stock INTEGER,
                last_updated INTEGER,
                FOREIGN KEY (category_id) REFERENCES categories(id)
            )
        ');

        // Create cache table for API responses
        $this->db->exec('
            CREATE TABLE IF NOT EXISTS api_cache (
                endpoint TEXT PRIMARY KEY,
                response TEXT,
                timestamp INTEGER
            )
        ');
    }

    private function makeApiRequest($endpoint) {
        $ch = curl_init($this->base_url . $endpoint);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FAILONERROR => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            $this->logError("API request failed: $error");
            throw new Exception("API request failed: $error");
        }

        curl_close($ch);

        if ($httpCode !== 200) {
            $this->logError("API returned non-200 status code: $httpCode");
            throw new Exception("API request failed with status code: $httpCode");
        }

        return $response;
    }

    private function getCachedResponse($endpoint) {
        $stmt = $this->db->prepare('
            SELECT response, timestamp 
            FROM api_cache 
            WHERE endpoint = :endpoint
        ');
        $stmt->bindValue(':endpoint', $endpoint, SQLITE3_TEXT);
        $result = $stmt->execute();
        
        if ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            if (time() - $row['timestamp'] < $this->cache_duration) {
                return $row['response'];
            }
        }
        return null;
    }

    private function cacheResponse($endpoint, $response) {
        $stmt = $this->db->prepare('
            INSERT OR REPLACE INTO api_cache (endpoint, response, timestamp)
            VALUES (:endpoint, :response, :timestamp)
        ');
        $stmt->bindValue(':endpoint', $endpoint, SQLITE3_TEXT);
        $stmt->bindValue(':response', $response, SQLITE3_TEXT);
        $stmt->bindValue(':timestamp', time(), SQLITE3_INTEGER);
        $stmt->execute();
    }

    public function fetchAndStoreCategories() {
        try {
            // Try to get cached response
            $cached = $this->getCachedResponse('/categories');
            $response = $cached ?: $this->makeApiRequest('/categories');
            
            if (!$cached) {
                $this->cacheResponse('/categories', $response);
            }

            $categories = json_decode($response, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception("Invalid JSON response");
            }

            $this->db->exec('BEGIN TRANSACTION');

            $stmt = $this->db->prepare('
                INSERT OR REPLACE INTO categories 
                (id, name, description, parent_id, last_updated)
                VALUES (:id, :name, :description, :parent_id, :last_updated)
            ');

            foreach ($categories as $category) {
                $stmt->bindValue(':id', $category['id'], SQLITE3_INTEGER);
                $stmt->bindValue(':name', $category['name'], SQLITE3_TEXT);
                $stmt->bindValue(':description', $category['description'] ?? '', SQLITE3_TEXT);
                $stmt->bindValue(':parent_id', $category['parent_id'] ?? null, SQLITE3_INTEGER);
                $stmt->bindValue(':last_updated', time(), SQLITE3_INTEGER);
                $stmt->execute();
            }

            $this->db->exec('COMMIT');
            return true;

        } catch (Exception $e) {
            $this->db->exec('ROLLBACK');
            $this->logError('Failed to fetch and store categories: ' . $e->getMessage());
            throw $e;
        }
    }

    public function fetchAndStoreItems($categoryId = null) {
        try {
            $endpoint = '/items' . ($categoryId ? "/$categoryId" : '');
            
            // Try to get cached response
            $cached = $this->getCachedResponse($endpoint);
            $response = $cached ?: $this->makeApiRequest($endpoint);
            
            if (!$cached) {
                $this->cacheResponse($endpoint, $response);
            }

            $items = json_decode($response, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception("Invalid JSON response");
            }

            $this->db->exec('BEGIN TRANSACTION');

            $stmt = $this->db->prepare('
                INSERT OR REPLACE INTO items 
                (id, name, description, price, brand, category_id, image_url, stock, last_updated)
                VALUES 
                (:id, :name, :description, :price, :brand, :category_id, :image_url, :stock, :last_updated)
            ');

            foreach ($items as $item) {
                $stmt->bindValue(':id', $item['id'], SQLITE3_INTEGER);
                $stmt->bindValue(':name', $item['name'], SQLITE3_TEXT);
                $stmt->bindValue(':description', $item['description'] ?? '', SQLITE3_TEXT);
                $stmt->bindValue(':price', $item['price'], SQLITE3_FLOAT);
                $stmt->bindValue(':brand', $item['brand'] ?? '', SQLITE3_TEXT);
                $stmt->bindValue(':category_id', $item['category_id'], SQLITE3_INTEGER);
                $stmt->bindValue(':image_url', $item['image_url'] ?? '', SQLITE3_TEXT);
                $stmt->bindValue(':stock', $item['stock'] ?? 0, SQLITE3_INTEGER);
                $stmt->bindValue(':last_updated', time(), SQLITE3_INTEGER);
                $stmt->execute();
            }

            $this->db->exec('COMMIT');
            return true;

        } catch (Exception $e) {
            $this->db->exec('ROLLBACK');
            $this->logError('Failed to fetch and store items: ' . $e->getMessage());
            throw $e;
        }
    }

    private function logError($message) {
        $logFile = __DIR__ . '/../logs/api_errors.log';
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[$timestamp] $message\n";
        
        if (!is_dir(dirname($logFile))) {
            mkdir(dirname($logFile), 0777, true);
        }
        
        file_put_contents($logFile, $logMessage, FILE_APPEND);
    }

    public function __destruct() {
        if ($this->db) {
            $this->db->close();
        }
    }
}

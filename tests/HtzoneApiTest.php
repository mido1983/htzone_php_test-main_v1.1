<?php

use PHPUnit\Framework\TestCase;

class HtzoneApiTest extends TestCase
{
    private $api;
    private $db;

    protected function setUp(): void
    {
        $this->api = new HtzoneApi();
        $this->db = new SQLite3(':memory:');
    }

    protected function tearDown(): void
    {
        $this->db->close();
    }

    public function testDatabaseInitialization()
    {
        // Check if tables exist
        $tables = [
            'categories' => [
                'id', 'name', 'description', 'parent_id', 'last_updated'
            ],
            'items' => [
                'id', 'name', 'description', 'price', 'brand', 
                'category_id', 'image_url', 'stock', 'last_updated'
            ],
            'api_cache' => [
                'endpoint', 'response', 'timestamp'
            ]
        ];

        foreach ($tables as $table => $columns) {
            $result = $this->db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='$table'");
            $this->assertNotFalse($result);
            $this->assertNotNull($result->fetchArray());

            // Check columns
            $schema = $this->db->query("PRAGMA table_info($table)");
            $actualColumns = [];
            while ($row = $schema->fetchArray(SQLITE3_ASSOC)) {
                $actualColumns[] = $row['name'];
            }
            foreach ($columns as $column) {
                $this->assertContains($column, $actualColumns);
            }
        }
    }

    public function testApiCaching()
    {
        $endpoint = '/test-endpoint';
        $response = '{"test": "data"}';
        
        // Store in cache
        $stmt = $this->db->prepare('
            INSERT INTO api_cache (endpoint, response, timestamp)
            VALUES (:endpoint, :response, :timestamp)
        ');
        $stmt->bindValue(':endpoint', $endpoint, SQLITE3_TEXT);
        $stmt->bindValue(':response', $response, SQLITE3_TEXT);
        $stmt->bindValue(':timestamp', time(), SQLITE3_INTEGER);
        $stmt->execute();

        // Check if cached
        $result = $this->db->query("
            SELECT response FROM api_cache 
            WHERE endpoint = '$endpoint'
        ");
        $row = $result->fetchArray(SQLITE3_ASSOC);
        
        $this->assertEquals($response, $row['response']);
    }

    public function testCategoryStorage()
    {
        $category = [
            'id' => 1,
            'name' => 'Test Category',
            'description' => 'Test Description'
        ];

        $stmt = $this->db->prepare('
            INSERT INTO categories (id, name, description, last_updated)
            VALUES (:id, :name, :description, :last_updated)
        ');
        $stmt->bindValue(':id', $category['id'], SQLITE3_INTEGER);
        $stmt->bindValue(':name', $category['name'], SQLITE3_TEXT);
        $stmt->bindValue(':description', $category['description'], SQLITE3_TEXT);
        $stmt->bindValue(':last_updated', time(), SQLITE3_INTEGER);
        
        $this->assertTrue($stmt->execute() !== false);

        $result = $this->db->query('SELECT * FROM categories WHERE id = 1');
        $row = $result->fetchArray(SQLITE3_ASSOC);
        
        $this->assertEquals($category['name'], $row['name']);
        $this->assertEquals($category['description'], $row['description']);
    }

    public function testItemStorage()
    {
        $item = [
            'id' => 1,
            'name' => 'Test Item',
            'description' => 'Test Description',
            'price' => 99.99,
            'brand' => 'Test Brand',
            'category_id' => 1,
            'image_url' => 'http://example.com/image.jpg',
            'stock' => 10
        ];

        $stmt = $this->db->prepare('
            INSERT INTO items 
            (id, name, description, price, brand, category_id, image_url, stock, last_updated)
            VALUES 
            (:id, :name, :description, :price, :brand, :category_id, :image_url, :stock, :last_updated)
        ');
        
        foreach ($item as $key => $value) {
            $type = is_int($value) ? SQLITE3_INTEGER : 
                   (is_float($value) ? SQLITE3_FLOAT : SQLITE3_TEXT);
            $stmt->bindValue(":$key", $value, $type);
        }
        $stmt->bindValue(':last_updated', time(), SQLITE3_INTEGER);
        
        $this->assertTrue($stmt->execute() !== false);

        $result = $this->db->query('SELECT * FROM items WHERE id = 1');
        $row = $result->fetchArray(SQLITE3_ASSOC);
        
        foreach ($item as $key => $value) {
            $this->assertEquals($value, $row[$key]);
        }
    }
} 
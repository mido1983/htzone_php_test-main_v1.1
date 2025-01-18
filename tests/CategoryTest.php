<?php

use PHPUnit\Framework\TestCase;

class CategoryTest extends TestCase
{
    private $db;
    private $category;

    protected function setUp(): void
    {
        $this->db = new SQLite3(':memory:');
        
        // Create tables
        $this->db->exec('
            CREATE TABLE categories (
                id INTEGER PRIMARY KEY,
                name TEXT NOT NULL,
                description TEXT,
                parent_id INTEGER,
                last_updated INTEGER,
                FOREIGN KEY (parent_id) REFERENCES categories(id)
            )
        ');
        
        $this->db->exec('
            CREATE TABLE items (
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

        $this->category = new Category($this->db);
        
        // Insert test data
        $this->seedTestData();
    }

    protected function tearDown(): void
    {
        $this->db->close();
    }

    private function seedTestData()
    {
        // Insert test categories
        $categories = [
            [1, 'Electronics', 'Electronic devices'],
            [2, 'Clothing', 'Fashion items'],
            [3, 'Books', 'Reading materials']
        ];

        foreach ($categories as [$id, $name, $description]) {
            $this->db->exec("
                INSERT INTO categories (id, name, description, last_updated)
                VALUES ($id, '$name', '$description', " . time() . ")
            ");
        }

        // Insert test items
        $items = [
            [1, 'Laptop', 'Test laptop', 999.99, 'Brand1', 1],
            [2, 'T-Shirt', 'Cotton shirt', 29.99, 'Brand2', 2],
            [3, 'Novel', 'Fiction book', 19.99, 'Brand3', 3]
        ];

        foreach ($items as [$id, $name, $description, $price, $brand, $categoryId]) {
            $this->db->exec("
                INSERT INTO items (id, name, description, price, brand, category_id, last_updated)
                VALUES ($id, '$name', '$description', $price, '$brand', $categoryId, " . time() . ")
            ");
        }
    }

    public function testGetAll()
    {
        $categories = $this->category->getAll();
        
        $this->assertCount(3, $categories);
        $this->assertEquals('Electronics', $categories[0]['name']);
        $this->assertEquals('Clothing', $categories[1]['name']);
        $this->assertEquals('Books', $categories[2]['name']);
    }

    public function testGetTopCategories()
    {
        $topCategories = $this->category->getTopCategories(2);
        
        $this->assertCount(2, $topCategories);
        $this->assertArrayHasKey('item_count', $topCategories[0]);
    }

    public function testGetById()
    {
        $category = $this->category->getById(1);
        
        $this->assertNotNull($category);
        $this->assertEquals('Electronics', $category['name']);
        $this->assertEquals('Electronic devices', $category['description']);
    }

    public function testGetItems()
    {
        $items = $this->category->getItems(1, 10, 0);
        
        $this->assertCount(1, $items);
        $this->assertEquals('Laptop', $items[0]['name']);
        $this->assertEquals(999.99, $items[0]['price']);
    }

    public function testGetItemCount()
    {
        $count = $this->category->getItemCount(1);
        $this->assertEquals(1, $count);
    }

    public function testSearch()
    {
        $results = $this->category->search('elect');
        
        $this->assertCount(1, $results);
        $this->assertEquals('Electronics', $results[0]['name']);
    }

    public function testGetNonExistentCategory()
    {
        $category = $this->category->getById(999);
        $this->assertFalse($category);
    }

    public function testGetItemsWithPagination()
    {
        // Add more items to test pagination
        for ($i = 4; $i <= 15; $i++) {
            $this->db->exec("
                INSERT INTO items (id, name, description, price, category_id, last_updated)
                VALUES ($i, 'Item $i', 'Description $i', 9.99, 1, " . time() . ")
            ");
        }

        $items = $this->category->getItems(1, 5, 5); // Get second page of 5 items
        
        $this->assertCount(5, $items);
        $this->assertEquals('Item 9', $items[4]['name']);
    }

    public function testSearchWithNoResults()
    {
        $results = $this->category->search('nonexistent');
        $this->assertEmpty($results);
    }
} 
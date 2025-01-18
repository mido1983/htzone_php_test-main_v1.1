<?php

use PHPUnit\Framework\TestCase;

class ItemTest extends TestCase
{
    private $db;
    private $item;

    protected function setUp(): void
    {
        $this->db = new SQLite3(':memory:');
        
        // Create tables
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
                last_updated INTEGER
            )
        ');

        $this->item = new Item($this->db);
        
        // Insert test data
        $this->seedTestData();
    }

    protected function tearDown(): void
    {
        $this->db->close();
    }

    private function seedTestData()
    {
        $items = [
            [1, 'Laptop', 'Gaming laptop', 1299.99, 'Brand1', 1, 10],
            [2, 'Phone', 'Smartphone', 699.99, 'Brand2', 1, 20],
            [3, 'Tablet', 'Android tablet', 499.99, 'Brand1', 1, 15],
            [4, 'Watch', 'Smart watch', 299.99, 'Brand2', 2, 25]
        ];

        foreach ($items as [$id, $name, $description, $price, $brand, $categoryId, $stock]) {
            $this->db->exec("
                INSERT INTO items 
                (id, name, description, price, brand, category_id, stock, last_updated)
                VALUES 
                ($id, '$name', '$description', $price, '$brand', $categoryId, $stock, " . time() . ")
            ");
        }
    }

    public function testGetItems()
    {
        $result = $this->item->getItems();
        
        $this->assertArrayHasKey('items', $result);
        $this->assertArrayHasKey('total', $result);
        $this->assertCount(4, $result['items']);
    }

    public function testGetItemsWithFilters()
    {
        $filters = [
            'category' => 1,
            'price_min' => 600,
            'price_max' => 1500,
            'brand' => 'Brand1'
        ];

        $result = $this->item->getItems(1, 10, $filters);
        
        $this->assertCount(1, $result['items']);
        $this->assertEquals('Laptop', $result['items'][0]['name']);
    }

    public function testGetItemsWithSorting()
    {
        $sort = [
            'field' => 'price',
            'direction' => 'DESC'
        ];

        $result = $this->item->getItems(1, 10, [], $sort);
        
        $this->assertEquals('Laptop', $result['items'][0]['name']);
        $this->assertEquals('Watch', $result['items'][3]['name']);
    }

    public function testGetById()
    {
        $item = $this->item->getById(1);
        
        $this->assertNotNull($item);
        $this->assertEquals('Laptop', $item['name']);
        $this->assertEquals(1299.99, $item['price']);
    }

    public function testGetBrands()
    {
        $brands = $this->item->getBrands();
        
        $this->assertCount(2, $brands);
        $this->assertContains('Brand1', $brands);
        $this->assertContains('Brand2', $brands);
    }

    public function testGetPriceRange()
    {
        $range = $this->item->getPriceRange();
        
        $this->assertEquals(299.99, $range['min_price']);
        $this->assertEquals(1299.99, $range['max_price']);
    }

    public function testGetItemsWithPagination()
    {
        $result = $this->item->getItems(2, 2); // Get second page with 2 items per page
        
        $this->assertCount(2, $result['items']);
        $this->assertEquals(4, $result['total']);
        $this->assertEquals('Tablet', $result['items'][0]['name']);
    }

    public function testGetNonExistentItem()
    {
        $item = $this->item->getById(999);
        $this->assertFalse($item);
    }

    public function testGetItemsWithInvalidFilters()
    {
        $filters = [
            'price_min' => 2000, // Higher than any item price
            'price_max' => 3000
        ];

        $result = $this->item->getItems(1, 10, $filters);
        $this->assertEmpty($result['items']);
    }

    public function testGetItemsWithEmptyFilters()
    {
        $result = $this->item->getItems(1, 10, []);
        $this->assertCount(4, $result['items']);
    }

    public function testGetItemsWithPartialFilters()
    {
        $filters = [
            'price_min' => 500 // Only minimum price
        ];
        
        $result = $this->item->getItems(1, 10, $filters);
        $this->assertCount(3, $result['items']); // Should return items >= 500
    }

    public function testGetItemsWithInvalidSortField()
    {
        $sort = [
            'field' => 'invalid_field',
            'direction' => 'ASC'
        ];
        
        $result = $this->item->getItems(1, 10, [], $sort);
        // Should default to sorting by name
        $this->assertEquals('Laptop', $result['items'][0]['name']);
    }

    public function testGetItemsWithInvalidSortDirection()
    {
        $sort = [
            'field' => 'price',
            'direction' => 'INVALID'
        ];
        
        $result = $this->item->getItems(1, 10, [], $sort);
        // Should default to ASC
        $this->assertEquals('Watch', $result['items'][0]['name']);
    }

    public function testGetItemsWithNegativePage()
    {
        $result = $this->item->getItems(-1, 10);
        // Should default to page 1
        $this->assertCount(4, $result['items']);
    }

    public function testGetItemsWithZeroLimit()
    {
        $result = $this->item->getItems(1, 0);
        $this->assertEmpty($result['items']);
    }

    public function testGetItemsWithNegativeLimit()
    {
        $result = $this->item->getItems(1, -10);
        $this->assertEmpty($result['items']);
    }

    public function testGetBrandsWithNoItems()
    {
        // Clear all items
        $this->db->exec('DELETE FROM items');
        
        $brands = $this->item->getBrands();
        $this->assertEmpty($brands);
    }

    public function testGetPriceRangeWithNoItems()
    {
        // Clear all items
        $this->db->exec('DELETE FROM items');
        
        $range = $this->item->getPriceRange();
        $this->assertNull($range['min_price']);
        $this->assertNull($range['max_price']);
    }

    public function testGetItemsWithSpecialCharactersInBrand()
    {
        // Insert item with special characters in brand
        $this->db->exec("
            INSERT INTO items (id, name, description, price, brand, category_id, stock, last_updated)
            VALUES (5, 'Special Item', 'Test', 99.99, 'Brand & Co.', 1, 10, " . time() . ")
        ");
        
        $filters = ['brand' => 'Brand & Co.'];
        $result = $this->item->getItems(1, 10, $filters);
        
        $this->assertCount(1, $result['items']);
        $this->assertEquals('Special Item', $result['items'][0]['name']);
    }
} 
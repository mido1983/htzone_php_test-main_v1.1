<?php

class Category {
    private $db;

    public function __construct(SQLite3 $db) {
        $this->db = $db;
    }

    /**
     * Get all categories
     */
    public function getAll() {
        $result = $this->db->query('
            SELECT * FROM categories 
            ORDER BY name ASC
        ');

        $categories = [];
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $categories[] = $row;
        }
        return $categories;
    }

    /**
     * Get top 3 categories (for carousels)
     */
    public function getTopCategories($limit = 3) {
        $stmt = $this->db->prepare('
            SELECT c.*, COUNT(i.id) as item_count 
            FROM categories c 
            LEFT JOIN items i ON c.id = i.category_id 
            GROUP BY c.id 
            ORDER BY item_count DESC 
            LIMIT :limit
        ');
        
        $stmt->bindValue(':limit', $limit, SQLITE3_INTEGER);
        $result = $stmt->execute();

        $categories = [];
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $categories[] = $row;
        }
        return $categories;
    }

    /**
     * Get category by ID
     */
    public function getById($id) {
        $stmt = $this->db->prepare('
            SELECT * FROM categories 
            WHERE id = :id
        ');
        
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
        $result = $stmt->execute();
        
        return $result->fetchArray(SQLITE3_ASSOC);
    }

    /**
     * Get items in a category
     */
    public function getItems($categoryId, $limit = 10, $offset = 0) {
        $stmt = $this->db->prepare('
            SELECT i.* 
            FROM items i 
            WHERE i.category_id = :category_id 
            ORDER BY i.name ASC 
            LIMIT :limit OFFSET :offset
        ');
        
        $stmt->bindValue(':category_id', $categoryId, SQLITE3_INTEGER);
        $stmt->bindValue(':limit', $limit, SQLITE3_INTEGER);
        $stmt->bindValue(':offset', $offset, SQLITE3_INTEGER);
        
        $result = $stmt->execute();
        
        $items = [];
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $items[] = $row;
        }
        return $items;
    }

    /**
     * Get category item count
     */
    public function getItemCount($categoryId) {
        $stmt = $this->db->prepare('
            SELECT COUNT(*) as count 
            FROM items 
            WHERE category_id = :category_id
        ');
        
        $stmt->bindValue(':category_id', $categoryId, SQLITE3_INTEGER);
        $result = $stmt->execute();
        $row = $result->fetchArray(SQLITE3_ASSOC);
        
        return $row['count'];
    }

    /**
     * Search categories
     */
    public function search($query) {
        $stmt = $this->db->prepare('
            SELECT * FROM categories 
            WHERE name LIKE :query 
            OR description LIKE :query 
            ORDER BY name ASC
        ');
        
        $searchTerm = '%' . $query . '%';
        $stmt->bindValue(':query', $searchTerm, SQLITE3_TEXT);
        
        $result = $stmt->execute();
        
        $categories = [];
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $categories[] = $row;
        }
        return $categories;
    }
}

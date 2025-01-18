<?php

class Item {
    private $db;

    public function __construct(SQLite3 $db) {
        $this->db = $db;
    }

    /**
     * Get items with filtering and sorting
     */
    public function getItems($page = 1, $limit = 10, $filters = [], $sort = []) {
        $offset = ($page - 1) * $limit;
        $where = ['1=1'];
        $params = [];
        
        // Apply filters
        if (!empty($filters['category'])) {
            $where[] = 'category_id = :category_id';
            $params[':category_id'] = $filters['category'];
        }
        
        if (!empty($filters['price_min'])) {
            $where[] = 'price >= :price_min';
            $params[':price_min'] = $filters['price_min'];
        }
        
        if (!empty($filters['price_max'])) {
            $where[] = 'price <= :price_max';
            $params[':price_max'] = $filters['price_max'];
        }
        
        if (!empty($filters['brand'])) {
            $where[] = 'brand LIKE :brand';
            $params[':brand'] = '%' . $filters['brand'] . '%';
        }

        // Build query
        $sql = 'SELECT * FROM items WHERE ' . implode(' AND ', $where);
        
        // Add sorting
        if (!empty($sort['field']) && !empty($sort['direction'])) {
            $allowedFields = ['name', 'price', 'brand'];
            $field = in_array($sort['field'], $allowedFields) ? $sort['field'] : 'name';
            $direction = strtoupper($sort['direction']) === 'DESC' ? 'DESC' : 'ASC';
            $sql .= " ORDER BY $field $direction";
        } else {
            $sql .= ' ORDER BY name ASC';
        }

        $sql .= ' LIMIT :limit OFFSET :offset';
        
        // Prepare and execute query
        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', $limit, SQLITE3_INTEGER);
        $stmt->bindValue(':offset', $offset, SQLITE3_INTEGER);
        
        $result = $stmt->execute();
        
        $items = [];
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $items[] = $row;
        }
        
        return [
            'items' => $items,
            'total' => $this->getTotalCount($where, $params),
            'page' => $page,
            'limit' => $limit
        ];
    }

    /**
     * Get total count for pagination
     */
    private function getTotalCount($where, $params) {
        $sql = 'SELECT COUNT(*) as count FROM items WHERE ' . implode(' AND ', $where);
        $stmt = $this->db->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $result = $stmt->execute();
        $row = $result->fetchArray(SQLITE3_ASSOC);
        return $row['count'];
    }

    /**
     * Get item by ID
     */
    public function getById($id) {
        $stmt = $this->db->prepare('
            SELECT * FROM items 
            WHERE id = :id
        ');
        
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
        $result = $stmt->execute();
        
        return $result->fetchArray(SQLITE3_ASSOC);
    }

    /**
     * Get unique brands for filtering
     */
    public function getBrands() {
        $result = $this->db->query('
            SELECT DISTINCT brand 
            FROM items 
            WHERE brand IS NOT NULL 
            ORDER BY brand ASC
        ');
        
        $brands = [];
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $brands[] = $row['brand'];
        }
        return $brands;
    }

    /**
     * Get price range for filtering
     */
    public function getPriceRange() {
        $result = $this->db->query('
            SELECT MIN(price) as min_price, MAX(price) as max_price 
            FROM items
        ');
        
        return $result->fetchArray(SQLITE3_ASSOC);
    }
}

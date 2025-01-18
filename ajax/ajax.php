<?php
require_once '../class/HtzoneApi.php';
require_once '../class/Category.php';
require_once '../class/Item.php';

header('Content-Type: application/json');

$response = [
    'status' => 'error',
    'message' => '',
    'data' => null
];

if (!isset($_POST['act'])) {
    $response['message'] = 'No action specified';
    echo json_encode($response);
    exit;
}

try {
    // Initialize API and database connection
    $api = new HtzoneApi();
    $db = new SQLite3(__DIR__ . '/../database/database.sqlite');
    $db->exec('PRAGMA foreign_keys = ON');
    
    // Initialize models
    $category = new Category($db);
    $item = new Item($db);

    switch ($_POST['act']) {
        case 'getItems':
            $page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
            $limit = isset($_POST['limit']) ? (int)$_POST['limit'] : 10;
            
            $filters = [
                'category' => $_POST['category'] ?? null,
                'price_min' => $_POST['price_min'] ?? null,
                'price_max' => $_POST['price_max'] ?? null,
                'brand' => $_POST['brand'] ?? null
            ];
            
            $sort = [
                'field' => $_POST['sort_field'] ?? 'name',
                'direction' => $_POST['sort_direction'] ?? 'ASC'
            ];

            $data = $item->getItems($page, $limit, $filters, $sort);
            $response['status'] = 'success';
            $response['data'] = $data;
            break;

        case 'getCategories':
            $data = $category->getAll();
            $response['status'] = 'success';
            $response['data'] = $data;
            break;

        case 'getTopCategories':
            $limit = isset($_POST['limit']) ? (int)$_POST['limit'] : 3;
            $data = $category->getTopCategories($limit);
            $response['status'] = 'success';
            $response['data'] = $data;
            break;

        case 'getCategoryItems':
            if (!isset($_POST['category_id'])) {
                throw new Exception('Category ID is required');
            }
            
            $categoryId = (int)$_POST['category_id'];
            $limit = isset($_POST['limit']) ? (int)$_POST['limit'] : 10;
            $offset = isset($_POST['offset']) ? (int)$_POST['offset'] : 0;
            
            $data = [
                'category' => $category->getById($categoryId),
                'items' => $category->getItems($categoryId, $limit, $offset),
                'total' => $category->getItemCount($categoryId)
            ];
            
            $response['status'] = 'success';
            $response['data'] = $data;
            break;

        case 'searchCategories':
            if (!isset($_POST['query'])) {
                throw new Exception('Search query is required');
            }
            
            $data = $category->search($_POST['query']);
            $response['status'] = 'success';
            $response['data'] = $data;
            break;

        case 'getBrands':
            $data = $item->getBrands();
            $response['status'] = 'success';
            $response['data'] = $data;
            break;

        case 'getPriceRange':
            $data = $item->getPriceRange();
            $response['status'] = 'success';
            $response['data'] = $data;
            break;

        default:
            throw new Exception('Invalid action');
    }

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    
    // Log the error
    error_log(sprintf(
        "[%s] Ajax Error: %s\nStack trace:\n%s",
        date('Y-m-d H:i:s'),
        $e->getMessage(),
        $e->getTraceAsString()
    ));
    
} finally {
    if (isset($db)) {
        $db->close();
    }
}

echo json_encode($response);

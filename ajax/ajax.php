<?php
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



switch ($_POST['act']) {
    case 'getItems':
        $page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
        $limit = isset($_POST['limit']) ? (int)$_POST['limit'] : 10;
        $filters = [
            'category' => $_POST['category'],
            'price_min' => $_POST['price_min'],
            'price_max' => $_POST['price_max'],
            'brand' => $_POST['brand']
        ];
        
        $sort = [
            'field' => $_POST['sort_field'],
            'direction' => $_POST['sort_direction']
        ];

        //implement the getItems method in Item class
        $data = $item->getItems($page, $limit, $filters, $sort);

        $response['status'] = 'success';
        $response['data'] = $data;
        break;

    case 'getCategories':
        //implement the getCategories method in Item class
        $data = $item->getCategories();

        $response['status'] = 'success';
        $response['data'] = $data;
        break;

    default:
        $response['message'] = 'Invalid action';
}


echo json_encode($response);

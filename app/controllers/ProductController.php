<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../config/dbcon.php';
require_once __DIR__ . '/../models/Product.php';

class ProductController {
    private $productModel;

    public function __construct($db) {
        $this->productModel = new Product($db);
    }

    public function indexActive() {
        try {
            $stmt = $this->productModel->readActiveProducts();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("indexActive error: " . $e->getMessage());
            return [];
        }
    }

    public function getProductsByCategory($category_id) {
        try {
            return $this->productModel->getProductsByCategory($category_id);
        } catch (Exception $e) {
            error_log("getProductsByCategory error: " . $e->getMessage());
            return [];
        }
    }

    

    public function checkAvailability() {
        try {
            $stmt = $this->productModel->readActiveProducts();
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $stockData = [];
            foreach ($products as $product) {
                $stockData[] = [
                    'id' => $product['id'],
                    'name' => $product['name'],
                    'stock' => $product['stock'],
                    'status' => $product['status']
                ];
            }
            
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'products' => $stockData
            ]);
            exit();
            
        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
            exit();
        }
    }
}

if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    // Handle request
    if (isset($_GET['action']) && $_GET['action'] === 'check_availability') {
        $database = new Database();
        $db = $database->getConnection();
        $controller = new ProductController($db);
        $controller->checkAvailability();
    }

    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
    exit();
}


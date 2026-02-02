<?php
require_once __DIR__ . '/../models/Category.php';

class CategoryController {
    private $model;
    
    public function __construct($db) {
        $this->model = new Category($db);
    }
    
    // Get all categories
    public function index() {
        return $this->model->readAll();
    }
    
    // Get single category
    public function show($id) {
        return $this->model->readOne($id);
    }
    
    // Create category
    public function create($name, $description, $status) {
        return $this->model->create($name, $description, $status);
    }
    
    // Update category
    public function update($id, $name, $description, $status) {
        return $this->model->update($id, $name, $description, $status);
    }
    
    // Delete category
    public function delete($id) {
        return $this->model->delete($id);
    }
    
    // Count products in category
    public function countProducts($category_id) {
        return $this->model->countProducts($category_id);
    }

    // Get category by ID - FIXED (using the model's readOne method)
    public function getCategoryById($id) {
        return $this->model->readOne($id);
    }
}
?>
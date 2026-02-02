<?php
require_once __DIR__ . '/../models/Notification.php';

class NotificationController {
    private $model;

    public function __construct($db) {
        $this->model = new Notification($db);
    }

    // Normal index for rendering the notifications page
    public function index($userId) {
        $notifications = $this->model->getUserNotifications($userId);
        require __DIR__ . '/../views/notifications/index.php';
    }

    // ✅ JSON endpoint for AJAX polling
    public function getUserNotifications($userId) {
        header('Content-Type: application/json');

        try {
            $notifications = $this->model->getUserNotifications($userId);
            echo json_encode([
                'success' => true,
                'notifications' => $notifications
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error fetching notifications',
                'error'   => $e->getMessage()
            ]);
        }
    }
}

<?php
class Notification {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Get notifications for a user (join orders + products)
    public function getUserNotifications($userId) {
        $sql = "SELECT o.id AS order_id, o.status, o.created_at,
                       p.name AS product_name, p.image, p.price
                FROM orders o
                INNER JOIN products p ON o.product_id = p.id
                WHERE o.user_id = :user_id
                ORDER BY o.created_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":user_id", $userId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

<?php
session_start();
require_once 'config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !isset($_GET['conversation_id'])) {
    echo json_encode(['error' => 'Invalid request']);
    exit;
}

try {
    $stmt = $conn->prepare("
        SELECT message, response, created_at as timestamp
        FROM chat_history 
        WHERE user_id = ? AND conversation_id = ?
        ORDER BY created_at ASC
    ");
    
    $stmt->bind_param("is", $_SESSION['user_id'], $_GET['conversation_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $messages = [];
    while ($row = $result->fetch_assoc()) {
        $messages[] = [
            'message' => $row['message'],
            'response' => $row['response'],
            'timestamp' => $row['timestamp']
        ];
    }
    
    echo json_encode($messages);
} catch (Exception $e) {
    error_log($e->getMessage());
    echo json_encode(['error' => 'Failed to load messages']);
}
?>

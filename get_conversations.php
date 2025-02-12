<?php
session_start();
require_once 'config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

try {
    // Get conversations with their latest messages
    $stmt = $conn->prepare("
        SELECT 
            ch1.conversation_id as id,
            ch1.message as preview,
            ch1.created_at as timestamp
        FROM chat_history ch1
        INNER JOIN (
            SELECT conversation_id, MAX(created_at) as max_created_at
            FROM chat_history
            WHERE user_id = ?
            GROUP BY conversation_id
        ) ch2 
        ON ch1.conversation_id = ch2.conversation_id 
        AND ch1.created_at = ch2.max_created_at
        WHERE ch1.user_id = ?
        ORDER BY ch1.created_at DESC
        LIMIT 50
    ");
    
    $stmt->bind_param("ii", $_SESSION['user_id'], $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $conversations = [];
    while ($row = $result->fetch_assoc()) {
        // Truncate preview text if too long
        $preview = strlen($row['preview']) > 50 
            ? substr($row['preview'], 0, 50) . '...' 
            : $row['preview'];
            
        $conversations[] = [
            'id' => $row['id'],
            'preview' => $preview,
            'timestamp' => $row['timestamp']
        ];
    }
    
    echo json_encode($conversations);
} catch (Exception $e) {
    error_log($e->getMessage());
    echo json_encode(['error' => 'Failed to load conversations']);
}
?>

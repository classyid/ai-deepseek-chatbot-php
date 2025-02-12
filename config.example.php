<?php
// config.php
define('DB_HOST', 'localhost');
define('DB_USER', 'db_user');
define('DB_PASS', 'db_pass');
define('DB_NAME', 'db_name');
define('OLLAMA_HOST', 'http://<IP/HOST-OLLAMA>:11434');

// Ollama model settings
define('OLLAMA_MODEL', 'deepseek-r1');
define('OLLAMA_TEMPERATURE', 0.7);
define('OLLAMA_TOP_K', 40);
define('OLLAMA_TOP_P', 0.95);

// Create database connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if tables exist
$tableCheckQuery = "SHOW TABLES LIKE 'users'";
$result = $conn->query($tableCheckQuery);

if ($result->num_rows == 0) {
    // Initialize database tables
    $createTables = "
    CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );

    CREATE TABLE IF NOT EXISTS chat_history (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        conversation_id VARCHAR(50) NOT NULL,
        message TEXT NOT NULL,
        response TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id)
    );";

    // Execute each query separately
    $queries = explode(';', $createTables);
    foreach($queries as $query) {
        $query = trim($query);
        if (!empty($query)) {
            $conn->query($query);
        }
    }
}

// Functions for chat history
function saveChat($userId, $conversationId, $message, $response) {
    global $conn;
    $stmt = $conn->prepare("INSERT INTO chat_history (user_id, conversation_id, message, response) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $userId, $conversationId, $message, $response);
    return $stmt->execute();
}

function getConversationHistory($userId, $conversationId) {
    global $conn;
    $stmt = $conn->prepare("SELECT message, response FROM chat_history WHERE user_id = ? AND conversation_id = ? ORDER BY created_at ASC");
    $stmt->bind_param("is", $userId, $conversationId);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}
?>

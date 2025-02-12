<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Cek apakah ini request API
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    // Cek autentikasi
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'error' => 'Not authenticated']);
        exit;
    }
    
    if ($_POST['action'] === 'generate' && isset($_POST['prompt'])) {
        $conversationId = $_POST['conversation_id'] ?? session_id();
        
        try {
            // Get custom settings if provided
            $settings = isset($_POST['settings']) ? json_decode($_POST['settings'], true) : [];
            
            // Merge default settings with custom settings
            $modelOptions = array_merge([
                'temperature' => OLLAMA_TEMPERATURE,
                'top_k' => OLLAMA_TOP_K,
                'top_p' => OLLAMA_TOP_P
            ], $settings);
            
            // Log request untuk debugging
            error_log("Sending request to Ollama with prompt: " . $_POST['prompt']);
            
            // Prepare API request to Ollama
            $data = array(
                'model' => OLLAMA_MODEL,
                'prompt' => $_POST['prompt'],
                'stream' => false,
                'options' => $modelOptions
            );
            
            $ch = curl_init(OLLAMA_HOST . '/api/generate');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
            
            // Log untuk debug
            error_log("Sending request to: " . OLLAMA_HOST . '/api/generate');
            error_log("Request data: " . json_encode($data));
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            
            // Log response untuk debugging
            error_log("Ollama Response Code: " . $httpCode);
            error_log("Ollama Response: " . $response);
            
            if (curl_errno($ch)) {
                throw new Exception('Curl error: ' . curl_error($ch));
            }
            
            curl_close($ch);
            
            if ($httpCode === 200) {
                $responseData = json_decode($response, true);
                if ($responseData && isset($responseData['response'])) {
                    $cleanResponse = preg_replace('/<think>.*?<\/think>/s', '', $responseData['response']);
                    $cleanResponse = trim(preg_replace('/\s+/', ' ', $cleanResponse));
                    
                    // Save to chat history
                    saveChat($_SESSION['user_id'], $conversationId, $_POST['prompt'], $cleanResponse);
                    
                    echo json_encode(['success' => true, 'response' => $cleanResponse]);
                    exit;
                } else {
                    throw new Exception('Invalid response format from Ollama');
                }
            } else {
                throw new Exception('API request failed with status code: ' . $httpCode);
            }
            
        } catch (Exception $e) {
            error_log("Error in chat.php: " . $e->getMessage());
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            exit;
        }
    }
    
    echo json_encode(['success' => false, 'error' => 'Invalid action']);
    exit;
}

// Get user statistics
$userStats = $conn->prepare("
    SELECT 
        COUNT(DISTINCT conversation_id) as total_conversations,
        COUNT(*) as total_messages,
        MAX(created_at) as last_chat
    FROM chat_history 
    WHERE user_id = ?
");

if ($userStats) {
    $userStats->bind_param("i", $_SESSION['user_id']);
    $userStats->execute();
    $stats = $userStats->get_result()->fetch_assoc();
    $userStats->close();
} else {
    $stats = [
        'total_conversations' => 0,
        'total_messages' => 0,
        'last_chat' => null
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Chat Assistant - Dashboard</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #3b82f6;
            --secondary-color: #2563eb;
            --bg-color: #f8fafc;
            --text-color: #1e293b;
            --sidebar-width: 280px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, sans-serif;
            line-height: 1.6;
            color: var(--text-color);
            background: var(--bg-color);
            display: flex;
            height: 100vh;
        }

        .sidebar {
            width: var(--sidebar-width);
            background: white;
            border-right: 1px solid #e2e8f0;
            display: flex;
            flex-direction: column;
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            z-index: 10;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.05);
        }

        .sidebar-header {
            padding: 1.5rem;
            border-bottom: 1px solid #e2e8f0;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary-color);
        }

        .user-stats {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            padding: 0.75rem;
            font-size: 0.875rem;
        }

        .stat-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
        }

        .new-chat-btn {
            margin: 1rem;
            padding: 0.75rem;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            transition: all 0.2s;
        }

        .new-chat-btn:hover {
            background: var(--secondary-color);
        }

        .conversation-list {
            flex: 1;
            overflow-y: auto;
            padding: 1rem;
        }

        .conversation-item {
            padding: 0.75rem;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 0.5rem;
        }

        .conversation-item:hover {
            background: #f1f5f9;
        }

        .conversation-item.active {
            background: #e0e7ff;
            color: var(--primary-color);
        }

        .conversation-icon {
            width: 32px;
            height: 32px;
            background: var(--primary-color);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }

        .conversation-info {
            flex: 1;
            overflow: hidden;
        }

        .conversation-title {
            font-weight: 500;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .conversation-preview {
            font-size: 0.875rem;
            color: #64748b;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .main-content {
            flex: 1;
            margin-left: var(--sidebar-width);
            display: flex;
            flex-direction: column;
            height: 100vh;
        }

        .chat-header {
            padding: 1rem 2rem;
            background: white;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .chat-title {
            font-size: 1.25rem;
            font-weight: 600;
        }

        .chat-actions {
            display: flex;
            gap: 1rem;
        }

        .action-btn {
            padding: 0.5rem;
            border-radius: 8px;
            cursor: pointer;
            color: #64748b;
            transition: all 0.2s;
        }

        .action-btn:hover {
            color: var(--primary-color);
            background: #f1f5f9;
        }

        .chat-container {
            flex: 1;
            padding: 2rem;
            overflow-y: auto;
        }

        .message {
            max-width: 80%;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: flex-start;
            gap: 1rem;
        }

        .message.user-message {
            margin-left: auto;
            flex-direction: row-reverse;
        }

        .message-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: var(--primary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            flex-shrink: 0;
        }

        .message-content {
            background: white;
            padding: 1rem;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            position: relative;
        }

        .user-message .message-content {
            background: var(--primary-color);
            color: white;
        }

        .message-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
            color: #64748b;
        }

        .user-message .message-header {
            color: rgba(255, 255, 255, 0.8);
        }

        .message-text {
            white-space: pre-wrap;
            word-break: break-word;
        }

        .input-container {
            padding: 1.5rem 2rem;
            background: white;
            border-top: 1px solid #e2e8f0;
        }

        .input-group {
            display: flex;
            gap: 1rem;
            background: var(--bg-color);
            padding: 0.75rem;
            border-radius: 12px;
        }

        textarea {
            flex: 1;
            border: none;
            background: transparent;
            resize: none;
            padding: 0.5rem;
            font-family: inherit;
            font-size: 1rem;
            min-height: 40px;
            max-height: 200px;
        }

        textarea:focus {
            outline: none;
        }

        .send-btn {
            padding: 0.75rem 1.5rem;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.2s;
        }

        .send-btn:hover {
            background: var(--secondary-color);
        }

        .send-btn:disabled {
            background: #94a3b8;
            cursor: not-allowed;
        }

        .typing-indicator {
            padding: 0.75rem;
            color: #64748b;
            display: none;
            align-items: center;
            gap: 0.5rem;
            font-style: italic;
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .chat-header {
                padding: 1rem;
            }

            .message {
                max-width: 90%;
            }

            .input-container {
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="user-info">
                <div class="user-avatar">
                    <i class="fas fa-user"></i>
                </div>
                <div>
                    <div style="font-weight: 500;"><?php echo htmlspecialchars($_SESSION['username']); ?></div>
                    <div style="font-size: 0.875rem; opacity: 0.8;">Active Now</div>
                </div>
            </div>
            <div class="user-stats">
                <div class="stat-item">
                    <span>Total Chats</span>
                    <span><?php echo $stats['total_conversations']; ?></span>
                </div>
                <div class="stat-item">
                    <span>Messages</span>
                    <span><?php echo $stats['total_messages']; ?></span>
                </div>
                <div class="stat-item">
                    <span>Last Active</span>
                    <span><?php echo $stats['last_chat'] ? date('M d', strtotime($stats['last_chat'])) : 'Never'; ?></span>
                </div>
            </div>
        </div>

        <button class="new-chat-btn" onclick="startNewChat()">
            <i class="fas fa-plus"></i> New Chat
        </button>

        <div class="conversation-list" id="conversationList">
            <!-- Populated via JavaScript -->
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="chat-header">
            <div class="chat-title">
                <i class="fas fa-robot"></i> AI Chat Assistant
            </div>
            <div class="chat-actions">
                <button class="action-btn" onclick="toggleSidebar()" title="Toggle Sidebar">
                    <i class="fas fa-bars"></i>
                </button>
                <a href="logout.php" class="action-btn" title="Logout">
                    <i class="fas fa-sign-out-alt"></i>
                </a>
            </div>
        </div>

        <div class="chat-container" id="chatBox">
            <!-- Chat messages will be populated here -->
        </div>

        <div class="input-container">
            <div class="input-group">
                <textarea 
                    id="prompt" 
                    placeholder="Type your message here... (Press Enter to send, Shift+Enter for new line)"
                    rows="1"
                ></textarea>
               <button id="sendButton" class="send-btn" onclick="sendMessage()">
                    <i class="fas fa-paper-plane"></i>
                    Send
                </button>
            </div>
            <div id="typingIndicator" class="typing-indicator">
                <i class="fas fa-circle-notch fa-spin"></i>
                AI is thinking...
            </div>
        </div>
    </div>

    <script>
        let currentConversationId = null;
        const sidebar = document.getElementById('sidebar');
        const textarea = document.getElementById('prompt');
        const chatBox = document.getElementById('chatBox');

        // Auto-resize textarea
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });

        // Toggle sidebar on mobile
        function toggleSidebar() {
            sidebar.classList.toggle('show');
        }

        function formatTimestamp(timestamp) {
            const date = new Date(timestamp);
            return date.toLocaleString('en-US', {
                hour: 'numeric',
                minute: 'numeric',
                hour12: true,
                month: 'short',
                day: 'numeric'
            });
        }

        function appendMessage(content, isUser = false) {
            const messageDiv = document.createElement('div');
            messageDiv.className = `message ${isUser ? 'user-message' : ''}`;
            
            const avatar = document.createElement('div');
            avatar.className = 'message-avatar';
            avatar.innerHTML = `<i class="fas fa-${isUser ? 'user' : 'robot'}"></i>`;
            
            const messageContent = document.createElement('div');
            messageContent.className = 'message-content';
            
            const header = document.createElement('div');
            header.className = 'message-header';
            header.innerHTML = `
                <span>${isUser ? 'You' : 'AI Assistant'}</span>
                <span>${formatTimestamp(new Date())}</span>
            `;
            
            const text = document.createElement('div');
            text.className = 'message-text';
            text.textContent = content;
            
            messageContent.appendChild(header);
            messageContent.appendChild(text);
            
            messageDiv.appendChild(avatar);
            messageDiv.appendChild(messageContent);
            
            chatBox.appendChild(messageDiv);
            chatBox.scrollTop = chatBox.scrollHeight;
        }

        function startNewChat() {
            currentConversationId = Date.now().toString();
            chatBox.innerHTML = '';
            appendMessage("Hello! I'm your AI assistant. How can I help you today?");
            sidebar.classList.remove('show');
            
            // Reset textarea
            textarea.value = '';
            textarea.disabled = false;
            document.getElementById('sendButton').disabled = false;
            
            loadConversations();
        }
        
        function loadConversations() {
            fetch('get_conversations.php')
                .then(response => response.json())
                .then(data => {
                    const conversationList = document.getElementById('conversationList');
                    conversationList.innerHTML = '';
                    
                    if (Array.isArray(data)) {
                        data.forEach(conv => {
                            const div = document.createElement('div');
                            div.className = `conversation-item ${conv.id === currentConversationId ? 'active' : ''}`;
                            div.setAttribute('data-id', conv.id);
                            div.innerHTML = `
                                <div class="conversation-icon">
                                    <i class="fas fa-comments"></i>
                                </div>
                                <div class="conversation-info">
                                    <div class="conversation-title">Chat ${formatTimestamp(parseInt(conv.id))}</div>
                                    <div class="conversation-preview">${conv.preview || 'Start a new conversation'}</div>
                                </div>
                            `;
                            div.onclick = () => loadConversation(conv.id);
                            conversationList.appendChild(div);
                        });
                    }
                })
                .catch(error => console.error('Error loading conversations:', error));
        }
        
        function loadConversation(conversationId) {
            currentConversationId = conversationId;
            fetch(`get_messages.php?conversation_id=${conversationId}`)
                .then(response => response.json())
                .then(data => {
                    chatBox.innerHTML = '';
                    if (Array.isArray(data)) {
                        data.forEach(msg => {
                            appendMessage(msg.message, true);
                            appendMessage(msg.response);
                        });
                    }
                    
                    // Update active state in sidebar
                    document.querySelectorAll('.conversation-item').forEach(item => {
                        item.classList.toggle('active', item.getAttribute('data-id') === conversationId);
                    });
                    
                    // Close sidebar on mobile after selection
                    sidebar.classList.remove('show');
                })
                .catch(error => {
                    console.error('Error loading conversation:', error);
                    appendMessage('Error loading conversation. Please try again.');
                });
        }
        
        async function sendMessage() {
            const prompt = textarea.value.trim();
            if (!prompt) return;
            
            if (!currentConversationId) {
                currentConversationId = Date.now().toString();
            }

            textarea.value = '';
            textarea.style.height = 'auto';
            textarea.disabled = true;
            document.getElementById('sendButton').disabled = true;
            document.getElementById('typingIndicator').style.display = 'flex';

            appendMessage(prompt, true);

            try {
                // Model settings
                const modelSettings = {
                    temperature: 0.7,  // Bisa disesuaikan
                    top_k: 40,        // Bisa disesuaikan
                    top_p: 0.95       // Bisa disesuaikan
                };

                const formData = new FormData();
                formData.append('action', 'generate');
                formData.append('prompt', prompt);
                formData.append('conversation_id', currentConversationId);
                formData.append('settings', JSON.stringify(modelSettings));

                const response = await fetch('chat.php', {
                    method: 'POST',
                    body: formData
                });

                // Log untuk debugging
                console.log('Response status:', response.status);
                const responseText = await response.text();
                console.log('Response text:', responseText);

                let data;
                try {
                    data = JSON.parse(responseText);
                } catch (e) {
                    console.error('JSON parse error:', e);
                    throw new Error('Invalid response format');
                }
                
                if (!data.success) {
                    throw new Error(data.error || 'Unknown error occurred');
                }

                appendMessage(data.response);
                loadConversations(); // Refresh conversation list
            } catch (error) {
                console.error('Error:', error);
                appendMessage(`Error: ${error.message}`);
            } finally {
                textarea.disabled = false;
                document.getElementById('sendButton').disabled = false;
                document.getElementById('typingIndicator').style.display = 'none';
                textarea.focus();
            }
        }

        // Handle textarea input
        textarea.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage();
            }
        });

        // Initialize
        window.onload = () => {
            startNewChat();
            loadConversations();
        };
    </script>
</body>
</html>

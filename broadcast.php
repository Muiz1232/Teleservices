<?php

if (!isset($_GET['method'])) {
    echo "Error: Method parameter is missing.";
    exit;
}

// Admin ID and Admin Bot Token
$admin_chat_id = isset($_GET['admin']) ? $_GET['admin'] : null;
$admin_bot_token = isset($_GET['bot_token']) ? $_GET['bot_token'] : null;
$access_token = isset($_GET['access_token']) ? $_GET['access_token'] : null;
$method = $_GET['method'];
$file_id = isset($_GET['file_id']) ? $_GET['file_id'] : null;
$text = isset($_GET['text']) ? $_GET['text'] : null;
$parse_mode = isset($_GET['parse_mode']) ? $_GET['parse_mode'] : "HTML";
$disableWebPreview = isset($_GET['disableWebPreview']) ? $_GET['disableWebPreview'] : false;
$protectContent = isset($_GET['protectContent']) ? $_GET['protectContent'] : false;

// Connect to MySQL database
$servername = "sql312.infinityfree.com";
$username = "if0_36396334";
$password = "TQDkQe1ozBS5fF5";
$dbname = "if0_36396334_Broadcast";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create table to store message IDs if not exists
$sql_create_table = "CREATE TABLE IF NOT EXISTS message_ids (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    message_id INT(11) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql_create_table) === false) {
    echo "Error creating table: " . $conn->error;
}

// Function to store message ID in the database
function storeMessageId($conn, $message_id) {
    $sql = "INSERT INTO message_ids (message_id, created_at) VALUES ('$message_id', NOW())";
    if ($conn->query($sql) === true) {
        echo "Message ID stored successfully.\n";
    } else {
        echo "Error storing message ID: " . $conn->error;
    }
}

// Function to delete old message IDs from the database
function deleteOldMessageIds($conn) {
    $sql = "DELETE FROM message_ids WHERE created_at < DATE_SUB(NOW(), INTERVAL 7 DAY)";
    if ($conn->query($sql) === true) {
        echo "Old message IDs deleted successfully.\n";
    } else {
        echo "Error deleting old message IDs: " . $conn->error;
    }
}

// Retrieve bot token and user IDs based on access token
$sql = "SELECT bot_token, users FROM bot_info WHERE access_token = '$access_token'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $bot_token = $row['bot_token'];
    $users = json_decode($row['users'], true);

    // Get bot username
    $bot_username = getBotUsername($bot_token);

    $successful_deliveries = 0;
    $failed_deliveries = 0;
    $start_time = microtime(true);

    foreach ($users as $user_id) {
        $start_message_time = microtime(true);
        if ($file_id) {
            $broadcast_status = sendBroadcastWithFile($bot_token, $user_id, $text, $method, $file_id, $parse_mode, $disableWebPreview, $protectContent);
        } else {
            $broadcast_status = sendBroadcastMessage($bot_token, $user_id, $text, $parse_mode, $disableWebPreview, $protectContent);
        }
        $end_message_time = microtime(true);
        $message_duration = $end_message_time - $start_message_time;
        if ($broadcast_status) {
            $successful_deliveries++;
            echo "Message sent to user ID: $user_id. Duration: $message_duration seconds.\n";
        } else {
            $failed_deliveries++;
            echo "Failed to send message to user ID: $user_id.\n";
        }
    }

    $end_time = microtime(true);
    $total_duration = $end_time - $start_time;

    $broadcast_status_message = "<b>Broadcast status for bot @$bot_username:\nSuccessful deliveries: $successful_deliveries\nFailed deliveries: $failed_deliveries\nTotal time taken: $total_duration seconds</b>";

    sendTelegramMessage($admin_chat_id, $broadcast_status_message, $bot_token);
} else {
    // Send a warning message to admin using the admin bot token
    $warning_message = "<b>Warning: No bot token found for the given access token $access_token</b>";
    sendTelegramMessage($admin_chat_id, $warning_message, $admin_bot_token);
}

// Close MySQL connection
$conn->close();

// Function to get bot username
function getBotUsername($bot_token) {
    $api_url = "https://api.telegram.org/bot$bot_token/getMe";
    $response = file_get_contents($api_url);
    $data = json_decode($response, true);
    if (isset($data['result']['username'])) {
        return $data['result']['username'];
    }
    return "UnknownBot";
}

// Function to send a broadcast message with text only
function sendBroadcastMessage($bot_token, $user_id, $text, $parse_mode, $disableWebPreview, $protectContent) {
    $api_url = "https://api.telegram.org/bot$bot_token/sendMessage";
    $payload = array(
        'chat_id' => $user_id,
        'text' => $text,
        'parse_mode' => $parse_mode,
        'disable_web_page_preview' => $disableWebPreview,
        'protect_content' => $protectContent,
    );
    $headers = array(
        'Content-Type: application/json'
    );
    $payload_json = json_encode($payload);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload_json);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    if ($response === false) {
        echo "Error sending message: " . curl_error($ch);
        return false;
    }
    $data = json_decode($response, true);
    if (isset($data['result']['message_id'])) {
        storeMessageId($conn, $data['result']['message_id']);
    }
    curl_close($ch);
    return true;
}

// Function to send a broadcast message with file and optional text caption
function sendBroadcastWithFile($bot_token, $user_id, $text, $method, $file_id, $parse_mode, $disableWebPreview, $protectContent) {
    $api_url = "https://api.telegram.org/bot$bot_token/$method";
    $payload = array(
        'chat_id' => $user_id,
        'parse_mode' => $parse_mode,
        'protect_content' => $protectContent,
        'disable_web_page_preview' => $disableWebPreview,
    );
    if ($text) {
        $payload['caption'] = $text;
    }
    $payload[$method] = $file_id;
    $headers = array(
        'Content-Type: application/json'
    );
    $payload_json = json_encode($payload);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload_json);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    if ($response === false) {
        echo "Error sending message: " . curl_error($ch);
        return false;
    }
    $data = json_decode($response, true);
    if (isset($data['result']['message_id'])) {
        storeMessageId($conn, $data['result']['message_id']);
    }
    curl_close($ch);
    return true;
}

// Function to send a message to Telegram
function sendTelegramMessage($chat_id, $message, $bot_token = null) {
    if ($bot_token) {
        $api_url = "https://api.telegram.org/bot$bot_token/sendMessage";
    } else {
        global $admin_bot_token;
        $api_url = "https://api.telegram.org/bot$admin_bot_token/sendMessage";
    }
    $params = array(
        'chat_id' => $chat_id,
        'text' => $message,
        'parse_mode' => "HTML",
    );
    $headers = array(
        'Content-Type: application/json'
    );
    $payload_json = json_encode($params);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload_json);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    if ($response === false) {
        echo "Error sending message: " . curl_error($ch);
    }
    curl_close($ch);
}
?>
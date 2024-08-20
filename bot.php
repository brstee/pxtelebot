<?php

// Đọc nội dung của file config.txt
$config = parse_ini_file('config.txt', true);  // true để parse theo nhóm

// Lấy giá trị bot_token từ file config.txt
$bot_token = $config['bot_token'];

// URL API Telegram sử dụng bot_token
$apiURL = "https://api.telegram.org/bot$bot_token/";

// Lấy dữ liệu từ webhook
$update = file_get_contents('php://input');
$updateArray = json_decode($update, TRUE);

$chatId = $updateArray['message']['chat']['id'];
$message = $updateArray['message']['text'];

// Tách lệnh và các tham số
$commandParts = explode(' ', $message);

if ($commandParts[0] == '/add' && count($commandParts) == 5) {
    // Xử lý lệnh /add
    $pixel_name = $commandParts[1];
    $pixel_id = $commandParts[2];
    $bm_id = $commandParts[3];
    $pixel_token = $commandParts[4];

    $configFile = 'config.txt';
    $existingConfig = parse_ini_file($configFile, true);

    $existingConfig[$pixel_name] = [
        'pixel_id' => $pixel_id,
        'bm_id' => $bm_id,
        'pixel_token' => $pixel_token,
    ];

    $configData = "bot_token={$existingConfig['bot_token']}\n\n";
    foreach ($existingConfig as $section => $values) {
        if (is_array($values)) {
            $configData .= "[$section]\n";
            foreach ($values as $key => $value) {
                $configData .= "$key=$value\n";
            }
            $configData .= "\n";
        }
    }

    file_put_contents($configFile, $configData);

    sendMessage($chatId, "Pixel information for $pixel_name added/updated successfully!");
} elseif ($commandParts[0] == '/update' && count($commandParts) == 3) {
    // Xử lý lệnh /update
    $pixel_name = $commandParts[1];
    $new_pixel_token = $commandParts[2];

    if (isset($config[$pixel_name])) {
        $config[$pixel_name]['pixel_token'] = $new_pixel_token;

        $configData = "bot_token={$config['bot_token']}\n\n";
        foreach ($config as $section => $values) {
            if (is_array($values)) {
                $configData .= "[$section]\n";
                foreach ($values as $key => $value) {
                    $configData .= "$key=$value\n";
                }
                $configData .= "\n";
            }
        }

        file_put_contents('config.txt', $configData);

        sendMessage($chatId, "Pixel token for $pixel_name updated successfully!");
    } else {
        sendMessage($chatId, "Pixel '$pixel_name' does not exist.");
    }
} elseif ($commandParts[0] == '/remove' && count($commandParts) == 2) {
    // Xử lý lệnh /remove
    $pixel_name = $commandParts[1];

    if (isset($config[$pixel_name])) {
        unset($config[$pixel_name]);

        $configData = "bot_token={$config['bot_token']}\n\n";
        foreach ($config as $section => $values) {
            if (is_array($values)) {
                $configData .= "[$section]\n";
                foreach ($values as $key => $value) {
                    $configData .= "$key=$value\n";
                }
                $configData .= "\n";
            }
        }

        file_put_contents('config.txt', $configData);

        sendMessage($chatId, "Pixel '$pixel_name' removed successfully!");
    } else {
        sendMessage($chatId, "Pixel '$pixel_name' does not exist.");
    }
} elseif ($commandParts[0] == '/list') {
    // Xử lý lệnh /list
    $message = "List of Pixels:\n";
    foreach ($config as $pixel_name => $values) {
        if (is_array($values)) {
            $message .= "Pixel Name: $pixel_name\n";
            $message .= "Pixel ID: " . $values['pixel_id'] . "\n\n";
        }
    }
    sendMessage($chatId, $message);
}
elseif ($commandParts[0] == '/edit' && count($commandParts) == 3) {
    // Xử lý lệnh /edit
    $old_pixel_name = $commandParts[1];
    $new_pixel_name = $commandParts[2];

    if (isset($config[$old_pixel_name])) {
        // Đổi tên pixel bằng cách sao chép dữ liệu và xóa tên cũ
        $config[$new_pixel_name] = $config[$old_pixel_name];
        unset($config[$old_pixel_name]);

        // Ghi lại toàn bộ cấu hình vào file
        $configData = "bot_token={$config['bot_token']}\n\n";
        foreach ($config as $section => $values) {
            if (is_array($values)) {
                $configData .= "[$section]\n";
                foreach ($values as $key => $value) {
                    $configData .= "$key=$value\n";
                }
                $configData .= "\n";
            }
        }

        file_put_contents('config.txt', $configData);

        sendMessage($chatId, "Pixel name changed from '$old_pixel_name' to '$new_pixel_name' successfully!");
    } else {
        sendMessage($chatId, "Pixel '$old_pixel_name' does not exist.");
    }
} elseif (count($commandParts) == 2) {
    // Xử lý lệnh chia sẻ pixel dựa trên tên pixel
    $pixel_name = trim($commandParts[0], '/'); // Lấy tên pixel từ lệnh
    $ad_account_id = $commandParts[1]; // Lấy ID tài khoản quảng cáo

    if (isset($config[$pixel_name])) {
        // Lấy các thông tin cần thiết từ config
        $pixel_id = $config[$pixel_name]['pixel_id'];
        $bm_id = $config[$pixel_name]['bm_id'];
        $pixel_token = $config[$pixel_name]['pixel_token'];

        // URL API để chia sẻ pixel
        $shareUrl = "https://graph.facebook.com/v12.0/$pixel_id/shared_accounts?account_id=$ad_account_id&access_token=$pixel_token&business=$bm_id&method=post";

        // Thực hiện request
        $response = file_get_contents($shareUrl);
        $responseArray = json_decode($response, true);

        // Xử lý phản hồi
        if (isset($responseArray['success']) && $responseArray['success'] === true) {
            sendMessage($chatId, "Pixel shared successfully!");
        } else {
            // Kiểm tra xem có lỗi cụ thể từ Facebook API không
            if (isset($responseArray['error'])) {
                sendMessage($chatId, "Failed to share pixel: " . $responseArray['error']['message']);
            } else {
                // Trường hợp không có lỗi rõ ràng, nhưng vẫn không thành công
                sendMessage($chatId, "Failed to share pixel: An unexpected error occurred.");
            }
        }
    } else {
        sendMessage($chatId, "Pixel '$pixel_name' does not exist.");
    }
} else {
    // Xử lý các lệnh khác
    switch($commandParts[0]) {
        case "/start":
            sendMessage($chatId, "Welcome to our bot!");
            break;
        case "/help":
            sendMessage($chatId, "To add a pixel, use: /add [pixel_name] [pixel_id] [bm_id] [pixel_token]\n".
                                 "To update a pixel token, use: /update [pixel_name] [new_pixel_token]\n".
                                 "To remove a pixel, use: /remove [pixel_name]\n".
                                 "To share a pixel, use: /[pixel_name] [ad_account_id]\n".
                                 "To edit a pixel name, use: /edit [old_pixel_name] [new_pixel_name]");
            break;
        default:
            sendMessage($chatId, "Sorry, I don't understand that command.");
            break;
    }
}

function sendMessage($chatId, $message) {
    global $apiURL;
    $url = $apiURL . "sendMessage?chat_id=" . $chatId . "&text=" . urlencode($message);
    file_get_contents($url);
}

?>

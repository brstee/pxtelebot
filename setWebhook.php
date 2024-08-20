<?php

$token = "Token_Của_Telebot";
$webhookURL = "https://đường dẫn vào  file/bot.php";
$apiURL = "https://api.telegram.org/bot$token/";

$response = file_get_contents($apiURL . "setWebhook?url=" . $webhookURL);

echo $response;

?>

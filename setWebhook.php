<?php

$token = "5271273866:AAGATshg5hEi5y6GYs4RQyNHEcQJpafaE20";
$webhookURL = "https://aliastone.com/bot_tg/bot.php";
$apiURL = "https://api.telegram.org/bot$token/";

$response = file_get_contents($apiURL . "setWebhook?url=" . $webhookURL);

echo $response;

?>

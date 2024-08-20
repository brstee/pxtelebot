<?php

$token = "5271273866:AAGATshg5hEi5y6GYs4RQyNHEcQJpafaE20";
$apiURL = "https://api.telegram.org/bot$token/";

$response = file_get_contents($apiURL . "deleteWebhook");

echo $response;

?>

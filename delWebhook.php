<?php

$token = "Token_của_telebot";
$apiURL = "https://api.telegram.org/bot$token/";

$response = file_get_contents($apiURL . "deleteWebhook");

echo $response;

?>

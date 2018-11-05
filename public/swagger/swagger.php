<?php
require("../../vendor/autoload.php");
$swagger = \Swagger\scan('../api/');
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type, api_key, Authorization');
echo $swagger;
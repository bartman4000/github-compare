<?php
require("../vendor/autoload.php");
$swagger = \Swagger\scan('../public/api/');
header('Content-Type: application/json');
//echo $swagger;

file_put_contents('../public/api/swagger.json',$swagger);
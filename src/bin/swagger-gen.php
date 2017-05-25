<?php
require("../vendor/autoload.php");
$swagger = \Swagger\scan('../public/api/');
file_put_contents('../public/swagger/swagger.json',$swagger);
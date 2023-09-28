<?php

require_once 'user.php';


$userObject = new User();

// Login
$data = json_decode(file_get_contents("php://input"),true);
if(!empty($data['email']) && !empty($data['password'])){

    $email = $data['email'];
    $password = $data['password'];
    $json_array = $userObject->loginUsers($email,$password);

    echo $json_array;
}


?>
<?php

require_once 'user.php';

//  Make New Object From User Class
$userObject = new User();

// Registration
$data = json_decode(file_get_contents("php://input"),true);
if(!empty($data['full_name']) && !empty($data['password']) && !empty($data['phone']) && !empty($data['confirm_password']) && !empty($data['email'])  && isset($data['is_admin'])){


    $full_name = $data['full_name'];
    $email = $data['email'];
    $password =  $data['password'];
    $confirm_password = $data['confirm_password'];
    $phone = $data['phone'];
    $is_admin = $data['is_admin'];

    $json_registration = $userObject->createNewRegisterUser($email, $full_name,$password,$confirm_password, $phone,$is_admin);

    echo json_encode($json_registration);
}else{
    echo 'Error some fields are missing';
}


?>
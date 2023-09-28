<?php

require_once 'user.php';

$email = "";
$password = "";
$phone = '';
$confirm_password = "";

// Get Values From post Request

if(isset($_POST['email'])){

    $email = $_POST['email'];

}
if(isset($_POST['full_name'])){

    $full_name = $_POST['full_name'];

}

if(isset($_POST['password'])){

    $password = $_POST['password'];

}

if(isset($_POST['confirm_password'])){

    $confirm_password = $_POST['confirm_password'];

}

if(isset($_POST['phone'])){

    $phone = $_POST['phone'];

}





//  Make New Object From User Class
$userObject = new User();

// Registration

if(!empty($email) && !empty($password) && !empty($phone) && !empty($confirm_password) && !empty($full_name) && !empty($is_admin)  ){

    if($password === $confirm_password)
        $json_registration = $userObject->createNewRegisterUser($email, $full_name,$password, $phone,$is_admin);
    else
        json_encode('Password Not Match');


    echo json_encode($json_registration);
}

// Login

if(!empty($email) && !empty($password)){

    $json_array = $userObject->loginUsers($email,$password);

    echo json_encode($json_array);
}


?>
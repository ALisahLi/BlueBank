<?php


require_once 'user.php';


$userObject = new User();
$headers = getallheaders();

//$stat = $userObject->Authenticate($headers['Authorization']);
//
//if($stat['status']){

    echo json_encode($userObject->RetrieveTransactions());

//}







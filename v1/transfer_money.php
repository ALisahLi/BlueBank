<?php


    require_once 'user.php';
    $userObject = new User();
    $headers = getallheaders();
    $headers = getallheaders();
    $stat = $userObject->Authenticate($headers['Authorization']);
if($stat['status']){

    if (!empty($_GET['to']) && !empty($_GET['amount'])){

        $to_id = $_GET['to'];
        $amount = $_GET['amount'];

        $json_checkAmount = $userObject->check_amount($amount);

        if($json_checkAmount)
        {
            $json_moneyTransfer = $userObject->money_Transfer($to_id,$amount,$stat['id']);
            echo json_encode($json_moneyTransfer);

        }else{
            $json['success'] = 0;
            $json['message'] = "Your Balance it's too low";
            echo json_encode($json);

        }

    }else {
        echo json_encode(['please fill the fields ']);
    }
}

?>
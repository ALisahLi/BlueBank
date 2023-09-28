<?php

include_once 'config.php';
// generate json web token
include_once __DIR__ . '/../vendor/autoload.php';
include_once '../vendor/firebase/php-jwt/src/BeforeValidException.php';
include_once '../vendor/firebase/php-jwt/src/ExpiredException.php';
include_once '../vendor/firebase/php-jwt/src/SignatureInvalidException.php';
include_once '../vendor/firebase/php-jwt/src/JWT.php';
use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

class User {

    private $db;

    private $db_table = "users";

    public function __construct(){
        $this->db = new DbConnect();
    }

    public function isLoginExist($email, $password){

        $key = "blueBank";
        $issued_at = time();
        $expiration_time = $issued_at + (60 * 60); // valid for 1 hour
        $issuer = "http://127.0.0.1/blueBank/";

        $query = "select * from ".$this->db_table." where email = '$email' AND password = '$password' Limit 1";
        $result = mysqli_query($this->db->getDb(), $query);
        $row = $result->fetch_row();

        $otp = rand(4000,4999);
        $insertQuery = "INSERT INTO otp_expiry(otp,	expired, created) VALUES ('".$otp."', 0, '".date("Y-m-d H:i:s")."')";
        $inserted = mysqli_query($this->db->getDb(),$insertQuery);


        $token = array(
            "iat" => $issued_at,
            "exp" => $expiration_time,
            "iss" => $issuer,
            "data" => array(
                "id" => $row[0],
                "full_name" => $row[1],
                "phone" => $row[2],
                "is_admin" => $row[5]
            )
        );
        // set response code
        http_response_code(200);
        // generate jwt
        $jwt = JWT::encode($token, $key, 'HS256');
        $json = json_encode(
            array(
                "message" => "Successful login.",
                "jwt" => $jwt,
                "success" => 1
            )
        );

        $_SESSION["ID"]  =  $row[0];
        $_SESSION['token'] = bin2hex(random_bytes(32));
        if(mysqli_num_rows($result) > 0){

            return $json;

        }

        mysqli_close($this->db->getDb());

        return false;

    }

    public function isEmailExist($email){

        $query = "select * from ".$this->db_table." where  email = '$email'";

        $result = mysqli_query($this->db->getDb(), $query);

        if(mysqli_num_rows($result) > 0){

            mysqli_close($this->db->getDb());

            return true;

        }


        return false;

    }

    public function isValidEmail($email){
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    public function Authenticate($jwt)
    {
        try {
            $decoded = JWT::decode($jwt, new Key('blueBank', 'HS256'));
            $payload = json_decode(json_encode($decoded),true);

            if(!empty($payload)) {
                $res=array("status"=>true,'id'=>$payload['data']['id']);

            }else{
                $res=array("status"=>false,"Error"=>"Invalid Token or Token Exipred, So Please login Again!");
            }
        }catch (UnexpectedValueException $e) {

            $res=array("status"=>false,"Error"=>$e->getMessage());
        }
        return $res;

    }




    public function createNewRegisterUser($email, $full_name,$password,$confirm_password, $phone,$is_admin)
    {


        $isExisting = $this->isEmailExist($email);


        if ($isExisting) {
            $json['success'] = 0;
            $json['message'] = "Error in registering. Probably the emailemail already exists";
        } elseif ($password != $confirm_password) {
            $json['success'] = 0;
            $json['message'] = "Error in registering. Password not match";
        }else{

            $isValid = $this->isValidEmail($email);

            if($isValid)
            {
                $query = "insert into ".$this->db_table." (full_name, phone, email, password, is_admin) values ('$full_name', '$phone','$email', '$password',  $is_admin)";
                $inserted = mysqli_query($this->db->getDb(), $query);

                if($inserted == 1){

                    http_response_code(201);
                    $json['success'] = 1;
                    $json['message'] = "Successfully registered";

                }else{

                    http_response_code(400);
                    $json['success'] = 0;
                    $json['message'] = "Error in registering. Probably the username/email already exists";

                }

                mysqli_close($this->db->getDb());
            }
            else{
                $json['success'] = 0;
                $json['message'] = "Error in registering. Email Address is not valid";


            }

        }

        return $json;

    }

    public function loginUsers($email, $password){

        $json = array();

        $canUserLogin = $this->isLoginExist($email, $password);


        if($canUserLogin){
            return $canUserLogin;
//            $json['success'] = 1;
//            $json['message'] = "Successfully logged in";

//            $sqlQuery = "SELECT * FROM otp_expiry WHERE otp='". $_POST["otp"]."' AND expired!=1 AND NOW() <= DATE_ADD(created, INTERVAL 1 HOUR)";
//            if(!empty($count)) {
//                $sqlUpdate = "UPDATE otp_expiry SET expired = 1 WHERE otp = '" . $_POST["otp"] . "'";
//                $result = mysqli_query($this->db->getDb(), $sqlUpdate);
//
//                header("Location:userinfo.php");
//            } else {
//                $errorMessage = "Invalid OTP!";
//            }
    mysqli_close($this->db->getDb());
        }else{
            $json['success'] = 0;
            $json['message'] = "Incorrect details";
        }
        return $json;
    }

    public function otp_verify($otp){
        $sqlQuery = "SELECT * FROM otp_expiry WHERE otp='".$otp."' AND expired!=1 AND NOW() <= DATE_ADD(created, INTERVAL 1 HOUR)";
        $inserted = mysqli_query($this->db->getDb(), $sqlQuery);

        return true;
    }

    public function check_amount($amount){


        $query = "SELECT id,amount,full_name FROM users where id = 1";
        $result = mysqli_query($this->db->getDb(), $query);
        $row_from = $result->fetch_assoc();
        $from_amount = $row_from['amount'];

        if($amount > $from_amount){
            return false;
        }

        return true;

    }

    public function InsertTransactions($from_id,$to_id,$amount)
    {
        $trans_query = "INSERT INTO transactions(`user_id`,`to_id`,`amount`,transaction_date) VALUES ('$from_id','$to_id',$amount,NOW())";
        echo $trans_query;
        $trans_result = mysqli_query($this->db->getDb(), $trans_query);

    }


   public function RetrieveTransactions(){

        $get_trans = "SELECT t.id, u1.full_name AS from_user, u2.full_name AS to_user, t.amount
                        FROM transactions t
                        JOIN users u1 ON t.user_id = u1.id
                        JOIN users u2 ON t.to_id = u2.id;";
        $trans = mysqli_query($this->db->getDb(), $get_trans);
       $result = array();
       while($row_from = $trans->fetch_assoc()){
           $result = $row_from;
       };
       return $result;

    }





    public function money_Transfer($to_id,$amount,$stat = null){


        $query = "SELECT id,amount,full_name FROM users where id = $stat";
        $result = mysqli_query($this->db->getDb(), $query);
        $row_from = $result->fetch_assoc();
        $from_amount = $row_from['amount'];
        $new_from_balance = ($from_amount - $amount);
        echo $new_from_balance.'-';

        $query_to = "SELECT id,amount,full_name FROM users where id = $to_id";
        $result_to = mysqli_query($this->db->getDb(), $query_to);
        $row_to = $result_to->fetch_assoc();
        $to_amount = $row_to['amount'];
        $new_to_balance = ($to_amount + $amount);
        echo $new_to_balance;




        $transfer_query = "UPDATE users SET amount = $new_from_balance where id = 1";
        $transfer_result = mysqli_query($this->db->getDb(), $transfer_query);

        $transfer_query_to = "UPDATE users SET amount = $new_to_balance where id = $to_id";
        $transfer_result_to = mysqli_query($this->db->getDb(), $transfer_query_to);

        $this->InsertTransactions(1,$to_id,$amount);

//        if($transfer_query && $transfer_query_to){
//            sendTransSms($row_from['full_name'],$row_from['phone'],$row_to['full_name'],$row_to['phone'],$amount);
//        }
        $json['success'] = 1;
        $json['message'] = "Your Transfer Has Been Done .";

        return $json;

    }








    //    public function sendTransSms($from_name,$from_phone,$to_name,$to_phone,$amount){
//
//
//
//    }
}
?>
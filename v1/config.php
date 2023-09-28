<?php
session_start();
// required headers
header("Access-Control-Allow-Origin: http://127.0.0.1/blueBank/v1/");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST,GET");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");



class DbConnect{

    private $connect;

    public function __construct(){

        $this->connect = mysqli_connect('localhost', 'root','' , 'bluebank');

        if (mysqli_connect_errno($this->connect)){
            echo "Unable to connect to MySQL Database: " . mysqli_connect_error();
        }
    }

    public function getDb(){
        return $this->connect;
    }
}
?>
<?php

$JSON = "[";

//MySQL Connection
$m_user = "root";
$m_pass = "password";
$m_serv = "localhost";
$m_db_s = "auth";
$m_db = mysqli_connect($m_serv, $m_user, $m_pass, $m_db_s);

if (!$m_db) {
    echo'Unable to connect to mysql <br>';
}

//Rounting
$verb = $_SERVER['REQUEST_METHOD'];

if ($verb == "POST") {
    POST();
} else if ($verb == "GET") {
    GET();
} else if ($verb == "DELETE") {
    DELETE();
}

function POST() {
    global $m_db;
    
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    $query = "INSERT INTO users(`username`, `password`) VALUES ('".$username."', '".$password."');";
    mysqli_query($m_db, $query);
    
    $JSON .= '{"Message":"'.$db->error.'"}';
    printLast();
}

function GET() {
    global $m_db;
    $username = $_GET['username'];
    $password = $_GET['password'];
    $key = $_GET['key'];
    $userOK = false;
    $uid = -1;
    $u_query = "SELECT ID, password FROM users WHERE username='".$username."'";
    $u_result = mysqli_query($m_db, $u_query);
    while ($u_row = mysqli_fetch_row($u_result)) {
        $pass_test = $u_row[1];
        if (strcmp($pass_test, $password) == 0) {
            $userOK = true;
            $uid = $u_row[0];
        }
    }
    if (($userOK == true) && ($uid != -1)) {
        $c_query = "SELECT ID FROM connections WHERE `key`='".$key."'";
        printMessage("QUERY", $c_query);
        $c_result = mysqli_query($m_db, $c_query);
        while ($c_row = mysqli_fetch_row($c_result)) {
            $c_id = $c_row[0];
            passToken($uid, $c_id);
        }
    }
}

function passToken($u_id, $c_id) {
    global $m_db;
    
    $address = $_SERVER['REMOTE_ADDR'];
    $token = random_key();
    
    $query = "INSERT INTO tokens (`userid`, `address`, `token`, `connectionid`) VALUES ('".$u_id."', '".$address."', '".$token."', '".$c_id."');";
    printMessage("QUERY", $query);
    mysqli_query($m_db, $query);
    
}

function random_key() {
    $random = '';
    for($i = 0; $i < 50; ++$i) {
        $random .= chr(mt_rand(48, 122));
    }
    return $random;
}

function DELETE() {
    global $m_db;
    
    $token = $_GET['token'];
    
    $query = "DELETE FROM tokens WHERE `token`='".$token."'";
    printMessage("QUERY", $query);
    mysqli_query($m_db, $query);
}

function printLast() {
    $query = "SELECT * FROM users ORDER BY ID DESC LIMIT 1";
    printResults($query);
}

function printResults($query) {
    global $m_db;
    global $JSON;
    
    $result = mysqli_query($m_db, $query);
    $fields = $result->fetch_fields();
    while ($row = mysqli_fetch_row($result)) {
        $JSON .= '{';
        for ($i = 0; $i < count($row); ++$i) {
            $JSON .= '"'.$fields[$i]->name.'":"'.$row[$i].'",';
        }
        $JSON = substr($JSON, 0, -1);
        $JSON .= '},';
    }
    $JSON = substr($JSON, 0, -1);
    
}

function printMessage($key, $value) {
    global $JSON;
    $JSON .= '{"'.$key.'" : "'.$value.'"}';
}



$JSON .="]";
echo $JSON;
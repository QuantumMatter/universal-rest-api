<?php

$m_serv = "localhost";
$m_user = "root";
$m_pass = "password";
$m_db_s = "auth";

$m_db = mysqli_connect($m_serv, $m_user, $m_pass, $m_db_s);

if (!$m_db) {
    echo "Could not connect";
}

$JSON = "[";

$verb = $_SERVER['REQUEST_METHOD'];
if (strcmp($verb, "POST") == 0) {
    POST();
} else if (strcasecmp($verb, "DELETE") == 0) {
    DELETE();
}

function POST() {
    global $m_db;
    global $JSON;


    $u_id = $_POST['uid'];
    $server = $_POST['server'];
    $database = $_POST['database'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $key = random_key();
    
    $query = "INSERT INTO connections(`userid`, `server`, `database`, `username`, `password`, `key`) VALUES ('".$u_id."', '".$server."', '".$database."', '".$username."', '".$password."', '".$key."');";
    
    mysqli_query($m_db, $query);
    printLast();
}

function printLast() {
    $query = "SELECT * FROM connections ORDER BY ID DESC LIMIT 1";
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

function DELETE() {
    global $m_db;
    
    $query = "DELETE FROM connections WHERE `key`='".$_GET['key']."'";
    mysqli_query($m_db, $query);
}

function random_key() {
    $random = '';
    for($i = 0; $i < 50; ++$i) {
        $random .= chr(mt_rand(48, 122));
    }
    return $random;
}

$JSON .= "]";

echo $JSON;
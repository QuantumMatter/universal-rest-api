<?php

//MySQL
$m_user = "root";
$m_pass = "password";
$m_serv = "localhost";

$u_id;
$c_id;

$m_db_auth = mysqli_connect($m_serv, $m_user, $m_pass, 'auth');

//Authentication
$authenticated = false;
$token = $_GET['token'];
$auth_query = "SELECT address, userid, connectionid FROM tokens WHERE `token`='".$token."'";
//echo $auth_query.'<br>';
$results = mysqli_query($m_db_auth, $auth_query);
while ($row = mysqli_fetch_row($results)) {
    $test_address = $row[0];
    $client_address = $_SERVER['REMOTE_ADDR'];
    if (strcmp($test_address, $client_address) == 0) {
        $authenticated = true;
        $u_id = $row[1];
        $c_id = $row[2];
    }
}

if ($authenticated != true) {
    $results = mysqli_query($m_db_auth, $auth_query);
    while ($row = mysqli_fetch_row($results)) {
        $test_address = $row[0];
        $client_address = "0.0.0.0";
        if (strcmp($test_address, $client_address) == 0) {
            $authenticated = true;
            $u_id = $row[1];
            $c_id = $row[2];
        }
    }
}

if ($authenticated != true) {
    echo'BAD';
    die();
}

#region User Configuration
//User Configuration
$u_user;
$u_pass;
$u_serv;
$u_database_s;

$table = $_GET['table'];

$u_query = "SELECT `username`, `password`, `server`, `database` FROM connections WHERE `ID`='".$c_id."'";
//echo $u_query.'<br>';
$u_results = mysqli_query($m_db_auth, $u_query);
while ($row1 = mysqli_fetch_row($u_results)) {
    $u_user = $row1[0];
    $u_pass = $row1[1];
    $u_serv = $row1[2];
    $u_database_s = $row1[3];
}

$u_db = mysqli_connect($u_serv, $u_user, $u_pass, $u_database_s);
#end region User Configuration

//Routing
$JSON = '[';
$verb = $_SERVER['REQUEST_METHOD'];
if (strcmp($verb, 'GET') == 0) {
    GET();
} else if (strcmp($verb, 'POST') == 0) {
    POST();
} else if (strcmp($verb, 'PUT') == 0) {
    PUT();
} else if (strcmp($verb, 'DELETE') == 0) {
    DELETE();
}

function GET() {
    global $u_db;
    global $JSON;
    global $table;
    
    $query = "SELECT * FROM ".$table."";
    //echo $query.'<br>';
    $result = mysqli_query($u_db, $query);
    //die();
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

function POST() {
    global $u_db;
    global $JSON;
    global $table;

    $keys = "(";
    $values = "(";
    
    foreach ($_POST as $key => $value) {
        $keys .= '`'.$key.'`, ';
        $values .= "'".$value."', ";
    }
    
    $keys = substr($keys, 0, -2);
    $values = substr($values, 0, -2);
    
    $keys .= ')';
    $values .= ')';
    
    $query = "INSERT INTO ".$table.$keys." VALUES ".$values;
    mysqli_query($u_db, $query);
    
    $j_query = "SELECT * FROM ".$table." ORDER BY ID DESC LIMIT 1;";
    printResults($j_query);
    
    printMessage("QUERY", $query);
}

function PUT() {
    global $u_db;
    global $JSON;
    global $table;
    
    $statement = "";
    parse_str(file_get_contents("php://input"),$post_vars);
    
    foreach ($post_vars as $key => $value) {
        $statement .= "`".$key."`='".$value."', ";
    }
    
    $statement = substr($statement, 0, -2);
    
    $query = "UPDATE ".$table." SET ".$statement." WHERE `ID`='".$post_vars['ID']."'";
    //echo $query." ";
    printMessage("QUERY", $query);
    mysqli_query($u_db, $query);
}

function DELETE() {
    global $u_db;
    global $JSON;
    global $table;
    
    $query = "DELETE FROM ".$table." WHERE `ID`='".$_GET['ID']."'";
    printMessage("QUERY", $query);
    mysqli_query($u_db, $query);
}

function printLast() {
    global $table;
    
    $query = "SELECT * FROM ".$table." ORDER BY ID DESC LIMIT 1";
    printResults($query);
}

function printWithID($id) {
    global $table;
    
    $query = "SELECT * FROM ".$table." WHERE `ID`='".$id."'";
    printResults($query);
}

function printInfo() {
    global $verb;
    global $table;
    global $JSON;
    
    $JSON .= ', {"verb":"'.$_SERVER['REQUEST_METHOD'].'", "table": "'.$table.'", "dir": "api"}';
}

function printResults($query) {
    global $u_db;
    global $JSON;
    
    $result = mysqli_query($u_db, $query);
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

printInfo();

$JSON .= ']';

echo $JSON;
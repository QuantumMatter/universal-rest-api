<?php

var_dump($_FILES);

echo '[';

$target_dir = "/var/www/html81/uploads/";
$target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
$uploadOK = 1;

echo '{"target_dir":"'.$target_dir.'"}';
echo '{"target_file":"'.$target_file.'"}';

if(file_exists($target_file)) {
    echo '{"Error":"File Already Exists"}';
    $uploadOK = 0;
}

if($_FILES["fileToUpload"]["size"] > 5000000) {
    echo '{"Error":"File Is Too Large"}';
    $uploadOK = 0;
}

if($uploadOK == 1) {
    if(move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
        echo '{"Message":"Success"}';
        echo '{"Path":"'.$_FILES["fileToUpload"]["name"].'"}';
    } else {
        echo '{"Error":"Could not upload file"}';
    }
}

echo ']';
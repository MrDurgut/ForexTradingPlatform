<?php
include "../connect.php";

$guncelle = $conn->prepare("UPDATE settings SET
		st_name=:st_name,
		st_description=:st_description,
		st_keywords=:st_keywords,
		st_logo=:st_logo,
		st_mserver=:st_mserver,
		st_mport=:st_mport,
		st_musername=:st_musername,
		st_mpassword=:st_mpassword
		WHERE id=1");

$Durum = $guncelle->execute(
    array(
        'st_name' => $_POST['st_name'],
        'st_description' => $_POST['st_description'],
        'st_keywords' => $_POST['st_keywords'],
        'st_logo' => $_POST['st_logo'],
        'st_mserver' => $_POST['st_mserver'],
        'st_mport' => $_POST['st_mport'],
        'st_musername' => $_POST['st_musername'],
        'st_mpassword' => $_POST['st_mpassword']
    )
);

//print_r($guncelle->errorInfo());
header("Location:settings.php?Durum=$Durum");

?>
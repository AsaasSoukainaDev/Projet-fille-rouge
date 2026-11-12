<?php
    require_once "../services/{{Entity}}Service.php";

    header("Content-Type: application/json");
    $methode = $_SERVER["REQUEST_METHOD"];
    $service = new {{Entity}}Service();

    if ($methode == "GET") {
        echo json_encode($service->lister{{Entities}}());
    }

    if ($methode == "POST") {
        echo json_encode($service->ajouter{{Entity}}());
    }
?>


<?php

include "../../controller/chatSponsorController.php";

if (isset($_POST['message']) && !empty($_POST['message'])) {

    $chat = new chatSponsorController();

    echo $chat->send($_POST['message']);
    exit; 
    }

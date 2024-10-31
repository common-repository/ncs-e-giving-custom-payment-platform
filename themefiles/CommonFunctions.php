<?php

//session_start();

global $AdminDetails;
$AdminDetails = get_option("custom_payment_name");

error_reporting(-1);

## This file will be used to manage the common functions used in the plugin...
include_once("library/Class.Rest.php");
include_once("library/Class.Session.php");
include_once("library/Class.Member.php");
include_once("library/Class.Funds.php");
include_once("library/Class.Schedule.php");
include_once("library/Class.CreditCard.php");
include_once("library/Class.BankAccount.php");
include_once("library/Class.Transaction.php");

use NCSSERVICES\eg_ws_call;
use NCSSERVICES\Entity;
use NCSSERVICES\Session;
use NCSSERVICES\Member;

## function to get the session for member defined from the admin...

function CPGetMemberSession() {
    global $AdminDetails;

    if (isset($_SESSION["CP-GENERALADMINSESSIONTOKEN"]) && trim($_SESSION["CP-GENERALADMINSESSIONTOKEN"]) != "") {
        $token = $_SESSION["CP-GENERALADMINSESSIONTOKEN"];
    } else {
        $eg_ws = new eg_ws_call();
        $sess = new \NCSSERVICES\Session($AdminDetails["admin_token"], $AdminDetails["admin_username"], $AdminDetails["admin_password"]); //MEMBER CREDENTIALS
        $EG_START_ADMIN_SESSION = $sess->AdminSession();
        $eg_ws->method = $EG_START_ADMIN_SESSION['method'];
        $out = $eg_ws->do_ws_call($EG_START_ADMIN_SESSION['url'], $sess->toJson());
        if ($out) {
            $response = json_decode($out);
            $token = $response->Token;
        } else {
            $token = $eg_ws->err;
        }
        $_SESSION["CP-GENERALADMINSESSIONTOKEN"] = $token;
    }
    return $token;
}

?>
<?php
/**
 * Template Name: Process Payment Page.
 *
 * @package Custom Payment
 * @Author Ankit Gupta
 * @Date 24/6/2014
 * Description: This template will be used to process the payment for the selected funds.
 */
## start the session from here...
session_start();

get_header();
include_once("CommonFunctions.php");

use NCSSERVICES\eg_ws_call;
use NCSSERVICES\Entity;
use NCSSERVICES\Member;
use NCSSERVICES\Session;
use NCSSERVICES\CreditCard;
use NCSSERVICES\Transaction;
use NCSSERVICES\BankAccount;
use NCSSERVICES\Schedule;

##prepare required urls from here...
$HelpImgUrl = plugins_url('CustomPayment/assets/images/help.png');
$CCImgUrl = plugins_url('CustomPayment/assets/images/');
$CheckImgUrl = plugins_url('CustomPayment/assets/images/bank-check.jpg');
$CartPageUrl = get_permalink(get_page_by_path("cp-cart-page"));
$FundsListUrl = get_permalink(get_page_by_path("cp-funds-listing-page"));

##Prepare list of states...
$StatesList = array("AL" => "Alabama",
    "AK" => "Alaska",
    "AZ" => "Arizona",
    "AR" => "Arkansas",
    "CA" => "California",
    "CO" => "Colorado",
    "CT" => "Connecticut",
    "DE" => "Delaware",
    "DC" => "District Of Columbia",
    "FL" => "Florida",
    "GA" => "Georgia",
    "HI" => "Hawaii",
    "ID" => "Idaho",
    "IL" => "Illinois",
    "IN" => "Indiana",
    "IA" => "Iowa",
    "KS" => "Kansas",
    "KY" => "Kentucky",
    "LA" => "Louisiana",
    "ME" => "Maine",
    "MD" => "Maryland",
    "MA" => "Massachusetts",
    "MI" => "Michigan",
    "MN" => "Minnesota",
    "MS" => "Mississippi",
    "MO" => "Missouri",
    "MT" => "Montana",
    "NE" => "Nebraska",
    "NV" => "Nevada",
    "NH" => "New Hampshire",
    "NJ" => "New Jersey",
    "NM" => "New Mexico",
    "NY" => "New York",
    "NC" => "North Carolina",
    "ND" => "North Dakota",
    "OH" => "Ohio",
    "OK" => "Oklahoma",
    "OR" => "Oregon",
    "PA" => "Pennsylvania",
    "RI" => "Rhode Island",
    "SC" => "South Carolina",
    "SD" => "South Dakota",
    "TN" => "Tennessee",
    "TX" => "Texas",
    "UT" => "Utah",
    "VT" => "Vermont",
    "VI" => "Virgin Islands",
    "VA" => "Virginia",
    "WA" => "Washington",
    "WV" => "West Virginia",
    "WI" => "Wisconsin",
    "WY" => "Wyoming",
    "AA" => "Armed Forces Americas",
    "AE" => "Armed Forces Europe",
    "AP" => "Armed Forces Pacific");


## manage form post from here...
$Error = array();
$Success = array();
$regex = '/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/';
$UNRegEx = '/^[A-Za-z0-9_.-]*$/';
$ZIPRegEx = '/(^\d{5}$)|(^\d{5}-\d{4}$)/';
$eg_ws = new eg_ws_call();
$token = '';
$SelectedPaymentMode = "";

## get user Details if user is logged in...
$UserEmailAddress = "";
$IsACHAllowed = 0;
if (isset($_SESSION["CP-UserToken"]) && trim($_SESSION["CP-UserToken"]) != "") {
    $UserID = $_SESSION["CP-UserID"];
    $IsACHAllowed = $_SESSION["CP-ACHAllowed"];
    $UserEmailAddress = trim($_SESSION["CP-Email"]);
}

$SelectedFunds = unserialize($_SESSION["CP-SelectedFunds"]);
$ReceiptPageUrl = get_permalink(get_page_by_path("cp-receipt-page"));

## handle form data from here...
if (isset($_POST["FinishBtn"])) {

    ## check for payment mode and place server side validations, call API accordingly...
    $PaymentMode = sanitize_text_field($_POST["PaymentMode"]);


    switch ($PaymentMode) {
        case "CreditCard":
            $CreditCardNumber = sanitize_text_field($_POST["CreditCardNumber"]);
            $CreditCardCvv = sanitize_text_field($_POST["CreditCardCvv"]);
            $CreditCardUserName = sanitize_text_field($_POST["CreditCardUserName"]);
            $CreditCardBillingAddress = sanitize_text_field($_POST["CreditCardBillingAddress"]);
            $CreditCardBillingAddress2 = sanitize_text_field($_POST["CreditCardBillingAddress2"]);
            $CreditCardCity = sanitize_text_field($_POST["CreditCardCity"]);
            $CreditCardState = sanitize_text_field($_POST["CreditCardState"]);
            $CreditCardZip = sanitize_text_field($_POST["CreditCardZip"]);
            $CreditCard = sanitize_text_field($_POST["CreditCardCountry"]);
            $UserEmailAddress = sanitize_text_field($_POST["CreditCardUserEmailAddress"]);
            $CreditCardMonth = sanitize_text_field($_POST["CreditCardMonth"]);
            $CreditCardYear = sanitize_text_field($_POST["CreditCardYear"]);

            $CreditCardFinalBillingAddress = $CreditCardBillingAddress . " " . $CreditCardBillingAddress2;

            if ($CreditCardNumber == "" && !is_numeric($CreditCardNumber)) {
                $Error[] = "Please enter proper credit card number.";
            } else if ($CreditCardCvv == "" && !is_numeric($CreditCardCvv)) {
                $Error[] = "Please enter proper CVV number.";
            } else if ($CreditCardDate == "") {
                ## place validations for month and year...
            } else if ($CreditCardUserName == "") {
                $Error[] = "Please enter proper CVV number.";
            } else if ($CreditCardBillingAddress == "") {
                $Error[] = "Address field 1 cannot be empty.";
            } /*else if ($CreditCardBillingAddress2 == "") {
                $Error[] = "Address field 2 cannot be empty.";
            }*/ else if ($CreditCardCity == "") {
                $Error[] = "City cannot be empty.";
            } else if ($CreditCardState == "") {
                $Error[] = "State cannot be empty.";
            } elseif (sanitize_text_field($CreditCardZip) == "") {
                $Error[] = "Zip cannot be blank.";
            } elseif (!preg_match($ZIPRegEx, sanitize_text_field($CreditCardZip))) {
                $Error[] = "Please enter proper zip code.";
            } else if ($UserEmailAddress == "") {
                $Error[] = "Please enter proper email address.";
            }

            if (empty($Error)) {
                if (isset($_SESSION["CP-UserToken"]) && trim($_SESSION["CP-UserToken"]) != "") {
                    $token = trim($_SESSION["CP-UserToken"]);
                } else {
                    $token = CPGetMemberSession();
                }

                //SEND MEMBER CC TRANSACTION
                $FundsObject = array();
                $Total = 0;
                foreach ($SelectedFunds as $key => $val) {
                    $FundsObject[] = new \NCSSERVICES\TransactionFund($val["NAME"], $val["AMNT"], '', 1);
                    $Total = $Total + (float) $val["AMNT"];
                }

                $funds = $FundsObject;

                $cc = new \NCSSERVICES\CreditCard('Credit Card Payment', $CreditCardNumber, '', $CreditCardMonth, $CreditCardYear, $CreditCardUserName, $CreditCardFinalBillingAddress, $CreditCardCity, $CreditCardState, $CreditCardZip);

                if (isset($_SESSION["CP-UserToken"])) {
                    $eg_ws->method = $EG_MEMBER_CC_TRANSACTION['method'];
                    $tr = new \NCSSERVICES\Transaction($Total, $UserEmailAddress, '', $funds, null, $cc);
                    if ($eg_ws->do_ws_call($EG_MEMBER_CC_TRANSACTION['url'], $tr->toJson(), $token)) {
                        $TransactionUrl = $eg_ws->location;

                        $CreditCardCompany = GetCreditCardCompany($CreditCardNumber);
                        $CityStateZip = $CreditCardCity . ", " . $CreditCardState . " " . $CreditCardZip;

                        ## schedule payments for logged in users using credit-cards...
                        $PaymentsScheduled = SchedulePayments($funds, $cc, $SelectedFunds, 'CC', $token, $eg_ws);
                        if ($PaymentsScheduled == 0) {
                            $Error[] = "Error while scheduling payments.";
                        }
                        if (empty($Error)) {
                            ## generate receipt if payment was successful...
                            GenerateReceipt($TransactionUrl, $UserEmailAddress, $CreditCardUserName, $CreditCardFinalBillingAddress, "CC", $CreditCardNumber, $CreditCardCompany, $CityStateZip);
                        }
                    } else {
                        $Error[] = $eg_ws->err;
                    }
                } else {
                        
                    $eg_ws->method = $EG_GUEST_TRANSACTION['method'];
                    
                    $tr = new \NCSSERVICES\Transaction($Total, $UserEmailAddress, '', $funds, null, $cc);
                    unset($_SESSION["CP-PaymentUserName"]);
                    unset($_SESSION["CP-PaymentUserAddress"]);
                    unset($_SESSION["CP-PaymentUserEmailAddress"]);
                    unset($_SESSION["CP-PaymentCompanyName"]);
                    unset($_SESSION["CP-CityStateZip"]);
                    unset($_SESSION["CP-PaymentAccountNumber"]);
                    
                    if ($eg_ws->do_ws_call($EG_GUEST_TRANSACTION['url'], $tr->toJson(), $token)) {
                        $TransactionUrl = $eg_ws->location;
                        $CreditCardCompany = GetCreditCardCompany($CreditCardNumber);
                        $CityStateZip = $CreditCardCity . ", " . $CreditCardState . " " . $CreditCardZip;
                        
                        ## generate receipt if payment was successful...
                        GenerateReceipt($TransactionUrl, $UserEmailAddress, $CreditCardUserName, $CreditCardFinalBillingAddress, "CC", $CreditCardNumber, $CreditCardCompany, $CityStateZip);
                    } else {
                        
                        $Error[] = $eg_ws->err;
                    }
                }
            }
            break;
        case "Check":
            $CheckBankName = sanitize_text_field($_POST["CheckBankName"]);
            $CheckAccntNumber = sanitize_text_field($_POST["CheckAccntNumber"]);
            $CheckRoutingNumber = sanitize_text_field($_POST["CheckRoutingNumber"]);
            $CheckAccntHolderName = sanitize_text_field($_POST["CheckAccntHolderName"]);
            $CheckAccntType = sanitize_text_field($_POST["CheckAccntType"]);
            $CheckBillingAddress = sanitize_text_field($_POST["CheckBillingAddress"]);
            $CheckBillingAddress2 = sanitize_text_field($_POST["CheckBillingAddress2"]);
            $CheckCity = sanitize_text_field($_POST["CheckCity"]);
            $CheckState = sanitize_text_field($_POST["CheckState"]);
            $CheckZip = sanitize_text_field($_POST["CheckZip"]);
            $UserEmailAddress = sanitize_text_field($_POST["CheckUserEmailAddress"]);

            if ($CheckAccntNumber == "") {
                $Error[] = "Account number cannot be blank.";
            } else if ($CheckRoutingNumber == "" || strlen($CheckRoutingNumber) != 9 || !is_numeric($CheckRoutingNumber)) {
                $Error[] = "Invalid routing number.";
            } else if ($CheckAccntHolderName == "") {
                $Error[] = "Account holder's name cannot be blank.";
            } else if ($CheckAccntType == "") {
                $Error[] = "Please select an account type.";
            } else if ($CheckBillingAddress == "") {
                $Error[] = "billing address cannnot be blank.";
            } /*else if ($CheckBillingAddress2 == "") {
                $Error[] = "billing address line two cannnot be blank.";
            }*/ else if ($CheckCity == "") {
                $Error[] = "City cannot be empty.";
            } else if ($CheckState == "") {
                $Error[] = "Please select a state.";
            } elseif (sanitize_text_field($CheckZip) == "") {
                $Error[] = "Zip cannot be blank.";
            } elseif (!preg_match($ZIPRegEx, sanitize_text_field($CheckZip))) {
                $Error[] = "Please enter proper zip code.";
            } else if ($UserEmailAddress == "") {
                $Error[] = "Please enter proper email address.";
            }
            if (empty($Error)) {
                //START A NEW MEMBER SESSION
                if (isset($_SESSION["CP-UserToken"]) && trim($_SESSION["CP-UserToken"]) != "") {
                    $token = trim($_SESSION["CP-UserToken"]);
                } else {
                    $token = CPGetMemberSession();
                }


                //SEND MEMBER ACH TRANSACTION
                $FundsObject = array();
                $Total = 0;
                foreach ($SelectedFunds as $key => $val) {
                    $FundsObject[] = new \NCSSERVICES\TransactionFund($val["NAME"], $val["AMNT"], '', 1);
                    $Total = $Total + (float) $val["AMNT"];
                }
                $funds = $FundsObject;
                $eg_ws->method = $EG_MEMBER_ACH_TRANSACTION['method'];
                $ach = new \NCSSERVICES\BankAccount($CheckAccntNumber, $CheckRoutingNumber, $CheckAccntHolderName, $CheckBankName, $CheckAccntType);
                $ach->MemberID = $_SESSION["CP-UserID"];
                $tr = new \NCSSERVICES\Transaction($Total, $UserEmailAddress, '', $funds, $ach, null);

                if ($eg_ws->do_ws_call($EG_MEMBER_ACH_TRANSACTION['url'], $tr->toJson(), $token)) {
                    $TransactionUrl = $eg_ws->location;
                } else {
                    $Error[] = $eg_ws->err;
                }

                $CityStateZip = $CheckCity . ", " . $CheckState . " " . $CheckZip;

                $PaymentsScheduled = SchedulePayments($funds, $ach, $SelectedFunds, 'C', $token, $eg_ws);
                if ($PaymentsScheduled == 0) {
                    $Error[] = "Error while scheduling payments.";
                }
                if (empty($Error)) {
                    ## generate receipt if payment was successful...
                    GenerateReceipt($TransactionUrl, $UserEmailAddress, $CheckAccntHolderName, $CreditCardFinalBillingAddress, "C", $CheckAccntNumber, $CheckBankName, $CityStateZip);
                }
            } // if no error generated condition ends here...

            break;
    }
    ## check for server side validations..
}

## function to generate receipt...

function GenerateReceipt($TransactionUrl, $UserEmailAddress, $UserName, $FinalBillingAddress, $PaymentMethod, $AccntNumber, $companyName, $CityStateZip) {
    $ReceiptUrl = get_permalink(get_page_by_path("cp-receipt-page"));
    $TransactionUrlArray = explode("/", $TransactionUrl);
    $TransactionUrlArrayLength = count($TransactionUrlArray);
    $TransactionId = $TransactionUrlArray[$TransactionUrlArrayLength - 1];

    $_SESSION["CP-PaymentUserName"] = $UserName;
    $_SESSION["CP-PaymentUserAddress"] = $FinalBillingAddress;
    $_SESSION["CP-PaymentUserEmailAddress"] = $UserEmailAddress;
    $_SESSION["CP-PaymentCompanyName"] = $companyName;
    $_SESSION["CP-CityStateZip"] = $CityStateZip;
    $_SESSION["CP-PaymentAccountNumber"] = $AccntNumber;
    $FinalReceiptUrl = add_query_arg(array('TrId' => $TransactionId, 'PmM' => $PaymentMethod), $ReceiptUrl);
    wp_redirect($FinalReceiptUrl);
    exit;
}

## function to get the company name on the basis of the credit card number...

function GetCreditCardCompany($CreditCardNumber) {
    $CompanyName = "";
    $FirstCharacter = substr($CreditCardNumber, 1, 1);
    switch ($FirstCharacter) {
        case "3":
            $CompanyName = "American Express";
            break;
        case "4":
            $CompanyName = "Visa";
            break;
        case "5":
            $CompanyName = "Master Card";
            break;
        case "6":
            $CompanyName = "Discover";
            break;
        default :
            $CompanyName = "Visa";
            break;
    }
    return $CompanyName;
}

## function to schedule payments...

function SchedulePayments($Funds, $PaymentObj, $SelectedFunds, $PaymentMethod, $token, $eg_ws) {
    $IsSuccess = 1;
    foreach ($SelectedFunds as $key => $val) {
        $Frequency = "";
        switch ($val["FREQUENCY"]) {
            case "Now":
                $Frequency = "o";
                break;
            case "one-time":
                $Frequency = "o";
                break;
            case "biweekly":
                $Frequency = "b";
                break;
            case "semi-monthly":
                $Frequency = "s";
                break;
            case "monthly":
                $Frequency = "m";
                break;
            case "quarterly":
                $Frequency = "q";
                break;
        }
        
        $FundsObject = array(new \NCSSERVICES\TransactionFund($val["NAME"], $val["AMNT"], '', $val["TAXDEDUCTIBLE"]));
        
        $Method = "";
        $URL = "";
        //print_r($sch);
        switch ($PaymentMethod) {
            case "CC":
                $sch = new \NCSSERVICES\Schedule($Frequency, $val["STARTDATE"], $val["ENDDATE"], $val["AMNT"], $FundsObject, $PaymentObj, '');
                $EG_ADD_CREDITCARD_SCHEDULE = $sch->CreditCardSchedulePayment();
                $Method = $EG_ADD_CREDITCARD_SCHEDULE["method"];
                $URL = $EG_ADD_CREDITCARD_SCHEDULE['url'];
                break;
            case "C":
                $sch = new \NCSSERVICES\Schedule($Frequency, $val["STARTDATE"], $val["ENDDATE"], $val["AMNT"], $FundsObject, '',$PaymentObj);
                $EG_ADD_BANKACCOUNT_SCHEDULE = $sch->ACHSchedulePayment();
                $Method = $EG_ADD_BANKACCOUNT_SCHEDULE["method"];
                $URL = $EG_ADD_BANKACCOUNT_SCHEDULE['url'];
                break;
        }
        
        if ($Method != "" && $URL != "") {
            $eg_ws->method = $Method;
            if ($eg_ws->do_ws_call($URL, $sch->toJson(), $token)) {
                 ///echo $eg_ws->location."<br />";
            } else {
                $IsSuccess = 0;
                //echo 'ERR: ' . $eg_ws->err;
            }
        }
    } 
    return $IsSuccess;
}

## check for payment mode...
if ($SelectedPaymentMode == "" || $SelectedPaymentMode == "CreditCard") {
    $CCSelected = "checked";
} else {
    $CheckSelected = "checked";
}

wp_enqueue_script('script-validation-rules', plugins_url('CustomPayment/assets/js/jquery.validationEngine-en.js'), array(), '1.0.0', true);
wp_enqueue_script('script-validation', plugins_url('CustomPayment/assets/js/jquery.validationEngine.js'), array(), '1.0.0', true);
wp_enqueue_style("style-validation", plugins_url('CustomPayment/assets/css/validationEngine.css'));
?>
<style>
    .MainConatiner{width:100%;}
    .PaymentModeSelector{margin-bottom:10px;}
    .PaymentFormContainer{float:left;width:70%;}
    .CreditCardImageContainer{float:left;width:30%}
    .CheckImageContainer{float:left;width:30%}
    .clear{clear:both;}
    .ui-datepicker-trigger { margin:-3px 10px;}
    .ui-datepicker-calendar {
        display: none;
    }
    .payment-form-box{ margin-bottom:10px; float: left; width: 100%;}
    .payment-form-box input{ width: 90.5%;}
    .payment-form-box.first input{ width:26%; }
    .payment-form-box.last input, .payment-form-box.last select{ width:28.9%; }
    .bank-nm{ float: left; margin-right:2%; width:40%;}
    .bank-nm input{ width:95%;}
    .ac-no{ float: left; width:58%;}
    .ac-no input{ width: 30%; float: left; margin-right: 5%;}
    .ac-holder-box{ float:left; margin-right:2%; width:40%;}
    .ac-holder-box input{ width:95%;}
    .ac-holder-select{ float:left; padding-top:10px;}
    .ac-holder-select input{ float:left; width:auto; margin-top:0;}
    .ac-holder-select label{ float:left; margin:0 10px;}
</style>
<div id="main-content" class="main-content">
    <div id="primary" class="content-area side-content-area">
        <!--        <div id="content" class="site-content" role="main">-->

        <?php if (!empty($Error)) { ?>
            <div class="ErrorMessages">
                <?php foreach ($Error as $key => $val) { ?>
                    <div class="red"><?php echo $val; ?></div>
                <?php } ?>
            </div>    
        <?php }
        ?>

        <?php if (!empty($Success)) { ?>
            <div class = "SuccsesMessages">
                <?php foreach ($Success as $key => $val) {
                    ?>
                    <div class="green"><?php echo $val; ?></div>
                <?php } ?>
            </div>    
        <?php }
        ?>

        <!--Display header from here...-->
        <?php include_once("cp-header.php"); ?> 

        <form method="post" action="" id="ProcessPaymentForm" name="ProcessPaymentForm" >

            <div class="MainConatiner">
                <?php if ($IsACHAllowed == 1) { ?>
                    <div class="PaymentModeSelector">
                        <input type="radio" name="PaymentMode" id="CreditCard" class="PaymentModeSelectionClass" <?php echo $CCSelected; ?> value="CreditCard" /> Credit Card
                        <input type="radio" name="PaymentMode" id="Check" class="PaymentModeSelectionClass" <?php echo $CheckSelected; ?> value="Check" /> Check
                    </div>
                <?php } else { ?>
                    <input type="hidden" name="PaymentMode" id="PaymentMode"  value="CreditCard" />
                <?php } ?>
                <div id="CreditCardPaymentForm">
                    <div class="PaymentFormContainer">
                        <div class="payment-form-box first">
                            <input type="text" id="CreditCardNumber" name="CreditCardNumber"  class="validate[required,creditCard,custom[onlyNumberSp]]" placeholder="Credit Card Number" /> 
                            <select id="CreditCardMonth" name="CreditCardMonth">
                                <?php
                                $MonthCount = 1;
                                for ($MonthCount = 1; $MonthCount <= 12; $MonthCount++) {
                                    ?>
                                    <option value="<?php echo $MonthCount; ?>"><?php echo $MonthCount; ?></option>
                                <?php } ?>
                            </select>
                            <select id="CreditCardYear" name="CreditCardYear">
                                <?php
                                $CurrentYear = date("Y");
                                $LastYear = $CurrentYear + 10;
                                $YearCount = 0;
                                for ($YearCount = $CurrentYear; $YearCount <= $LastYear; $YearCount++) {
                                    ?>
                                    <option value="<?php echo $YearCount; ?>"><?php echo $YearCount; ?></option>
                                <?php } ?>
                            </select>
<!--                            <input type="text" id="CreditCardDate" name="CreditCardDate" class="validate[required]" placeholder="mm/yyyy"  /> -->
                            <input type="text" id="CreditCardCvv" name="CreditCardCvv" class="validate[required,custom[CVV]]" placeholder="CVV" /> 
                            <image style="position:relative; top: 5px;" src="<?php echo $HelpImgUrl; ?>" id="CreditCardHelpImage" />
                        </div>
                        <div class="payment-form-box">
                            <input type="text" id="CreditCardUserName" name="CreditCardUserName" class="validate[required]" placeholder="Your name as it appears on the card" />   
                        </div>   
                        <div class="payment-form-box">
                            <input type="text" id="CreditCardBillingAddress" name="CreditCardBillingAddress" class="validate[required]" placeholder="Billing Address" />   
                        </div>   
                        <div class="payment-form-box">
                            <input type="text" id="CreditCardBillingAddress2" name="CreditCardBillingAddress2" class="" placeholder="Billing Address line two"/>   
                        </div>
                        <div class="payment-form-box last">
                            <input type="text" id="CreditCardCity" name="CreditCardCity" class="validate[required]" placeholder="City" />   
                            <select name="CreditCardState" id="CreditCardState" class="width40p validate[required]" >
                                <option value="">State </option>
                                <?php
                                foreach ($StatesList as $key => $val) {
                                    $selected = "";
                                    if ($_POST["state"] == $key) {
                                        $selected = "selected";
                                    }
                                    ?>
                                    <option value="<?php echo $key; ?>" <?php echo $selected; ?>><?php echo $val; ?></option>
                                <?php } ?>
                            </select>
                            <input type="text" class="validate[required,custom[CustomZip]]" id="CreditCardZip" name="CreditCardZip" placeholder="Zip Code" value="<?php echo $_POST["zip"]; ?>" />
                            <input type="hidden" id="CreditCardCountry" name="CreditCardCountry" placeholder="Country" value="US" />    
                        </div>
                        <?php if (!isset($_SESSION["CP-UserToken"])) { ?>
                            <div class="payment-form-box">

                                <input type="text" id="UserEmailAddress" name="CreditCardUserEmailAddress" class="validate[required,custom[email]]" placeholder="Email Address" value="<?php echo $UserEmailAddress; ?>" />
                            </div>
                        <?php } else { ?>
                            <input type="hidden" id="UserEmailAddress" name="CreditCardUserEmailAddress" class="validate[required,custom[email]]" placeholder="Email Address" value="<?php echo $UserEmailAddress; ?>" />   
                        <?php } ?>    
                    </div> <!-- PaymentFormContainer -->

                    <div class="CreditCardImageContainer">
                        <img src="<?php echo $CCImgUrl . "default-credit-card-back.jpg"; ?>" width="300px" id="CreditCardImage" style="display:none;" />
                    </div>  <!-- CreditCardImageContainer -->

                    <div class="clear"></div>
                </div> <!-- #CreditCardPaymentForm--> 


                <!-- ************************* Form for check payment starts from here... ************************ -->

                <div id="CheckPaymentForm" style="display: none;">
                    <div class="PaymentFormContainer">
                        <div class="payment-form-box">
                            <div class="bank-nm"><input type="text" id="CheckBankName" name="CheckBankName" class="validate[required]" placeholder="Bank Name" /></div>  
                            <div class="ac-no">    
                                <input type="text" id="CheckAccntNumber" name="CheckAccntNumber" class="validate[required]" placeholder="Account number" />   
                                <input type="text" id="CheckRoutingNumber" name="CheckRoutingNumber" class="validate[required]" maxlength="9" placeholder="Routing number" />   
                                <image style="position:relative; top: 5px;" src="<?php echo $HelpImgUrl; ?>" id="CheckHelpImage" />
                            </div>
                        </div>   
                        <div class="payment-form-box">
                            <div class="ac-holder-box"><input type="text" id="CheckAccntHolderName" name="CheckAccntHolderName" class="validate[required]" placeholder="Account holder's name" /></div>
                            <div class="ac-holder-select">
                                <input type="radio" id="CheckCheckingAccount" name="CheckAccntType" checked="" value="C" /> 
                                <label>Checking Account</label>
                                <input type="radio" id="CheckSavingsAccount" name="CheckAccntType" value="S" /> 
                                <label>Savings Account</label>
                            </div>    
                        </div>
                        <div class="payment-form-box">
                            <input type="text" id="CheckBillingAddress" name="CheckBillingAddress" class="validate[required]" placeholder="Billing address" />   
                        </div>
                        <div class="payment-form-box">
                            <input type="text" id="CheckBillingAddress2" name="CheckBillingAddress2" class="" placeholder="Billing address line two" />   
                        </div>

                        <div class="payment-form-box last">
                            <input type="text" id="CheckCity" name="CheckCity" class="validate[required]" placeholder="City" />   
                            <select name="CheckState" id="CheckState" class="width40p validate[required]" >
                                <option value="">State </option>
                                <?php
                                foreach ($StatesList as $key => $val) {
                                    $selected = "";
                                    if ($_POST["state"] == $key) {
                                        $selected = "selected";
                                    }
                                    ?>
                                    <option value="<?php echo $key; ?>" <?php echo $selected; ?>><?php echo $val; ?></option>
                                <?php } ?>
                            </select>
                            <input type="text" class="validate[required,custom[CustomZip]]" id="CheckZip" name="CheckZip" placeholder="Zip Code" value="<?php echo $_POST["zip"]; ?>" />
                            <input type="hidden" id="CheckCountry" name="CheckCountry" placeholder="Country" value="US" />
                        </div>
                        <?php if (!isset($_SESSION["CP-UserToken"])) { ?>
                            <div class="payment-form-box">
                                <input type="text" id="UserEmailAddress" name="CheckUserEmailAddress" class="validate[required,custom[email]]" placeholder="Email Address" value="<?php echo $UserEmailAddress; ?>" />
                            </div>
                        <?php } else { ?>
                            <input type="hidden" id="UserEmailAddress" name="CheckUserEmailAddress" class="validate[required,custom[email]]" placeholder="Email Address" value="<?php echo $UserEmailAddress; ?>" />   
                        <?php } ?>    
                    </div>
                    <div class="CheckImageContainer">
                        <img src="<?php echo $CheckImgUrl; ?>" width="300px" id="CheckImage" style="display:none;" />
                    </div>  <!-- CreditCardImageContainer -->
                    <div class="clear"></div>
                </div>  <!-- #CheckPaymentForm -->  
            </div> <!-- MainConatiner -->


            <?php if (!empty($SelectedFunds)) { ?>
                <a href="<?php echo $CartPageUrl; ?>" style="text-decoration: none;"><input type="button" id="BackBtn" name="BackBtn" value=" < Back to Cart" /></a>
                <input type="submit" id="FinishBtn" name="FinishBtn" value=" Finish > " />
            <?php } else { ?>
                <a href="<?php echo $FundsListUrl; ?>" style="text-decoration: none;"><input type="button" id="BackBtn" name="BackBtn" value=" < Back to Funds Listing" /></a>
                <?php } ?>
        </form>
        <!--        </div> #content -->
    </div><!-- #primary -->
</div><!-- #main-content -->
<script>
    jQuery.noConflict();
    jQuery(document).ready(function() {

        /*validate form from here...*/
        jQuery("#ProcessPaymentForm").validationEngine();

        /*Display the help image from here...*/
        jQuery("#CreditCardHelpImage").click(function() {
            if (jQuery("#CreditCardNumber").val() == "") {
                jQuery("#CreditCardImage").css("display", "");
            } else {
                var CreditCardNumber = jQuery("#CreditCardNumber").val();
                var FirstCreditCardNumber = CreditCardNumber.charAt(0);
                var ImageName = "";
                switch (FirstCreditCardNumber) {
                    case "3":
                        ImageName = "american-express";
                        break;
                    case "4":
                        ImageName = "visa";
                        break;
                    case "5":
                        ImageName = "master-card";
                        break;
                    case "6":
                        ImageName = "discover";
                        break;
                    default :
                        ImageName = "visa";
                        break;
                }
                ImageUrl = '<?php echo $CCImgUrl ?>' + ImageName + "-credit-card-back.jpg";
                jQuery("#CreditCardImage").attr("src", ImageUrl);
                jQuery("#CreditCardImage").css("display", "");
            }
        });

        jQuery("#CheckHelpImage").click(function() {
            jQuery("#CheckImage").css("display", "");
        });

        /*Hide the help image from here...*/
        jQuery("#CreditCardImage").click(function() {
            jQuery(this).css("display", "none");
        });
        jQuery("#CheckImage").click(function() {
            jQuery(this).css("display", "none");
        });

        /*Switch Forms for credit cards and checks from here ...*/
        jQuery(".PaymentModeSelectionClass").click(function() {
            var SelectdForm = "";
            var UnselectedForm = ""
            switch (jQuery(this).val()) {
                case "Check":
                    SelectdForm = "CheckPaymentForm";
                    UnselectedForm = "CreditCardPaymentForm";
                    break;
                case "CreditCard":
                    SelectdForm = "CreditCardPaymentForm";
                    UnselectedForm = "CheckPaymentForm";
                    break;
            }
            jQuery("#" + UnselectedForm).hide();
            jQuery("#" + SelectdForm).show();
        });

        jQuery("#ProcessPaymentForm").submit(function(e) {
            var d = new Date();
            var CurMonth = d.getMonth();
            var CurYear = d.getFullYear();
            if (jQuery(".PaymentModeSelector").length) {
                if ($('#CreditCard').is(':checked')) {
                    var SelectedMonth = jQuery("#CreditCardMonth").val();
                    var SelectedYear = jQuery("#CreditCardYear").val();
                    if (SelectedYear <= CurYear && SelectedMonth < CurMonth) {
                        alert("Invalid expiry month and year selection for credit card.");
                        e.preventDefault();
                        return false;
                    }
                }
            } else {
                var SelectedMonth = jQuery("#CreditCardMonth").val();
                var SelectedYear = jQuery("#CreditCardYear").val();
                if (SelectedYear <= CurYear && SelectedMonth < CurMonth) {
                    alert("Invalid expiry month and year selection for credit card.");
                    e.preventDefault();
                    return false;
                }
            }
        });
    });
</script>
<?php
get_footer();

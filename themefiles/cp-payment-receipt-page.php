<?php
/**
 * Template Name: Payment Receipt Page.
 *
 * @package Custom Payment
 * @Author Ankit Gupta
 * @Date 24/6/2014
 * Description: This template will be used to generate the receipt.
 */
session_start();

get_header();

include_once("CommonFunctions.php");

use NCSSERVICES\eg_ws_call;
use NCSSERVICES\Entity;
use NCSSERVICES\Session;
use NCSSERVICES\Member;
use NCSSERVICES\Transaction;
use NCSSERVICES\TransactionFund;

$Error = array();
$Success = array();
$eg_ws = new eg_ws_call();
$token = '';
if (isset($_SESSION["CP-UserToken"]) && trim($_SESSION["CP-UserToken"]) != "") {
    $Username = $_SESSION["CP-UserName"];
    $Password = $_SESSION["CP-UserPassword"];
    $token = $_SESSION["CP-UserToken"];
} else {
    $token = CPGetMemberSession();
}
if (!isset($_SESSION["CP-PaymentAccountNumber"]) || trim($_SESSION["CP-PaymentAccountNumber"]) == "") {
    $ProcessPaymentUrl = get_permalink(get_page_by_path("cp-process-payment-page"));
    wp_redirect($ProcessPaymentUrl);
    exit;
}
if (isset($_GET["SendEmail"]) && trim($_GET["SendEmail"]) == 1) {
    $TransactionId = sanitize_text_field($_GET["TrId"]);
    $eg_ws->method = $EG_SEND_TRANSACTION_EMAIL['method'];
    if ($eg_ws->do_ws_call(sprintf($EG_SEND_TRANSACTION_EMAIL['url'], sanitize_text_field($_GET["TrId"])), '', $token)) {
        $Success[] = 'Email Sent Successfully.';
    } else {
        if (trim($eg_ws->err) == "Status code: 204") {
            $Success[] = 'Email Sent Successfully.';
        } else {
            $Error[] = $eg_ws->err;
        }
    }
}

$FundsListingUrl = get_permalink(get_page_by_path("cp-funds-listing-page"));
?>
<style>
    .red{
        color:red;
    }
    .green{
        color:green;
    }
    .SuccsesMessages, .ErrorMessages{
        text-align:center;
    }
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
        <?php include_once("cp-header.php"); ?> 

        <div style="float:right;">
            <h1 style="font-size:50px;">Receipt</h1>
            <h1 class="receipt-date"><?php echo date("F d, Y"); ?></h1>
        </div>
        <div style="clear:both;"></div>

        <!-- Display the address and transaction details from here... -->
        <div class="TransactionDetails" style="width:100%;margin-bottom:1%;">
            <div class="NameAddress" style="float:left;width:50%;">
                <div style="float:left;width:8%;" class="to-class"><b>To: </b></div>
                <div style="float:left;width:90%;">
                    <div><?php echo $_SESSION["CP-PaymentUserName"]; ?></div>
                    <div><?php echo $_SESSION["CP-PaymentUserAddress"]; ?></div>
                    <div><?php echo $_SESSION["CP-CityStateZip"]; ?></div>
                    <div><?php echo "United States Of America"; ?></div>

                </div>
            </div>
            <div class="TransactionDetails" style="float:right;width:50%;">
                <div>
                    <b>Transaction ID: </b><?php echo sanitize_text_field($_GET["TrId"]); ?>
                </div>
                <?php if (sanitize_text_field($_GET["PmM"]) == "CC") { ?>
                    <div>
                        <b>Payment Method: </b><?php echo $_SESSION["CP-PaymentCompanyName"] . " ****" . substr($_SESSION["CP-PaymentAccountNumber"], -4); ?>
                    </div>
                <?php } else { ?>
                    <div>
                        <b>Bank Details: </b><?php echo $_SESSION["CP-PaymentCompanyName"] . " ****" . substr($_SESSION["CP-PaymentAccountNumber"], -4); ?>
                    </div>
                <?php } ?>

            </div>
            <div style="clear:both;"></div>
        </div>
        <div class="payment-receipt-funds">
            <?php
            $SelectedFunds = unserialize($_SESSION["CP-SelectedFunds"]);
            $TotalAmount = 0;
            foreach ($SelectedFunds as $key => $val) {
                ?>
                <div style="width:90%; border:1px solid #ddd; background:#f9f9f9; padding:2%;margin-bottom:1%;height:25px;">
                    <div style="float:left;width:90%;"><?php echo $val["NAME"]; ?></div>
                    <div style="float:right;width:10%;"><?php echo '$' . $val["AMNT"]; ?></div>
                    <div style="clear:both;"></div>
                    <?php if (isset($_SESSION["CP-UserToken"]) && trim($_SESSION["CP-UserToken"]) != "") { ?>
                        <div style="margin-top: 10px;">
                            <?php
                            $Frequency = "";
                            switch ($val["FREQUENCY"]) {
                                case "Now":
                                    $Frequency = "One time offering";
                                    break;
                                case "one-time":
                                    $Frequency = "One time offering";
                                    if ($val["STARTDATE"] != "")
                                        $Frequency .=" on " . $val["STARTDATE"];
                                    break;
                                case "biweekly":
                                    $Frequency = "Recurring bi-weekly offering";
                                    if ($val["STARTDATE"] != "")
                                        $Frequency .=" from " . $val["STARTDATE"];
                                    if ($val["ENDDATE"] != "")
                                        $Frequency .=" until " . $val["ENDDATE"];
                                    break;
                                case "semi-monthly":
                                    $Frequency = "Recurring semi-monthly offering";
                                    if ($val["STARTDATE"] != "")
                                        $Frequency .=" from " . $val["STARTDATE"];
                                    if ($val["ENDDATE"] != "")
                                        $Frequency .=" until " . $val["ENDDATE"];
                                    break;
                                case "monthly":
                                    $Frequency = "Recurring monthly offering";
                                    if ($val["STARTDATE"] != "")
                                        $Frequency .=" from " . $val["STARTDATE"];
                                    if ($val["ENDDATE"] != "")
                                        $Frequency .=" until " . $val["ENDDATE"];
                                    break;
                                case "quarterly":
                                    $Frequency = "Recurring quarterly offering";
                                    if ($val["STARTDATE"] != "")
                                        $Frequency .=" from " . $val["STARTDATE"];
                                    if ($val["ENDDATE"] != "")
                                        $Frequency .=" until " . $val["ENDDATE"];
                                    break;
                            }
                            echo $Frequency;
                            ?>
                        </div>
                    <?php }
                    ?>
                </div>
                <?php
                $TotalAmount += ltrim($val["AMNT"], '$');
            }
            ?>
        </div>
        <div class="MailDetials" style="width:100%;margin-top:1%;" >
            <div style="width:80%;float:left;">
                <input class="email-info" id="EmailInfo" name="EmailInfo" type="button" value="Email To: <?php echo $_SESSION["CP-PaymentUserEmailAddress"]; ?>" />
            </div>
            <div style="width:20%;float:right;">
                <b>Total: </b><?php echo "$" . $TotalAmount; ?>
            </div>
            <div style="clear:both;"></div>
        </div>
        <center>
            <a href="<?php echo $FundsListingUrl; ?>" style="text-decoration:none;"><input type="button" value="Back to Funds >" id="BckBtn" name="BckBtn" /></a>
        </center>
        <!--        </div> #content -->
    </div><!-- #primary -->
</div><!-- #main-content -->
<script>
    jQuery.noConflict();
    jQuery(document).ready(function() {
        jQuery("#EmailInfo").click(function() {
            Url = document.URL;
            Href = addParameter(Url, 'SendEmail', 1, false);
            window.location = Href;
        });
    });

    /*Function to add parameters... */
    function addParameter(url, parameterName, parameterValue, atStart/*Add param before others*/) {
        replaceDuplicates = true;
        if (url.indexOf('#') > 0) {
            var cl = url.indexOf('#');
            urlhash = url.substring(url.indexOf('#'), url.length);
        } else {
            urlhash = '';
            cl = url.length;
        }
        sourceUrl = url.substring(0, cl);

        var urlParts = sourceUrl.split("?");
        var newQueryString = "";

        if (urlParts.length > 1)
        {
            var parameters = urlParts[1].split("&");
            for (var i = 0; (i < parameters.length); i++)
            {
                var parameterParts = parameters[i].split("=");
                if (!(replaceDuplicates && parameterParts[0] == parameterName))
                {
                    if (newQueryString == "")
                        newQueryString = "?";
                    else
                        newQueryString += "&";
                    newQueryString += parameterParts[0] + "=" + (parameterParts[1] ? parameterParts[1] : '');
                }
            }
        }
        if (newQueryString == "")
            newQueryString = "?";

        if (atStart) {
            newQueryString = '?' + parameterName + "=" + parameterValue + (newQueryString.length > 1 ? '&' + newQueryString.substring(1) : '');
        } else {
            if (newQueryString !== "" && newQueryString != '?')
                newQueryString += "&";
            newQueryString += parameterName + "=" + (parameterValue ? parameterValue : '');
        }
        return urlParts[0] + newQueryString + urlhash;
    };

</script>
<?php
get_footer();

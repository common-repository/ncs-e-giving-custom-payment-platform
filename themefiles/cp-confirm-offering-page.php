<?php
/**
 * Template Name: Confirm Offering Page.
 *
 * @package Custom Payment
 * @Author Ankit Gupta
 * @Date 24/6/2014
 * Description: This template will be used to confirm with the user funds selected by him.
 */
session_start();

get_header();

include_once("CommonFunctions.php");

use NCSSERVICES\eg_ws_call;
use NCSSERVICES\Entity;
use NCSSERVICES\Session;
use NCSSERVICES\Member;

$eg_ws = new eg_ws_call();
$token = "";
// Make sure we don't expose any info if called directly
if (!function_exists('add_action')) {
    echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
    exit;
}
$SelectedFundsAmount = array();
$SessionFunds = unserialize($_SESSION["CP-SelectedFunds"]);


if (!empty($_POST)) {
    foreach ($_POST["amnt_box"] as $key => $val) {
        $SessionFunds[$key]["AMNT"] = $val;
        $SessionFunds[$key]["FREQUENCY"] = trim($_POST["Frequency_" . $key]);
        $SessionFunds[$key]["STARTDATE"] = trim($_POST["StartDate_" . $key]);
        $SessionFunds[$key]["ENDDATE"] = trim($_POST["EndDate_" . $key]);
    }
    $_SESSION["CP-SelectedFunds"] = serialize($SessionFunds);
}
$SelectedFundsAmount = unserialize($_SESSION["CP-SelectedFunds"]);

$TotalAmount = 0;

$CartUrl = get_permalink(get_page_by_path("cp-cart-page"));
$ProcessPaymentUrl = get_permalink(get_page_by_path("cp-process-payment-page"));
$FundsListUrl = get_permalink(get_page_by_path("cp-funds-listing-page"));
?>
<style>
    .red{color:red;}
    .Buttons{text-align: center;}
</style>
<div id="main-content" class="main-content">
    <div id="primary" class="content-area side-content-area">
        <!--        <div id="content" class="site-content" role="main">-->
        <!--Display header from here...-->
        <?php include_once("cp-header.php"); ?> 

        <form id="FundsConfirmationForm" name="FundsConfirmationForm" method="post" action="<?php echo $ProcessPaymentUrl; ?>">
            <div style="width:100%;height: 250px; overflow-x: hidden; overflow-y:auto; ">
                <?php
                if (!empty($SelectedFundsAmount)) {
                    foreach ($SelectedFundsAmount as $key => $val) {
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
                }else { ## if no funds are selected... 
                    ?>
                    <div class="red">No funds selected.</div>

                <?php }
                ?>
                <div style="clear:both;"></div>     
            </div>

            <div style="float:right;width:25%;">
                <b>Total: $<?php echo $TotalAmount; ?></b> 
            </div>

            <center>
                <div class="Buttons">
                    <?php if (!empty($SelectedFundsAmount)) { ?>
                        <a href="<?php echo $CartUrl; ?>" style="text-decoration:none;"><input type="button" value="< Back to Cart" id="BckBtn" name="BckBtn" /></a>
                        <input type="submit" value="Next >" id="NextBtn" name="NextBtn" />
                    <?php } else { ?>
                        <a href="<?php echo $FundsListUrl; ?>" style="text-decoration: none;"><input type="button" id="BackBtn" name="BackBtn" value=" < Back to Funds Listing" /></a>
                        <?php } ?>
            </center> 
        </form>
        <!--        </div> #content -->
    </div><!-- #primary -->
</div><!-- #main-content -->
<?php
get_footer();
?>
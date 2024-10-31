<?php
/**
 * Template Name: Funds Listing Template
 *
 * @package Custom Payment
 * @Author Ankit Gupta
 * @Date 19/6/2014
 * Description: This template will be used to display the funds list for authenticated and anonymous users.
 */
session_start();

get_header();
include_once("CommonFunctions.php");

use NCSSERVICES\eg_ws_call;
use NCSSERVICES\Entity;
use NCSSERVICES\Session;
use NCSSERVICES\Fund;

global $Error;
$Error = array();
$eg_ws = new eg_ws_call();
$token = '';

$UserLogin = 0;
if (isset($_SESSION["CP-UserToken"]) && !empty($_SESSION["CP-UserToken"])) {
    $UserLogin = 1;
}

## function to validate the amount and funds selected...
if (isset($_REQUEST["Pay"]) && trim($_REQUEST["Pay"]) != "") {
    $Pay = base64_decode(urldecode($_REQUEST["Pay"]));
    $PayList = explode("_", $Pay);
    if (trim($PayList[0]) != "Amount") {
        $Error[] = "Invalid payment request.";
    } else if (!is_numeric($PayList[1])) {
        $Error[] = "Invalid payment amount.";
    } else if (!is_numeric($PayList[2])) {
        $Error[] = "Invalid fund selected.";
    }
}

## if valid amount and fund is chosen add it to the session...
if (empty($Error)) {
    if (!empty($PayList)) {
        if (isset($_SESSION["CP-UserToken"]) && $_SESSION["CP-UserToken"] != "") {
            $token = $_SESSION["CP-UserToken"];
        } else {
            $token = CPGetMemberSession();
        }
        AddFunds($PayList, $eg_ws, $EG_FUND_DETAILS, $token);
    }
}

## function to add the amount and funds to the session

function AddFunds($PayList, $eg_ws, $EG_FUND_DETAILS, $token) {
    global $Error;

    ## get list of funds on the basis of the token...
    $eg_ws->method = $EG_FUND_DETAILS['method'];
    $out = $eg_ws->do_ws_call($EG_FUND_DETAILS['url'] . "/" . $PayList[2], '', $token);

    if ($out) {
        $FundsDetails = json_decode($out);
    } else {
        $Error[] = $eg_ws->err;
    }

    if (!isset($_SESSION["CP-SelectedFunds"])) {
        $temp[$PayList[2]] = array("AMNT" => $PayList[1], "NAME" => $FundsDetails->Name);
        $_SESSION["CP-SelectedFunds"] = serialize($temp);
    } else {
        $Previous = unserialize($_SESSION["CP-SelectedFunds"]);
        $Previous[$PayList[2]] = array("AMNT" => $PayList[1], "NAME" => $FundsDetails->Name, "TAXDEDUCTIBLE" => $FundsDetails->TaxDeductible);
        $_SESSION["CP-SelectedFunds"] = serialize($Previous);
    }
}

## display the list of the funds...
if (isset($_SESSION["CP-SelectedFunds"]) && !empty($_SESSION["CP-SelectedFunds"])) {
    $SelectedFunds = unserialize($_SESSION["CP-SelectedFunds"]);
}
/*wp_enqueue_script('script-validation-rules', plugins_url('CustomPayment/assets/js/jquery-ui-1.8.21.custom.min.js'), array(), '1.0.0', true);
wp_enqueue_style("style-validation", plugins_url('CustomPayment/assets/css/jquery-ui-1.8.21.custom.css'));*/
wp_enqueue_script('jquery-ui-datepicker', '/wp-includes/js/jquery/ui/jquery.ui.datepicker.min.js', array('jquery'));    
wp_enqueue_style("style-validation", plugins_url('CustomPayment/assets/css/jquery-ui-1.8.21.custom.css'));



$closeImage = plugins_url('CustomPayment/assets/images/close.png');
$FundsListingUrl = get_permalink(get_page_by_path("cp-funds-listing-page"));

## Add filter arguments from here...
if (isset($_REQUEST["Filter"])) {
    $FundsListingUrl = add_query_arg('Filter', trim($_REQUEST["Filter"]), $FundsListingUrl);
}
$ShoppingConfirmationUrl = get_permalink(get_page_by_path("cp-confirm-offering-page"));

$TotalItems = 0;
$TotalAmount = 0;

$PaymentFrequencyList = array(
    "Now" => "One-Time Now",
    "one-time" => "One-time Future Date",
    "biweekly" => "Every Other Week",
    "semi-monthly" => "Semi-Monthly (1st & 15th)",
    "monthly" => "Monthly",
    "quarterly" => "Quarterly"
);
?>
<style>
    .payment-date-selector img { margin-left: 10px;}
    .payment-date-selector input { width: 100px;}
    .FundName{margin-bottom:1%; }
    .red{color:red;}
    .Buttons{margin-top:10px;}
    .Close{cursor:pointer;}
</style>
<div id="main-content" class="main-content plugin-content">
    <div id="primary" class="content-area side-content-area">
        <!--        <div id="content" class="site-content" role="main">-->
        <!--Display the errors from here... -->
        <?php if (!empty($Error)) { ?>
            <div class="ErrorMessages">
                <?php foreach ($Error as $key => $val) { ?>
                    <div class="red"><?php echo $val; ?></div>
                <?php } ?>
            </div>    
        <?php }
        ?>

        <!--Display header from here...-->
        <?php include_once("cp-header.php"); ?> 
        <form id="FundsSelectionForm" name="FundsSelectionForm" method="post" action="<?php echo $ShoppingConfirmationUrl; ?>">
            <div style="display:none;" id="NoFundsError" class="red">No funds found.</div>
            <?php
            if (isset($_SESSION["CP-UserToken"])) {
                ?>
                <div style="width:100%; height:350px; overflow-x: hidden; overflow-y:auto;">
                    <?php
                    if (!empty($SelectedFunds)) {
                        foreach ($SelectedFunds as $key => $val) {
                            $TotalItems++;
                            $TotalAmount += $val["AMNT"];
                            ?>
                            <div id="Container_<?php echo $key; ?>" class="MainContainer" style="width:95%; border:1px solid #ddd; background:#f9f9f9; padding:2%;margin-bottom:1%;height:auto;">

                                <div class="name-frequency-container" style="float:left; width:60%;">
                                    <div class="FundName">
                                        <?php echo $val["NAME"]; ?>
                                    </div>
                                    <div class="payment-frequency-selector" style=" margin-bottom: 20px;">
                                        <span> Schedule: </span>
                                        <select class="payment_frequency_select" id="Frequency_<?php echo $key; ?>" name="Frequency_<?php echo $key; ?>">
                                            <?php
                                            foreach ($PaymentFrequencyList as $Freq => $FreqData) {
                                                $SelectedFreq = "";
                                                if ($val["FREQUENCY"] == $Freq)
                                                    $SelectedFreq = "selected";
                                                ?>

                                                <option value="<?php echo $Freq; ?>" <?php echo $SelectedFreq; ?>><?php echo $FreqData; ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div> <!-- name-frequency-container -->

                                <div class="amount-container" style="float:left; width:40%;">
                                    <div style="float:left;width:65%">
                                        <div style="margin-bottom:2%;">
                                            <label>$</label><input type="text" class="amnt_box" id="amnt_box_<?php echo $key; ?>" name="amnt_box[<?php echo $key; ?>]" value="<?php echo $val["AMNT"]; ?>" maxlength="8" /> 

                                        </div>
                                        <div id="total_<?php echo $key; ?>">

                                        </div>
                                    </div>

                                    <div style="float:right;width:35%">
                                        <img class="Close" id="Close_<?php echo $key; ?>" src="<?php echo $closeImage; ?>" alt="Remove fund" title="Remove Fund" />
                                        <div class="CloseConfirm" Id="CloseConfirm_<?php echo $key; ?>" style="display: none;">
                                            <div style="margin-bottom:10px;">Are you sure?</div>
                                            <input type="button" class="CloseConfirmYes" value="Yes" Id="CloseConfirm_Yes_<?php echo $key; ?>"  />
                                            <input type="button" class="CloseConfirmNo" value="No" Id="CloseConfirm_No_<?php echo $key; ?>"  />
                                        </div>

                                    </div>
                                </div>    <!-- amount-container -->

                                <div style="clear:both;"></div>   

                                <div class="date-container" style="width:100%; ">
                                    <?php
                                    $Display = "none";
                                    if (isset($val["FREQUENCY"]) && $val["FREQUENCY"] != "Now" && $val["FREQUENCY"] != "") {
                                        $Display = "";
                                    }
                                    $EndDateDispaly = "";
                                    if ($val["FREQUENCY"] == "one-time" || $val["FREQUENCY"] == "Now") {
                                        $EndDateDispaly = "none";
                                    }
                                    if ($val["STARTDATE"] == "" && $val["FREQUENCY"] != "Now") {
                                        $val["STARTDATE"] = date("m/d/Y");
                                    }
                                    ?>
                                    <div class="payment-date-selector" id="payment_date_selector_<?php echo $key; ?>" style="display:<?php echo $Display; ?>;">
                                        <div style="float:left;width:30%;">
                                            Start date: <input type="text" class="StartDate" id="StartDate_<?php echo $key; ?>" name="StartDate_<?php echo $key; ?>" readonly="true" value="<?php echo $val["STARTDATE"]; ?>"  />    
                                        </div>
                                        <div style="float:left;width:40%;display: <?php echo $EndDateDispaly ?>;" id="EndDateContainer_<?php echo $key; ?>" >
                                            End date (optional): <input type="text" class="EndDate" id="EndDate_<?php echo $key; ?>" name="EndDate_<?php echo $key; ?>" readonly="true" value="<?php echo $val["ENDDATE"]; ?>"  />        
                                        </div>
                                        <div style="clear:both;"></div>
                                        <div class="red" style="display: none;" id="DateError_<?php echo $key; ?>">Please select proper date range.</div> 
                                    </div> 
                                    <div style="clear:both;"></div>
                                </div> <!-- date-container -->

                            </div>
                            <?php
                        }
                    } else {
                        ## if no funds are selected... 
                        ?>
                        <div class="red">No funds selected.</div>
                    <?php }
                    ?>
                </div>   
                <div style="float:right;width:25%;">
                    <span id="FinalTotalItems"><?php echo $TotalItems; ?></span> items totaling at least <span id="FinalTotalAmount"><?php echo "$" . $TotalAmount; ?></span>
                </div>

                <?php
            }
            /* if the anonymous user is selecting the funds... */ else {
                ?>
                <div style="width:100%;height: 350px; overflow-x: hidden; overflow-y:auto; ">
                    <?php
                    if (!empty($SelectedFunds)) {
                        foreach ($SelectedFunds as $key => $val) {
                            $TotalItems++;
                            $TotalAmount += $val["AMNT"];
                            ?>
                            <div class="MainContainer" id="Container_<?php echo $key; ?>" style="width:95%; border:1px solid #ddd; background:#f9f9f9; padding:2%;margin-bottom:1%;height:auto;">
                                <div class="name-frequency-container" style="float:left; width:60%;">
                                    <div class="FundName">
                                        <?php echo $val["NAME"]; ?>
                                    </div>
                                </div> <!-- name-frequency-container -->

                                <div class="amount-container" style="float:left; width:40%;">
                                    <div style="float:left;width:65%">
                                        <div style="margin-bottom:2%;">
                                            $<input type="text" class="amnt_box" id="amnt_box_<?php echo $key; ?>" name="amnt_box[<?php echo $key; ?>]" value="<?php echo $val["AMNT"]; ?>" maxlength="8" /> 
                                        </div>
                                        <div id="total_<?php echo $key; ?>">
                                        </div>
                                    </div>

                                    <div style="float:right;width:35%">
                                        <img class="Close" id="Close_<?php echo $key; ?>" src="<?php echo $closeImage; ?>" />
                                        <div class="CloseConfirm" Id="CloseConfirm_<?php echo $key; ?>" style="display: none;">
                                            <div style="margin-bottom:10px;">Are you sure?</div>
                                            <input type="button" class="CloseConfirmYes" value="Yes" Id="CloseConfirm_Yes_<?php echo $key; ?>"  />
                                            <input type="button" class="CloseConfirmNo" value="No" Id="CloseConfirm_No_<?php echo $key; ?>"  />
                                        </div>
                                    </div>
                                </div>    <!-- amount-container -->   
                                <div style="clear:both"></div>
                            </div>
                            <div style="clear:both"></div>
                            <?php
                        }
                    } else {
                        ## if no funds are selected... 
                        ?>
                        <div class="red">No funds selected.</div>
                    <?php }
                    ?>
                </div>   
                <div style="float:right;width:25%;">
                    <span id="FinalTotalItems"> <?php echo $TotalItems; ?></span> items totaling <span id="FinalTotalAmount"><?php echo "$" . $TotalAmount; ?></span>
                </div>
            <?php }
            ?>

            <div style="clear:both;"></div>

            <center>
                <div class="Buttons">
                    <input type="button" value="< Back to Funds" id="BckBtn" name="BckBtn" onclick="window.location.assign('<?php echo $FundsListingUrl; ?>')" />
                    <?php if (!empty($SelectedFunds)) { ?>
                        <input type="submit" value="Next >" id="NextBtn" name="NextBtn" />
                    <?php } ?>
            </center>    

        </form>
        <!--        </div> #content -->
    </div><!-- #primary -->
</div><!-- #main-content -->


<script>
    jQuery.noConflict();
    jQuery(document).ready(function() {

        /*Enable/disable date selection from here...*/
        jQuery(".payment_frequency_select").change(function() {
            var Id = jQuery(this).attr("id");
            var IdList = Id.split("_");
            var payment_date_selector = "payment_date_selector_" + IdList[1];
            var StartDateId = "StartDate_" + IdList[1];
            var EndDateId = "EndDate_" + IdList[1];
            if (jQuery(this).val() != "Now") {
                jQuery("#" + payment_date_selector).show();
                if (jQuery(this).val() == "one-time") {
                    jQuery("#EndDateContainer_" + IdList[1]).hide();
                } else {
                    jQuery("#EndDateContainer_" + IdList[1]).show();
                }
            } else {
                jQuery("#" + payment_date_selector).hide();
            }
        });

        /*function to as for confirmation to delete the fund..*/
        jQuery(".Close").click(function() {
            var Id = jQuery(this).attr("id");
            var IdList = Id.split("_");
            var CurId = "CloseConfirm_" + IdList[1];
            jQuery(this).hide();
            jQuery("#" + CurId).show();
        });

        /*call function to delete fund if yes is clicked...*/
        jQuery(".CloseConfirmYes").live("click", function() {
            var Id = jQuery(this).attr("id");
            var IdList = Id.split("_");
            var FundId = +IdList[2];
            DeleteFund(FundId);
        });

        /*display corss if no is clicked...*/
        jQuery(".CloseConfirmNo").live("click", function() {
            var Id = jQuery(this).attr("id");
            var IdList = Id.split("_");
            var FundId = +IdList[2];
            jQuery("#CloseConfirm_" + FundId).hide();
            jQuery("#Close_" + FundId).show();
        });

        /*function to Delete Fund...*/
        function DeleteFund(CurId) {
            var Path = '<?php echo get_site_url(); ?>';
            jQuery.ajax({
                type: 'POST',
                url: Path + '/wp-admin/admin-ajax.php',
                data: {
                    action: 'remove_funds_ajax_function', // the PHP function to run
                    fund: CurId,
                },
                success: function(data, textStatus, XMLHttpRequest) {
                    jQuery("#Container_" + CurId).remove();
                    if (jQuery("#FundsSelectionForm").find(".MainContainer").length <= 0) {
                        jQuery("#NextBtn").remove();
                        jQuery("#NoFundsError").show();
                    }
                    var FinalTotalItems = 0;
                    var FinalTotalAmount = 0;
                    jQuery(".amnt_box").each(function() {
                        var amount = jQuery(this).val();
                        if (!isNaN(amount) || amount > 0) {
                            FinalTotalAmount = Number(FinalTotalAmount) + Number(amount);
                        }
                        FinalTotalItems++;
                    });
                    FinalTotalAmount = +FinalTotalAmount.toFixed(2);
                    jQuery("#FinalTotalAmount").html('$' + FinalTotalAmount);
                    jQuery("#FinalTotalItems").html(FinalTotalItems);
                },
                error: function(XMLHttpRequest, textStatus, errorThrown) {
                    if (typeof console === "undefined") {
                        console = {
                            log: function() {
                            },
                            debug: function() {
                            },
                        };
                    }
                    if (XMLHttpRequest.status == 404) {
                        console.log('Element not found.');
                    } else {
                        console.log('Error: ' + errorThrown);
                    }
                }
            });
        }
        /* Change in amount from textbox... */
        jQuery(".amnt_box").keyup(function() {
            var amount = jQuery(this).val();
            var Id = jQuery(this).attr("id");
            var IdList = Id.split("_");
            var CurId = IdList[2];

            if (isNaN(amount) || amount <= 0) {
                if (jQuery("#total_" + CurId).length)
                    jQuery("#total_" + CurId).html('<span class="red">Invalid amount.</span>');
            } else {
                jQuery("#total_" + CurId).html('');
            }

            /* Calculate the final total amount from here... */
            var FinalTotalAmount = 0;
            jQuery(".amnt_box").each(function() {
                var amount = jQuery(this).val();
                if (!isNaN(amount) || amount > 0) {
                    FinalTotalAmount = Number(FinalTotalAmount) + Number(amount);
                }
            });
            //FinalTotalAmount = +FinalTotalAmount.toFixed(2);
            jQuery("#FinalTotalAmount").html('$' + FinalTotalAmount);
        });

    });  /* Ready function ends here... */

    /*Form Validations added from here...*/
    jQuery("#FundsSelectionForm").submit(function(e) {
        var IsError = 0;
        if (jQuery(".payment-date-selector").length > 0) {
            jQuery(".payment-date-selector").each(function() {
                if (jQuery(this).css("display") != "none") {
                    var Id = jQuery(this).attr("id");
                    var IdList = Id.split("_");
                    var CurId = IdList[3];

                    var StartDate = jQuery("#StartDate_" + CurId).val();
                    var EndDate = jQuery("#EndDate_" + CurId).val();

                    if (EndDate != "") {
                        if (new Date(StartDate).getTime() > new Date(EndDate).getTime()) {
                            IsError = 1;
                            jQuery("#DateError_" + CurId).css("display", "block");
                            jQuery("#DateError_" + CurId).focus();
                        }
                    }
                }
            });
        }

        /*check if funds amount are entered as numeric...*/
        jQuery(".amnt_box").each(function() {
            var amount = jQuery(this).val();
            var Id = jQuery(this).attr("id");
            var IdList = Id.split("_");
            var CurId = IdList[2];

            if (isNaN(amount) || amount <= 0) {
                if (jQuery("#total_" + CurId).length)
                    jQuery("#total_" + CurId).html('<span class="red">Invalid amount.</span>');
                IsError = 1;
            }
        });

        if (IsError != 0) {
            e.preventDefault();
            return false;
        }
    });

    /*Datepicker added from here...*/
    jQuery(function() {
        var calendar = '<?php echo plugins_url('CustomPayment/assets/images/calendar.gif'); ?>';
        var date = new Date();
        // Datepicker
        jQuery('.StartDate').datepicker({
            showOn: "button",
            buttonImage: calendar,
            buttonImageOnly: true,
            changeMonth: true,
            changeYear: true,
            minDate: date
        });
        jQuery('.EndDate').datepicker({
            showOn: "button",
            buttonImage: calendar,
            buttonImageOnly: true,
            changeMonth: true,
            changeYear: true,
            minDate: date
        });
    });

</script>
<?php
get_footer();
?>
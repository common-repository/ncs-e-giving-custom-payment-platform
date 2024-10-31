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

use NCSSERVICES\eg_ws_call ;
use NCSSERVICES\Entity ;
use NCSSERVICES\Session ;
use NCSSERVICES\Member ;
use NCSSERVICES\Fund ;


$Error = array();
$eg_ws = new eg_ws_call();
$token = '';
## check if user is logged in than use logged in user's username and password...
if (isset($_SESSION["CP-UserToken"]) && trim($_SESSION["CP-UserToken"]) != "") {
    $Username = $_SESSION["CP-UserName"];
    $Password = $_SESSION["CP-UserPassword"];
    $token = $_SESSION["CP-UserToken"];
} else {
    $token = CPGetMemberSession();
}

## get list of funds on the basis of the token...
$eg_ws->method = $EG_LIST_FUND['method'];
$out = $eg_ws->do_ws_call($EG_LIST_FUND['url'], '', $token);
if ($out) {
    $FundsList = json_decode($out);
} else {
    $Error[] = $eg_ws->err;
}

## List Filters...
$FiltersList = array('ABC', 'DEF', 'GHI', 'JKL', 'MNO', 'PQR', 'STU', 'VWXYZ');
$PrevRequestFilter=sanitize_text_field($_GET["Filter"]);
## function to scan the filters...
foreach($FiltersList as $key=>$val){
    $_REQUEST["Filter"] = $val;
    $List = array_filter($FundsList, "FilterFunds");
    if(count($List)==0){
        $ArKey= array_keys ($FiltersList,$val);
        unset($FiltersList[$ArKey[0]]);
    }
}

if($PrevRequestFilter=="")
    $_REQUEST["Filter"]="";
else{
    $_REQUEST["Filter"] = $PrevRequestFilter;
}
## function to create the filter links...
function CreateFilterLinks($FilterStr) {
    if ($FilterStr != "")
        $Url = add_query_arg('Filter', $FilterStr, get_permalink(get_page_by_path("cp-funds-listing-page")));
    else
        $Url = get_permalink(get_page_by_path("cp-funds-listing-page"));

    return $Url;
}

## function to create payment links...

function CreatePaymentLinks($Id = "") {
    if ($Id != "")
        $Url = add_query_arg('Pay', urlencode(base64_encode($Id)), get_permalink(get_page_by_path("cp-cart-page")));
    else
        $Url = get_permalink(get_page_by_path("cp-cart-page"));

    ## Add filter arguments from here...
    if (isset($_REQUEST["Filter"])) {
        $Url = add_query_arg('Filter', trim($_REQUEST["Filter"]), $Url);
    }
    return $Url;
}

## function to filter the funds on the basis of the filter selected...

function FilterFunds($val) {
    if($filter=="")
        $filter = sanitize_text_field($_REQUEST["Filter"]);
    switch ($filter) {
        case "ABC":
            $RegEx = '/^a.|^b.|^c./';
            break;
        case "DEF":
            $RegEx = '/^d.|^e.|^f./';
            break;
        case "GHI":
            $RegEx = '/^g.|^h.|^i./';
            break;
        case "JKL":
            $RegEx = '/^j.|^k.|^l./';
            break;
        case "MNO":
            $RegEx = '/^m.|^n.|^o./';
            break;
        case "PQR":
            $RegEx = '/^p.|^q.|^r./';
            break;
        case "STU":
            $RegEx = '/^s.|^t.|^u./';
            break;
        case "VWXYZ":
            $RegEx = '/^v.|^w.|^x.|^y.|^z./';
            break;
    }

    return preg_match($RegEx, strtolower($val->Name));
}

## get the filtered list from here if any filter is set...
$filter = sanitize_text_field($_REQUEST["Filter"]);
if ($filter != "") {
    $FilteredList = array_filter($FundsList, "FilterFunds");
    $FundsList = $FilteredList;
}

## function to sort the funds list alphabetically...

function cmp($a, $b) {
    return strcmp($a->Name, $b->Name);
}

usort($FundsList, "cmp");

## get the list of selected funds...
$SelectedFunds = unserialize($_SESSION["CP-SelectedFunds"]);

## image urls...
$TickImgUrl = plugins_url('CustomPayment/assets/images/tick.png');
?>
<style>
    .red{
        color:red;
    }
    .CustomAmountBox{
        width:75px !important;
    }
    .CustomAmountConatiner{
        margin:2% 0; 
    }
</style>
<div id="main-content" class="main-content">
    <div id="primary" class="content-area side-content-area" style="text-align: normal !important;">
        <!--        <div id="content" class="site-content" role="main">-->
<?php if (!empty($Error)) { ?>
            <div class="ErrorMessages">
            <?php foreach ($Error as $key => $val) { ?>
                    <div class="red"><?php echo $val; ?></div>
                <?php } ?>
            </div>    
            <?php }
            ?>

        <?php include_once("cp-header.php"); ?> 
        <center>
            <div style="width:60%;padding-bottom:4%;" class="Filters">
        <?php
        foreach ($FiltersList as $fltr) {
            $FilterSelection = "";
            if ($fltr == $filter)
                $FilterSelection = "font-weight:bold;";
            ?>
                    <div style="float:left;width:10%;">
                        <a style="<?php echo $FilterSelection; ?>" href="<?php echo CreateFilterLinks($fltr); ?>"><?php echo $fltr; ?></a> |
                    </div>
<?php
}
$AllFilter = "";
if ($filter == "") {
    $AllFilter = "font-weight:bold;";
}
?>
                <div style="float:left;width:10%;">
                    <a style="<?php echo $AllFilter; ?>" href="<?php echo CreateFilterLinks(""); ?>">All</a>
                </div>
            </div>
        </center>
        <div style="width:100%;height: 250px; overflow-x: hidden; overflow-y:auto; ">
            <?php
            if (!empty($FundsList)) {
                foreach ($FundsList as $key => $val) {
                    ?>

                    <div style="width:95%; border:1px solid #ddd; background:#f9f9f9; padding:2%;">
                        <div style="float:left; width:60%;">
                            <?php echo $val->Name; ?> 
                            <div style="margin-top:5px;">
                                <?php
                                if (isset($SelectedFunds[$val->ID])) {
                                    echo '<img src="' . $TickImgUrl . '" style="width:15px;" />' . '$' . $SelectedFunds[$val->ID]["AMNT"];
                                }
                                ?>
                            </div>
                        </div> 

                        <div style="float:left; width:40%;">
                            <div style="float:left;width:15%">
                                <a href="<?php echo CreatePaymentLinks("Amount_25_" . $val->ID); ?>" id="Amount_25_<?php echo $val->ID; ?>">$25</a> |
                            </div>
                            <div style="float:left;width:15%">
                                <a href="<?php echo CreatePaymentLinks("Amount_50_" . $val->ID); ?>" id="Amount_50_<?php echo $val->ID; ?>">$50</a> |
                            </div>
                            <div style="float:left;width:15%">
                                <a href="<?php echo CreatePaymentLinks("Amount_75_" . $val->ID); ?>" id="Amount_75_<?php echo $val->ID; ?>">$75</a> |
                            </div>
                            <div style="float:left;width:15%">
                                <a href="<?php echo CreatePaymentLinks("Amount_100_" . $val->ID); ?>" id="Amount_100_<?php echo $val->ID; ?>">$100</a> |
                            </div>
                            <div style="float:left;width:35%">
                                <a href="javascript:void(0);" class="OtherAmount" id="Amount_Other_<?php echo $val->ID; ?>">Other</a>
                                <div class="CustomAmountConatiner" id="CustomAmountConatiner_<?php echo $val->ID; ?>" style="display:none;">
                                    <input type="text" id="CustomAmount_<?php echo $val->ID; ?>" name="CustomAmount_<?php echo $val->ID; ?>" class="CustomAmountBox" maxlength="8" /> 
                                    <a href="javascript:void(0);" id="CustomAmountLink_<?php echo $val->ID; ?>">Add</a>
                                    <span style="display:none;" class="red" id="CustomAmountValidate_<?php echo $val->ID; ?>">Please enter valid amount.</span>
                                </div>    
                            </div>


                        </div>
                        <div style="clear:both;"></div>
                    </div>
                    <?php
                }
            } else {
                ?>
                <span class="red">No funds found.</span>
            <?php }
            ?>
        </div>
        <?php if (isset($_SESSION["CP-SelectedFunds"]) && !empty($SelectedFunds)) { ?>
            <center style="margin-top:5px">
                <a href="<?php echo CreatePaymentLinks(); ?>" style="text-decoration: none;"><input type="button" value="Back To Shopping Cart >" /></a> 
            </center>     


            <?php
        }
        ?>
        <!--        </div> #content -->
    </div><!-- #primary -->
</div><!-- #main-content -->
<script>
    jQuery.noConflict();
    jQuery(document).ready(function() {

        /* function to display the other checkbox amount... */
        jQuery(".OtherAmount").click(function() {
            var CurId = jQuery(this).attr("id");
            var IdElements = CurId.split("_");
            var ID = IdElements[2];
            jQuery("#CustomAmountConatiner_" + ID).show("slow");
        });

        /* function to validate the amount entered in the amount box... */
        jQuery(".CustomAmountBox").keyup(function() {
            var CurId = jQuery(this).attr("id");
            var IdElements = CurId.split("_");
            var ID = IdElements[1];
            var Amount = jQuery(this).val();

            if (isNaN(Amount) || Amount == "" || Amount <= 0) {
                jQuery("#CustomAmountValidate_" + ID).show();
                jQuery("#CustomAmountLink_" + ID).attr("href", "javascript:void(0);");
            } else {
                var Href = '<?php echo CreatePaymentLinks(); ?>'
                Href =addParameter(Href,'Pay',encodeURIComponent(btoa("Amount_" + Amount + "_" + ID)),false);
                jQuery("#CustomAmountLink_" + ID).attr("href", Href);
                jQuery("#CustomAmountValidate_" + ID).hide();
            }
        });
    });


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
    }
    ;

</script>
<?php
//get_sidebar();
get_footer();
?>
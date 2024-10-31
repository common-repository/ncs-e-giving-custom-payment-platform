<?php
/**
 * Template Name: Common Header Template file
 *
 * @package Custom Payment
 * @Author Ankit Gupta
 * @Date 18/6/2014
 * Description: This template will be used to display header in all the pages.
 */
session_start();

if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 1740)) {
    
    ## Regenerate session from here...
   if(isset($_SESSION["CP-UserID"])){
       $RUserName= $_SESSION["CP-UserName"];
       $RPassword= $_SESSION["CP-UserPassword"];
       $Key = $AdminDetails["admin_token"];
       $sess = new \NCSSERVICES\Session($AdminDetails["admin_token"], $RUserName, $RPassword); //MEMBER CREDENTIALS
        $EG_START_MEMBER_SESSION = $sess->MemberSession();
        $eg_ws->method = $EG_START_MEMBER_SESSION['method'];
        $Rout = $eg_ws->do_ws_call($EG_START_MEMBER_SESSION['url'], $sess->toJson());
        if ($Rout) {
            $Rresponse = json_decode($Rout);
            $Rtoken = $Rresponse->Token;
        }
        $_SESSION["CP-UserToken"] = $Rtoken;
        $_SESSION["LAST_ACTIVITY"]=time();
   }else if(isset( $_SESSION["CP-GENERALADMINSESSIONTOKEN"]) && trim( $_SESSION["CP-GENERALADMINSESSIONTOKEN"])!=""){
       unset($_SESSION["CP-GENERALADMINSESSIONTOKEN"]);
       $Rtoken = CPGetMemberSession();
       $_SESSION["LAST_ACTIVITY"]=time();
   }
    
    // last request was more than 29 minutes ago
}else if(!isset($_SESSION["LAST_ACTIVITY"])){
    $_SESSION["LAST_ACTIVITY"]=time();
}

$LoginUrl = get_permalink(get_page_by_path("cp-login-page"));
$CreateMemberUrl = get_permalink(get_page_by_path("cp-create-account-page"));
$LogoutUrl = add_query_arg('logout', '1', get_permalink(get_page_by_path("cp-login-page")));

global $post;
$post_slug = $post->post_name;
$ShowLoginLink = 1;
$ShowCreateLink = 1;
$ShowLogoutLink = 0;
$ShowPrintLink = 0;
$PageTitle = "";
$PageTitle1 = "";
$Padding_bottom = "";
switch ($post_slug) {
    case "cp-create-account-page":
        $ShowLoginLink = 0;
        $ShowCreateLink = 0;
        $PageTitle = "Create account";
        break;
    case "cp-login-page":
        $ShowLoginLink = 0;
        $ShowCreateLink = 1;
        break;
    case "cp-funds-listing-page":
        $ShowLoginLink = 1;
        $ShowCreateLink = 0;
        if($AdminDetails["homepage_title"]!="")
            $PageTitle = $AdminDetails["homepage_title"];
        else
            $PageTitle= "Funds";    
        break;
    case "cp-cart-page":
        $ShowLoginLink = 1;
        $ShowCreateLink = 0;
        $PageTitle = "My offering cart";
        break;
    case "cp-confirm-offering-page":
        $ShowLoginLink = 1;
        $ShowCreateLink = 0;
        $PageTitle = "Confirm my offering";
        break;
    case "cp-process-payment-page":
        $ShowLoginLink = 1;
        $ShowCreateLink = 0;
        $PageTitle = "Process payment";
        break;
    case "cp-receipt-page":
        $ShowLoginLink = 0;
        $ShowCreateLink = 0;
        $ShowPrintLink = 1;
        $PageTitle = $AdminDetails["church_name"];
        $PageTitle1 = $AdminDetails["church_address1"];
        $PageTitle1 .="<br />".$AdminDetails["church_address2"];      
        break;
}
if (isset($_SESSION["CP-UserToken"]) && trim($_SESSION["CP-UserToken"]) != "" && $post_slug != "cp-receipt-page") {
    $ShowLoginLink = 0;
    $ShowLogoutLink = 1;
    $ShowCreateLink = 0;
}
$UserName = "";
$StyleSheet = plugins_url('CustomPayment/assets/css/stylesheet.css');
?>
<div class="CPMainHeader" style="<?php echo $Padding_bottom; ?>">
    <div style="float:left;width:35%;">
        <h1 style="font-size: 20px;"><?php echo $PageTitle; ?></h1>
        <?php if($PageTitle1!=""){ 
            echo $PageTitle1;
        } ?>
    </div>
    <div style="float:right;padding-bottom: 10px;width:65%;">

        <?php if ($ShowLoginLink == 1): ?>    
            <div style="float:right; margin-left:1%;">| <a href="<?php echo $LoginUrl; ?>">Login</a></div>
        <?php endif; ?>
        <?php if ($ShowLogoutLink == 1): ?>    
            <div style="float:right; margin-left:1%;">| <a href="<?php echo $LogoutUrl; ?>">Logout</a></div>
        <?php endif; ?>   
        <?php if ($ShowPrintLink == 1) { ?>
            <a class="print-receipt" href="javascript:void(0);" onclick="window.print();">Print</a>
        <?php } ?>
        <div style="float:right;"><a href="http://www.ncsservices.org/" target="_blank">Powered by NCS Services</a></div>
        <div style="clear:both;"></div>
        <?php if (isset($_SESSION["CP-UserToken"]) && trim($_SESSION["CP-UserToken"]) != "") { ?>
            <div class="user-name" style="float:right;padding: 1% 0">
                <h4>Welcome <?php echo stripcslashes($_SESSION["CP-Name"]); ?></h4>
            </div>  

        <?php }
        ?>

    </div> 
</div>
<div style="clear:both;"></div>

<?php if ($ShowCreateLink == 1): ?>
    <div style="padding-bottom: 20px;">
        <a href="<?php echo $CreateMemberUrl; ?>">Create Account</a>
    </div>
<?php endif; ?>

<link rel="stylesheet" href="<?php echo $StyleSheet; ?>" />
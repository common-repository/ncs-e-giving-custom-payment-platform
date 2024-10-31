<?php
/**
 * Template Name: Member Login page
 *
 * @package Custom Payment
 * @Author Ankit Gupta
 * @Date 17/6/2014
 * Description: This template will be used to allow members to login for NCS services.
 */
session_start();

if (isset($_REQUEST["logout"]) && trim($_REQUEST["logout"]) == 1 && isset($_SESSION["CP-UserToken"])) {
    unset($_SESSION);
    session_destroy();
}

## check if user is already logged in than redirect user to the funds listing page..
$FundsListUrl = get_permalink(get_page_by_path("cp-funds-listing-page"));
if (isset($_SESSION["CP-UserToken"]) && trim($_SESSION["CP-UserToken"]) != "") {
    wp_redirect($FundsListUrl);
    exit;
}
get_header();
include_once("CommonFunctions.php");

use NCSSERVICES\eg_ws_call;
use NCSSERVICES\Entity;
use NCSSERVICES\Session;
use NCSSERVICES\Member;

$Error = array();
$FundsListUrl = get_permalink(get_page_by_path("cp-funds-listing-page"));
$eg_ws = new eg_ws_call();
$token = '';

## Add assets file from here...
wp_enqueue_script('script-validation-rules', plugins_url('CustomPayment/assets/js/jquery.validationEngine-en.js'), array(), '1.0.0', true);
wp_enqueue_script('script-validation', plugins_url('CustomPayment/assets/js/jquery.validationEngine.js'), array(), '1.0.0', true);
wp_enqueue_style("style-validation", plugins_url('CustomPayment/assets/css/validationEngine.css'));

if (isset($_POST["LoginBtn"])) {
    if (sanitize_text_field($_POST["username"]) == "") {
        $Error[] = "Username cannot be blank.";
    } elseif (sanitize_text_field($_POST["password"]) == "") {
        $Error[] = "Password cannot be blank.";
    } elseif (strlen(sanitize_text_field($_POST["password"])) < 8) {
        $Error[] = "Password should be atleast 8 characters long.";
    }

    ## Means no error is generated...
    if (empty($Error)) {
        ## call API if no Error is generated...

        $UserName = sanitize_text_field($_POST["username"]);
        $Password = sanitize_text_field($_POST["password"]);

        $sess = new \NCSSERVICES\Session($AdminDetails["admin_token"], $UserName, $Password); //MEMBER CREDENTIALS
        $EG_START_MEMBER_SESSION = $sess->MemberSession();
        $eg_ws->method = $EG_START_MEMBER_SESSION['method'];
        $out = $eg_ws->do_ws_call($EG_START_MEMBER_SESSION['url'], $sess->toJson());
        if ($out) {
            $response = json_decode($out);
            $token = $response->Token;

            $eg_ws->method = $EG_GET_MEMBER_DETAILS["method"];
            $url = $EG_GET_MEMBER_DETAILS["url"];
            $out1 = $eg_ws->do_ws_call($url, '', $token);
            if ($out1) {
                $response = json_decode($out1);
                $UserName = $response->FirstName . " " . $response->LastName;
            } else {
                $Error[] = $eg_ws->err;
            }

            $_SESSION["CP-UserToken"] = $token;
            $_SESSION["CP-UserID"] = $response->ID;
            $_SESSION["CP-OrgID"] = $response->OrgID;
            $_SESSION["CP-Email"] = $response->Email;
            $_SESSION["CP-ACHAllowed"] = $response->ACHAllowed;
            $_SESSION["CP-UserName"] = sanitize_text_field($_POST["username"]);
            $_SESSION["CP-UserPassword"] = sanitize_text_field($_POST["password"]);
            $_SESSION["CP-Name"] = $UserName;
            $_SESSION['LAST_ACTIVITY'] = time();
            wp_redirect($FundsListUrl);
            exit;
        } else {
            $Error[] = $eg_ws->err;
        }
    }
}
?>
<style>
    ::-webkit-input-placeholder { color: #000; opacity:1;}
    :-moz-placeholder { color: #000; opacity:1;}
    ::-moz-placeholder { color: #000; opacity:1;}
    :-ms-input-placeholder { color: #000; opacity:1;}
    .field{
        margin-bottom: 10px;
    }
    .red{
        color:red;
    }
    .green{
        color:green;
    }
</style>

<div id="main-content" class="main-content">
    <div id="primary" class="content-area">
        <!--        <div id="content" class="site-content" role="main">-->
            <?php if (!empty($Error)) { ?>
            <div class="ErrorMessages">
                <?php foreach ($Error as $key => $val) { ?>
                    <div class="red"><?php echo $val; ?></div>
            <?php } ?>
            </div>    
        <?php }
        ?>

<?php if (isset($_GET["AccntCreated"]) && trim($_GET["AccntCreated"]) == 1) { ?>
            <div class = "SuccsesMessages">
                <div class="green">Account created successfully. Please enter your username and password to login.</div>
            </div>    
        <?php } ?>
<?php include_once("cp-header.php"); ?> 


        <form id="LoginFrm" name="LoginFrm" method="post">  
            <div class="field">
                <input type="text" class="validate[required]" id="username" name="username" class="validate[required]" placeholder="User Name" />
            </div>
            <div class="field">
                <input type="password" class="validate[required,minSize[8],custom[usernameR]]" id="password" name="password" placeholder="Password" />
            </div>
            <div class="field">
                <input type="button" id="CancelBtn" name="CancelBtn" value="< Cancel" onclick="window.location.assign('<?php echo $FundsListUrl; ?>');" />
                <input type="Submit" id="LoginBtn" name="LoginBtn" value="Login >" />
            </div>
        </form>    
        <!--        </div> #content -->
    </div><!-- #primary -->
</div><!-- #main-content -->
<script>
    jQuery.noConflict();
    jQuery(document).ready(function() {
        jQuery("#LoginFrm").validationEngine();
    });
</script>
<?php
//get_sidebar();
get_footer();
?>

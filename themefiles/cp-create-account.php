<?php
/**
 * Template Name: Create Account page
 *
 * @package Custom Payment
 * @Author Ankit Gupta
 * @Date 16/6/2014
 * Description: This template will be used to display the member addition form for the users.
 */
session_start();

$FundsListUrl = get_permalink(get_page_by_path("cp-funds-listing-page"));
## check if user is already logged in than redirect user to the funds listing page..
if (isset($_SESSION["CP-UserToken"]) && sanitize_text_field($_SESSION["CP-UserToken"]) != "") {
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
$Success = array();
$regex = '/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/';
$UNRegEx = '/^[A-Za-z0-9_.-@]*$/';
$ZIPRegEx = '/(^\d{5}$)|(^\d{5}-\d{4}$)/';
$eg_ws = new eg_ws_call();
$token = '';

## validate form from here...
if (isset($_POST["CreateAcntBtn"])) {
    if (sanitize_text_field($_POST["fname"]) == "") {
        $Error[] = "First name cannot be blank.";
    } elseif (sanitize_text_field($_POST["lname"]) == "") {
        $Error[] = "Last name cannot be blank.";
    } elseif (sanitize_text_field($_POST["address1"]) == "") {
        $Error[] = "Address field cannot be blank.";
    } /* elseif (sanitize_text_field($_POST["address2"]) == "") {
      $Error[] = "Address field2 cannot be blank.";
      } */ elseif (sanitize_text_field($_POST["country"]) == "") {
        $Error[] = "country cannot be blank.";
    } elseif (sanitize_text_field($_POST["city"]) == "") {
        $Error[] = "City cannot be blank.";
    } elseif (sanitize_text_field($_POST["state"]) == "") {
        $Error[] = "State cannot be blank.";
    } elseif (sanitize_text_field($_POST["zip"]) == "") {
        $Error[] = "Zip cannot be blank.";
    } elseif (!preg_match($ZIPRegEx, sanitize_text_field($_POST["zip"]))) {
        $Error[] = "Please enter proper zip code.";
    } elseif (sanitize_text_field($_POST["username"]) == "") {
        $Error[] = "Username cannot be blank.";
    } elseif (!preg_match($UNRegEx, sanitize_text_field($_POST["username"]))) {
        $Error[] = "Alphabets, numbers,'_' ,'-','.' are only allowed in username.";
    } elseif (sanitize_text_field($_POST["password"]) == "") {
        $Error[] = "Password cannot be blank.";
    } elseif (strlen(sanitize_text_field($_POST["password"])) < 8) {
        $Error[] = "Password should be atleast 8 characters long.";
    } elseif (!preg_match($UNRegEx, sanitize_text_field($_POST["password"]))) {
        $Error[] = "Alphabets, numbers,'_' ,'-','.' are only allowed in password.";
    } elseif (sanitize_text_field($_POST["password"]) != sanitize_text_field($_POST["password"])) {
        $Error[] = "Password not confirmed.";
    } elseif (sanitize_email(sanitize_text_field($_POST["email"])) == "") {
        $Error[] = "Email cannot be blank.";
    } elseif (!preg_match($regex, sanitize_text_field($_POST["email"]))) {
        $Error[] = "Please enter proper email address.";
    } elseif (sanitize_text_field($_POST["phone"]) == "") {
        $Error[] = "Phone number cannot be blank.";
    }
    ## Means no error is generated...
    if (empty($Error)) {
        ## call API if no Error is generated...
        $token = CPGetMemberSession();

        ##Once token is generated add member from here...
        $eg_ws->method = $EG_MEMBER_ADD['method'];
        $Address = sanitize_text_field($_POST["address1"]);
        if (sanitize_text_field($_POST["address2"]) != "") {
            $Address .=" " . sanitize_text_field($_POST["address2"]);
        }

        $mem = new Member(sanitize_text_field($_POST["username"]), sanitize_text_field($_POST["password"]), sanitize_text_field($_POST["fname"]), '', sanitize_text_field($_POST["lname"]), $Address, sanitize_text_field($_POST["city"]), sanitize_text_field($_POST["state"]), sanitize_text_field($_POST["zip"]), sanitize_text_field($_POST["country"]), sanitize_text_field($_POST["phone"]), sanitize_email(sanitize_text_field($_POST["email"])), '');

        if ($eg_ws->do_ws_call($EG_MEMBER_ADD['url'], $mem->toJson(), $token)) {
            ## redirect to login if member is created successfully...
            $LoginUrl = get_permalink(get_page_by_path("cp-login-page"));
            $FinalLoginUrl = add_query_arg('AccntCreated', '1', $LoginUrl);
            wp_redirect($FinalLoginUrl);
            exit();
        } else {
            $Error[] = $eg_ws->err;
        }
    }
    ## if no errors are generated than unset post so that previous values are not popped up in the form...
    if (empty($Error)) {
        unset($_POST);
    }
}

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

## Add assets file from here...
wp_enqueue_script('script-validation-rules', plugins_url('CustomPayment/assets/js/jquery.validationEngine-en.js'), array(), '1.0.0', true);
wp_enqueue_script('script-validation', plugins_url('CustomPayment/assets/js/jquery.validationEngine.js'), array(), '1.0.0', true);

wp_enqueue_style("style-validation", plugins_url('CustomPayment/assets/css/validationEngine.css'));
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
    .width40p{
        width:40%;
    }
</style>    
<div id="main-content" class="main-content">
    <div id="primary" class="content-area">
        <!--        <div id="content" class="site-content" role="main" style="border:solid 1px red;">-->
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

        <!--        <div style="margin-bottom:5px;text-align:right;"><b>Note:</b> All fields are mandatory.</div>-->
        <form id="CreateAccountFrm" name="CreateAccountFrm" method="post">  
            <div style="width:100%;"> 
                <div style="float:left;width:50%; "> 
                    <div class="field">
                        <input type="text" class="validate[required]" id="fname" name="fname" placeholder="First Name" value="<?php echo $_POST["fname"]; ?>" />
                    </div>
                    <div class="field">
                        <input type="text" class="validate[required]" id="lname" name="lname" placeholder="Last Name" value="<?php echo $_POST["lname"]; ?>" />
                    </div>
                    <div class="field">
                        <input type="text" class="validate[required]" id="address1" name="address1" placeholder="Address" value="<?php echo $_POST["address1"]; ?>" />
                    </div>
                    <div class="field">
                        <input type="text" class="" id="address2" name="address2" placeholder="Address" value="<?php echo $_POST["address2"]; ?>" />
                    </div>
                    <div class="field">
                        <input type="text" class="validate[required]" id="city" name="city" placeholder="City" value="<?php echo $_POST["city"]; ?>" />
                    </div>
                    <div class="field">
                        <select name="state" id="state" class="width40p validate[required]" >
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

                    </div>
                    <div class="field">
                        <input type="hidden" class="validate[required] myCustomRule" id="country" name="country" placeholder="Country" value="US" />
                        <input type="text" class="validate[required,custom[CustomZip]]" id="zip" name="zip" placeholder="Zip" value="<?php echo $_POST["zip"]; ?>" />
                    </div>
                </div>
                <div style="float:left;width:50%;"> 
                    <div class="field">
                        <input type="text" class="validate[required,custom[usernameR]]" id="username" name="username"  placeholder="User Name" value="<?php echo $_POST["username"]; ?>" />
                    </div>
                    <div class="field">
                        <input type="password" class="validate[required,minSize[8],custom[usernameR]]" id="password" name="password" placeholder="Password" value="<?php echo $_POST["password"]; ?>" />
                    </div>
                    <div class="field">
                        <input type="password" class="validate[required,equals[password],custom[usernameR]] " id="cpassword" name="cpassword" placeholder="Password" value="<?php echo $_POST["cpassword"]; ?>" />
                    </div>
                    <div class="field">
                        <input type="text" class="validate[required,custom[email]]" id="email" name="email" placeholder="Email Address" value="<?php echo $_POST["email"]; ?>" />
                    </div>
                    <div class="field">
                        <input type="text" class="validate[required,minSize[10],custom[phone]]" id="phone" name="phone" placeholder="Phone Number" value="<?php echo $_POST["phone"]; ?>" />
                    </div>
                    <div class="field">
                        <input type="button" id="CancelBtn" name="CancelBtn" value="< Cancel" onclick="window.location.assign('<?php echo $FundsListUrl; ?>');" />
                        <input type="Submit" id="CreateAcntBtn" name="CreateAcntBtn" value="Create Account >" />
                    </div>
                </div>   
            </div>    
        </form>    
        <!--        </div> #content -->
    </div><!-- #primary -->
</div><!-- #main-content -->
<?php
//get_sidebar();
get_footer();
?>
<script>
    jQuery.noConflict();
    jQuery(document).ready(function() {
        jQuery("#CreateAccountFrm").validationEngine();
    });
</script>
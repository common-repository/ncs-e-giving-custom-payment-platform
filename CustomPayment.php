<?php
/**
 * Plugin Name: E-Giving
 * Plugin URI: 
 * Description: It will be used to create funds and set 5 different prices and make payment using cheque and credit cards.
 * Version: 1.01
 * Author: Ankit Gupta
 * Author URI: 
 * License: GPL2
 */
session_start();
// Make sure we don't expose any info if called directly
if (!function_exists('add_action')) {
    echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
    exit;
}

include_once('Pages.inc');
include_once('Class.AdminCustomSettings.php');

## call function when plugin is activated...
register_activation_hook(__FILE__, 'custom_payment_plugin_activate');
add_action("template_redirect", 'CreatePageTemplates');

## function called when the plugin is activated...
$custom_settings = new CustomPaymentSettings();
$custom_settings->CreateCustomSettings();

function custom_payment_plugin_activate() {

    /* register_sidebar(array(
      'name' => __('Main1 Sidebar', 'wpb'),
      'id' => 'sidebar-1111',
      'description' => __('The main sidebar appears on the right on each page except the front page template', 'wpb'),
      'before_widget' => '<aside id="%1$s" class="widget %2$s">',
      'after_widget' => '</aside>',
      'before_title' => '<h3 class="widget-title">',
      'after_title' => '</h3>',
      )); */

    if (is_admin()) {
        ## create new pages from here...
        $PagesList = CreateNewPages();
    }
}

## function to load the page templates dynamically...

function CreatePageTemplates() {
    global $wp;
    define('CPTEMPLATEPATH', dirname(__FILE__) . "/themefiles/");
    $plugindir = dirname(__FILE__);

    if ($wp->query_vars["pagename"] == 'cp-create-account-page') {
        $templatefilename = 'cp-create-account.php';
        $return_template = CPTEMPLATEPATH . $templatefilename;
        do_theme_redirect($return_template);
    }
    if ($wp->query_vars["pagename"] == 'cp-login-page') {
        $templatefilename = 'cp-login.php';
        $return_template = CPTEMPLATEPATH . $templatefilename;
        do_theme_redirect($return_template);
    }
    if ($wp->query_vars["pagename"] == 'cp-funds-listing-page') {
        $templatefilename = 'cp-funds-listing-page.php';
        $return_template = CPTEMPLATEPATH . $templatefilename;
        do_theme_redirect($return_template);
    }
    if ($wp->query_vars["pagename"] == 'cp-cart-page') {
        $templatefilename = 'cp-cart-page.php';
        $return_template = CPTEMPLATEPATH . $templatefilename;
        do_theme_redirect($return_template);
    }
    if ($wp->query_vars["pagename"] == 'cp-confirm-offering-page') {
        $templatefilename = 'cp-confirm-offering-page.php';
        $return_template = CPTEMPLATEPATH . $templatefilename;
        do_theme_redirect($return_template);
    }
    if ($wp->query_vars["pagename"] == 'cp-process-payment-page') {
        $templatefilename = 'cp-process-payment-page.php';
        $return_template = CPTEMPLATEPATH . $templatefilename;
        do_theme_redirect($return_template);
    }
    if ($wp->query_vars["pagename"] == 'cp-receipt-page') {
        $templatefilename = 'cp-payment-receipt-page.php';
        $return_template = CPTEMPLATEPATH . $templatefilename;
        do_theme_redirect($return_template);
    }
}

## function to do theme redirection and load the themes...

function do_theme_redirect($url) {
    global $post, $wp_query;
    if (have_posts()) {
        include($url);
        die();
    } else {
        $wp_query->is_404 = true;
    }
}

## call function when plugin is deactivated...
register_deactivation_hook(__FILE__, 'custom_payment_plugin_deactivate');

function custom_payment_plugin_deactivate() {
    //   delete_option('custom_payment_name');
    ## deltete new pages from here...
    DeletePages();
}

## function to delete the selected funds...
add_action('wp_ajax_remove_funds_ajax_function', 'remove_funds_ajax_function');
add_action('wp_ajax_nopriv_remove_funds_ajax_function', 'remove_funds_ajax_function');

function remove_funds_ajax_function() {
    $SelectedFunds = unserialize($_SESSION["CP-SelectedFunds"]);
    unset($SelectedFunds[(int) trim($_POST["fund"])]);
    $_SESSION["CP-SelectedFunds"] = serialize($SelectedFunds);
    wp_die(); // ajax call must die to avoid trailing 0 in your response
}

## widget code starts from here... Created aby Ankit Gupta on 24/7/2014..... 

add_action("plugins_loaded", "custom_payment_widget_init");

/**
 * Function to initialize subscriber's issue widget & control panel
 *
 * @author    Created By : Ankit Gupta On 24 July 2014
 * @author    Last Modified By : Ankit Gupta On 24 July 2014
 * @version 1.4
 * @name custom_payment_widget_init
 * @access public
 */
function custom_payment_widget_init() {
    register_sidebar_widget(__('E-Giving'), 'widget_custom_payment');
    // register_widget_control(__('Custom Payment'), 'widget_custom_payment_control');
}

/**
 * Function to initialize subscriber's issue widget & control panel
 *
 * @author    Created By : Ankit Gupta On 24 July 2014
 * @author    Last Modified By : Ankit Gupta On 24 July 2014
 * @version 1.4
 * @name widget_custom_payment
 * @access public
 */
function widget_custom_payment($args) {

    extract($args);
    echo $before_widget;
    echo $before_title;
    echo "E-Giving Title1";
    echo $after_title;
    widget_custom_payment_control();
    echo $after_widget;
}

/**
 * Function to initialize subscriber's issue widget & control panel
 *
 * @author    Created By : Ankit Gupta On 24 July 2014
 * @author    Last Modified By : Ankit Gupta On 24 July 2014
 * @version 1.4
 * @name widget_custom_payment_control
 * @access public
 */
function widget_custom_payment_control() {

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
    ?>

    <form id = "CreateAccountFrm" name = "CreateAccountFrm" method = "post">
        <div style = "width:100%;">
            <div style = "float:left;width:50%; ">
                <div class = "field">
                    <input type = "text" class = "validate[required]" id = "fname" name = "fname" placeholder = "First Name" value = "<?php echo $_POST["fname"]; ?>" />
                </div>
                <div class = "field">
                    <input type = "text" class = "validate[required]" id = "lname" name = "lname" placeholder = "Last Name" value = "<?php echo $_POST["lname"]; ?>" />
                </div>
                <div class = "field">
                    <input type = "text" class = "validate[required]" id = "address1" name = "address1" placeholder = "Address" value = "<?php echo $_POST["address1"]; ?>" />
                </div>
                <div class = "field">
                    <input type = "text" class = "" id = "address2" name = "address2" placeholder = "Address" value = "<?php echo $_POST["address2"]; ?>" />
                </div>
                <div class = "field">
                    <input type = "text" class = "validate[required]" id = "city" name = "city" placeholder = "City" value = "<?php echo $_POST["city"]; ?>" />
                </div>
                <div class = "field">
                    <select name = "state" id = "state" class = "width40p validate[required]" >
                        <option value = "">State </option>
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
    <?php
    /* $options = get_option("widget_subscriber_issue_config");
      $error = "";

      if(!is_array($options)) {
      $options = array('issueCount'=>5,'loginRequire'=>1,'articleCount'=>2,'title'=>'','desc'=>'');
      }


      if($_POST['subscriberIssue-submit']) {
      if($_POST['chkloginReqSubscriberIssue'] == "on")
      $options['loginRequire'] = 1;
      else
      $options['loginRequire'] = 0;

      if(trim($_POST['txtSubscriberIssueTitle']) == "") {
      $error = "Please enter title for the widget.";
      $options['title'] = "";
      } else {
      $options['title'] = urlencode(trim($_POST['txtSubscriberIssueTitle']));
      $options['desc'] = urlencode(trim($_POST['txtSubscriberIssueDesc']));
      }

      if(is_numeric($_POST['txtSubscriberIssueCount'])) {
      $_POST['txtSubscriberIssueCount'] = (int) trim($_POST['txtSubscriberIssueCount']);

      if($_POST['txtSubscriberIssueCount'] > 0)
      $options['issueCount'] = $_POST['txtSubscriberIssueCount'];
      else {
      if($error != "")
      $error .= "<br/>";

      $error .= "Number of issues should be positive and greater than 0.";
      $options['issueCount'] = "";
      }
      } else {
      if($error != "")
      $error .= "<br/>";

      $error .= "Numeric value required for number of issues.";
      $options['issueCount'] = "";
      }

      if(is_numeric($_POST['txtSubscriberArticleCount'])) {
      $_POST['txtSubscriberArticleCount'] = (int) trim($_POST['txtSubscriberArticleCount']);

      if($_POST['txtSubscriberArticleCount'] >= 0)
      $options['articleCount'] = (int) $_POST['txtSubscriberArticleCount'];
      else {
      if($error != "")
      $error .= "<br/>";

      $error .= "Number of articles should be positive and greater than or equal to 0.";
      $options['articleCount'] = "";
      }
      } else {
      if($error != "")
      $error .= "<br/>";

      $error .= "Numeric value required for number of articles.";
      $options['articleCount'] = "";
      }

      if($error == "") {
      update_option("widget_subscriber_issue_config", $options);
      }
      }
      ?>
      <p>
      <span>Fields marked as <span style="color:red;">*</span> are mandatory.</span><br/><br/>
      <?php
      if ($error != "") {
      ?>
      <span style="color:red;"><b><?php echo $error; ?></b></span><br/>
      <?php
      }

      $loginReq = '';
      if ($options['loginRequire'] == 1)
      $loginReq = 'checked="checked"';
      ?>

      <label>Title<span style="color:red;">*</span></label><br/>
      <input type="text" id="txtSubscriberIssueTitle" name="txtSubscriberIssueTitle" value="<?php echo stripslashes(htmlentities(urldecode($options['title']), ENT_QUOTES, "UTF-8")); ?>" size="30" autocomplete="off" /><br />

      <label>Description</label><br/>
      <textarea cols="30" rows="2" id="txtSubscriberIssueDesc" name="txtSubscriberIssueDesc"><?php echo stripslashes(htmlentities(urldecode($options['desc']), ENT_QUOTES, "UTF-8")); ?></textarea><br />

      <input type="checkbox" id="chkloginReqSubscriberIssue" name="chkloginReqSubscriberIssue" <?php echo $loginReq; ?> />
      <label>Login require to show issues listing</label> <br/>
      <label>Number of issues to show<span style="color:red;">*</span></label><br/>
      <input type="text" id="txtSubscriberIssueCount" name="txtSubscriberIssueCount" value="<?php echo $options['issueCount']; ?>" size="3" autocomplete="off" /> <br/>
      <label>Number of articles to show<span style="color:red;">*</span></label><br/>
      <input type="text" id="txtSubscriberArticleCount" name="txtSubscriberArticleCount" value="<?php echo $options['articleCount']; ?>" size="3" autocomplete="off" />
      <input type="hidden" id="subscriberIssue-submit" name="subscriberIssue-submit" value="1" />
      </p>
      <?php
     */
}

function wpb_widgets_init() {

    register_sidebar(array(
        'name' => __('Main Sidebar 1234', 'wpb'),
        'id' => 'sidebar-1234',
        'description' => __('The main sidebar appears on the right on each page except the front page template', 'wpb'),
        'before_widget' => '<aside id="%1$s" class="widget %2$s">',
        'after_widget' => '</aside>',
        'before_title' => '<h3 class="widget-title">',
        'after_title' => '</h3>',
    ));

    /* register_sidebar( array(
      'name' =>__( 'Front page sidebar', 'wpb'),
      'id' => 'sidebar-2',
      'description' => __( 'Appears on the static front page template', 'wpb' ),
      'before_widget' => '<aside id="%1$s" class="widget %2$s">',
      'after_widget' => '</aside>',
      'before_title' => '<h3 class="widget-title">',
      'after_title' => '</h3>',
      ) ); */
}

add_action('widgets_init', 'wpb_widgets_init');




#### shortcodes.....

function widget($atts) {
    
    
     extract( shortcode_atts( 
        array( 
            'name'  => ''
        ), 
        $atts 
    ));
    $args = array(
        'before_widget' => '<div class="box widget">',
        'after_widget'  => '</div>',
        'before_title'  => '<div class="widget-title">',
        'after_title'   => '</div>',
    );
     //$args = 'before_widget=<div class="ra-post-widget">&after_widget=</div>&before_title=<h3>&after_title=</h3>';
    echo "NCS PLUGIN";
    //echo $after_title;
    widget_custom_payment_control();
    ob_start();
    the_widget( $name, $content, $args ); 
    $output = ob_get_clean();

    return $output; 
    
   /* global $wp_widget_factory;
    
    extract(shortcode_atts(array(
        'widget_name' => FALSE
    ), $atts));
    
    $widget_name = "widget_custom_payment";
    $widget_name = wp_specialchars($widget_name);
    
    if (!is_a($wp_widget_factory->widgets[$widget_name], 'WP_Widget')):
        $wp_class = 'WP_Widget_'.ucwords(strtolower($class));
        
        if (!is_a($wp_widget_factory->widgets[$wp_class], 'WP_Widget')):
            return '<p>'.sprintf(__("%s: Widget class not found. Make sure this widget exists and the class name is correct"),'<strong>'.$class.'</strong>').'</p>';
        else:
            $class = $wp_class;
        endif;
    endif;
    
    ob_start();
    the_widget($widget_name, $instance, array('widget_id'=>'arbitrary-instance-'.$id,
        'before_widget' => '',
        'after_widget' => '',
        'before_title' => '',
        'after_title' => ''
    ));
    $output = ob_get_contents();
    ob_end_clean();
    return $output;*/
    
}
add_shortcode('widget','widget'); 

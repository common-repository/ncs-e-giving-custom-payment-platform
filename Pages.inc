<?php

function CreateNewPages() {

    $PagesList = array();
    ##Funds Listing
    $funds_page = array(
        'post_title' => 'Funds Listing',
        'post_content' => 'This is the funds listing page.',
        'post_status' => 'publish',
        'post_type' => 'page',
        'post_author' => 2,
        'post_date' => '2012-08-20 15:10:30',
        'post_name' => 'CP-funds-listing-page'
    );
    $funds_id = wp_insert_post($funds_page);

    ##My Offering Cart
    $cart_page = array(
        'post_title' => 'My Offering Cart',
        'post_content' => 'This is the my offering cart page.',
        'post_status' => 'publish',
        'post_type' => 'page',
        'post_author' => 2,
        'post_date' => '2012-08-20 15:10:30',
        'post_name' => 'CP-cart-page'
    );
    $cart_id = wp_insert_post($cart_page);

    ## Confirm My Offering
    $ConfirmOffering_page = array(
        'post_title' => 'Confirm My Offering',
        'post_content' => 'This is the confirm my offering page.',
        'post_status' => 'publish',
        'post_type' => 'page',
        'post_author' => 2,
        'post_date' => '2012-08-20 15:10:30',
        'post_name' => 'CP-confirm-offering-page'
    );
    $ConfirmOffering_id = wp_insert_post($ConfirmOffering_page);

    ## process payment
    $ProcessPayment_page = array(
        'post_title' => 'Process Payment',
        'post_content' => 'This is the process payment page.',
        'post_status' => 'publish',
        'post_type' => 'page',
        'post_author' => 2,
        'post_date' => '2012-08-20 15:10:30',
        'post_name' => 'CP-process-payment-page'
    );
    $ProcessPayment_id = wp_insert_post($ProcessPayment_page);

    ## Receipt
    $Receipt_page = array(
        'post_title' => 'Receipt',
        'post_content' => 'This is the receipt page.',
        'post_status' => 'publish',
        'post_type' => 'page',
        'post_author' => 2,
        'post_date' => '2012-08-20 15:10:30',
        'post_name' => 'CP-receipt-page'
    );
    $Receipt_id = wp_insert_post($Receipt_page);

    ## Create account
    $CreateAccount_page = array(
        'post_title' => 'Create Account',
        'post_content' => 'This is the process payment page.',
        'post_status' => 'publish',
        'post_type' => 'page',
        'post_author' => 2,
        'post_date' => '2012-08-20 15:10:30',
        'post_name' => 'CP-create-account-page'
    );
    $CreateAccount_id = wp_insert_post($CreateAccount_page);
    $PagesList[] = array("ID" => $CreateAccount_id, "SLUG" => "CP-create-account-page");

    ## login
    $Login_page = array(
        'post_title' => 'Login',
        'post_content' => 'This is the login page.',
        'post_status' => 'publish',
        'post_type' => 'page',
        'post_author' => 2,
        'post_date' => '2012-08-20 15:10:30',
        'post_name' => 'CP-login-page'
    );
    $Login_id = wp_insert_post($Login_page);
    $PagesList[] = array("ID" => $Login_id, "SLUG" => "CP-login-page");

    return $PagesList;
}

## function to delete all the pages created...

function DeletePages() {
    $page = get_page_by_path("cp-login-page");
    wp_delete_post($page->ID, true);

    $page = get_page_by_path("cp-create-account-page");
    wp_delete_post($page->ID, true);

    $page = get_page_by_path("cp-funds-listing-page");
    wp_delete_post($page->ID, true);

    $page = get_page_by_path("cp-cart-page");
    wp_delete_post($page->ID, true);

    $page = get_page_by_path("cp-confirm-offering-page");
    wp_delete_post($page->ID, true);

    $page = get_page_by_path("cp-process-payment-page");
    wp_delete_post($page->ID, true);

    $page = get_page_by_path("cp-receipt-page");
    wp_delete_post($page->ID, true);
}

?>
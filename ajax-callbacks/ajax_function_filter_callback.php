<?php 

namespace MainStart\ProductViewer\KatalogFilters\Filters;

    check_ajax_referer('ps_nonce', 'nonce');

    $marka         = isset($_POST['marka']) ? sanitize_text_field($_POST['marka']) : '';
    $model         = isset($_POST['model']) ? sanitize_text_field($_POST['model']) : '';
    $priceMin      = isset($_POST['priceMin']) ? sanitize_text_field($_POST['priceMin']) : '';
    $priceMax      = isset($_POST['priceMax']) ? sanitize_text_field($_POST['priceMax']) : '';
    $rocznikMin    = isset($_POST['rocznikMin']) ? sanitize_text_field($_POST['rocznikMin']) : '';
    $rocznikMax    = isset($_POST['rocznikMax']) ? sanitize_text_field($_POST['rocznikMax']) : '';
    $product_type  = isset($_POST['product_type']) ? sanitize_text_field($_POST['product_type']) : '';
    $product_color = isset($_POST['product_color']) ? sanitize_text_field($_POST['product_color']) : '';

    error_log('Received AJAX Data: ' . print_r($_POST, true));

    $markas  = AjaxFilters::get_models_by_marka($marka);
    $models  = AjaxFilters::models_query($marka, $model);
    $prices  = AjaxFilters::product_price_filter_query($marka, $model, $rocznikMin, $rocznikMax, $product_type, $product_color);
    $rocznik = AjaxFilters::product_rocznik_query($marka, $model, $priceMin, $priceMax, $product_type, $product_color);
    $types   = AjaxFilters::product_type_query($marka, $model, $priceMin, $priceMax, $rocznikMin, $rocznikMax, $product_color);
    $colors  = AjaxFilters::product_color_query($term_id, $priceMin, $priceMax, $rocznikMin, $rocznikMax, $product_type, $product_color);

    error_log('markas'. print_r($markas,true));
    error_log('models'. print_r($models,true));

    wp_send_json_success(compact('models', 'prices', 'rocznik', 'types', 'colors'));

    wp_die();
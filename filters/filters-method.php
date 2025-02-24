<?php

namespace MainStart\ProductViewer\KatalogFilters\Filters;

if ( ! defined( 'WPINC' ) ) {
	die;
}

class Filters extends Product_ShortCodes {


public static function marka() {
    $taxonomy = 'pa_marka'; 
    
    $terms = get_terms(array(
        'taxonomy'   => $taxonomy,
        'hide_empty' => true,
    ));
    
    $term_data = array(); 

    if (!empty($terms) && !is_wp_error($terms)) {
        foreach ($terms as $term) {
            $term_id = $term->term_id;
            $term_name = $term->name;
            $term_data[$term_name] = $term_id;
            
        }
    }
    return $term_data; 
}

public static function models() {
    $taxonomy = 'pa_model'; 
    
    $terms = get_terms(array(
        'taxonomy'   => $taxonomy,
        'hide_empty' => true,
    ));
    
    $term_data = array(); 

    if (!empty($terms) && !is_wp_error($terms)) {
        foreach ($terms as $term) {
            $term_id = $term->term_id;
            $term_name = $term->name;
            $term_data[$term_name] = $term_id;
        }
    }

    return $term_data; 
}


public static function product_price_filter() {
    global $wpdb;

    $results = $wpdb->get_col(
        "SELECT meta_value FROM {$wpdb->postmeta} WHERE meta_key = '_regular_price' AND meta_value != '' AND meta_value != 0"
    );

    if (!empty($results)) {
        $prices = array();

        foreach ($results as $price) {
            $clean_price = floatval(str_replace(' ', '', $price));
            if ($clean_price > 0) {
                $prices[] = $clean_price;
            }
        }

        if (!empty($prices)) {
            $min_price = floor(min($prices) / 1000) * 1000;

            $max_price = ceil(max($prices) / 1000) * 1000;

            return array(
                'min' => number_format($min_price, 0, ',', ' '),
                'max' => number_format($max_price, 0, ',', ' ')
            );
        }
    }

    return false;
}



public static function product_rocznik() {
    global $wpdb;

    $results = $wpdb->get_col("
        SELECT t.name 
        FROM {$wpdb->terms} t
        INNER JOIN {$wpdb->term_taxonomy} tt ON t.term_id = tt.term_id
        WHERE tt.taxonomy = 'pa_rocznik'
    ");

    if (!empty($results)) {
        $years = array_map('intval', $results);

        return array(
            'min' => min($years),
            'max' => max($years),
        );
    }

    return false;
}




public static function product_type() {

    $taxonomy = 'pa_typ_podwozia'; 
    
    $terms = get_terms(array(
        'taxonomy'   => $taxonomy,
        'hide_empty' => true,
    ));
    
    $term_data = array(); 

    if (!empty($terms) && !is_wp_error($terms)) {
        foreach ($terms as $term) {
            $term_id = $term->term_id;
            $term_name = $term->name;

            $term_data[$term_name] = $term_id;
        }
    }

    return $term_data; 
}


public static function product_color() {
    $taxonomy = 'pa_kolor';

    $terms = get_terms(array(
        'taxonomy'   => $taxonomy,
        'hide_empty' => true,
    ));

    $term_data = array();

    if (!empty($terms) && !is_wp_error($terms)) {
        foreach ($terms as $term) {
            $term_data[$term->name] = $term->term_id;
        }
    }

    return $term_data;
}


}


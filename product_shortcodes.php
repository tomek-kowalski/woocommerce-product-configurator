<?php

namespace MainStart\ProductViewer\KatalogFilters\Filters;
use ElementorPro\Modules\GlobalWidget\Documents\Widget;

if ( ! defined( 'WPINC' ) ) {
    die;
}

/*
 * Plugin Name:       Custom ShortoCodes
 * Description:       Custom ShortoCodes
 * Version:           1.00
 * Author:            Tomasz Kowalski
 * Author URI:        https://kowalski-consulting.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       copart_import
 * Date:              2025-02-11  
 */

 class Product_ShortCodes{

 
     public function __construct() {
         $this->set_constants();
         $this->change_elementor_cat();
         add_action('wp_enqueue_scripts', [$this, 'plugin_enqueue']);
         add_action('init',[$this, 'load_configurator']);
         add_action('wp_ajax_nopriv_auto_callback', [$this,'auto_callback' ]);
         add_action('wp_ajax_auto_callback', [$this,'auto_callback' ]);
         add_action('wp_ajax_filter_callback', [$this, 'filter_callback']);
         add_action('wp_ajax_nopriv_filter_callback', [$this, 'filter_callback']);
         
     }

    public function set_constants() {
        if (!defined('PS_PATH')) {  
            define('PS_PATH', plugin_dir_path(__FILE__));
        }
        if (!defined('PS_URL')) {
            define('PS_URL', plugin_dir_url(__FILE__));
        }
        if (!defined('PS_VERSION')) {
            define('PS_VERSION', '1.0.0');
        }
    }

    public function filter_callback() {
        check_ajax_referer('psCodesAjax', 'nonce');
        
        //error_log('Nonce check passed.');
    
        $marka         = isset($_POST['marka']) ? sanitize_text_field($_POST['marka']) : '';
        $model         = isset($_POST['model']) ? sanitize_text_field($_POST['model']) : '';
        $priceMin      = isset($_POST['priceMin']) ? sanitize_text_field($_POST['priceMin']) : '';
        $priceMax      = isset($_POST['priceMax']) ? sanitize_text_field($_POST['priceMax']) : '';
        $rocznikMin    = isset($_POST['rocznikMin']) ? sanitize_text_field($_POST['rocznikMin']) : '';
        $rocznikMax    = isset($_POST['rocznikMax']) ? sanitize_text_field($_POST['rocznikMax']) : '';
        $product_type  = isset($_POST['product_type']) ? sanitize_text_field($_POST['product_type']) : '';
        $product_color = isset($_POST['product_color']) ? sanitize_text_field($_POST['product_color']) : '';

        $response_data = [];
    
        if ($marka) {
            $models = AjaxFilters::get_models_by_marka($marka, $priceMin, $priceMax, $rocznikMin, $rocznikMax, $product_type, $product_color);
            if (!empty($models)) {
                $response_data = $models;
            }
        }
    
        if ($priceMin || $priceMax) {
            $prices = AjaxFilters::product_price_filter_query($marka, $model, $rocznikMin, $rocznikMax, $product_type, $product_color);
            if (!empty($prices)) {
                $response_data['prices'] = $prices;
            }
        }
    
        if ($rocznikMin || $rocznikMax) {
            $rocznik = AjaxFilters::product_rocznik_query($marka, $model, $priceMin, $priceMax, $product_type, $product_color);
            if (!empty($rocznik)) {
                $response_data['rocznik'] = $rocznik;
            }
        }
    
        if ($product_type) {
            $types = AjaxFilters::product_type_query($marka, $model, $priceMin, $priceMax, $rocznikMin, $rocznikMax, $product_color);
            if (!empty($types)) {
                $response_data['types'] = $types;
            }
        }
    
        if ($product_color) {
            $colors = AjaxFilters::product_color_query($marka, $model, $priceMin, $priceMax, $rocznikMin, $rocznikMax, $product_type);
            if (!empty($colors)) {
                $response_data['colors'] = $colors;
            }
        }
    
        //error_log('Response Data: ' . print_r($response_data, true));
    
        wp_send_json_success($response_data);
        
        wp_die();
    }
    
    
    function auto_callback() {

        $marka         = isset($_POST['marka']) ? sanitize_text_field($_POST['marka']) : '';
        $model         = isset($_POST['model']) ? sanitize_text_field($_POST['model']) : '';
        $priceMin      = isset($_POST['priceMin']) ? sanitize_text_field($_POST['priceMin']) : '';
        $priceMax      = isset($_POST['priceMax']) ? sanitize_text_field($_POST['priceMax']) : '';
        $rocznikMin    = isset($_POST['rocznikMin']) ? sanitize_text_field($_POST['rocznikMin']) : '';
        $rocznikMax    = isset($_POST['rocznikMax']) ? sanitize_text_field($_POST['rocznikMax']) : ''; 
        $product_type  = isset($_POST['product_type']) ? sanitize_text_field($_POST['product_type']) : '';
        $product_color = isset($_POST['product_color']) ? sanitize_text_field($_POST['product_color']) : '';
        $paged =         isset($_POST['paged']) ? sanitize_text_field($_POST['paged']) : 1;

        error_log('marka: ' . $marka);
        error_log('kolor: ' . $product_color);
        error_log('model: ' . $model);
        error_log('nadwozie: ' . $product_type); 
        error_log('priceMin: ' . print_r($priceMin, true));
        error_log('priceMax: ' . print_r($priceMax, true));
        error_log('rocznik min: ' . print_r($rocznikMin, true));
        error_log('rocznik max: ' . print_r($rocznikMax, true));
        error_log('paged: ' . print_r( $paged, true));

        $args = array(
            'paged'          => max(1, $paged),
            'post_status'    => 'publish',
            'post_type'      => 'product',
            'posts_per_page' => 28,
            'orderby'        => 'rand',
        );
    
        if($marka && $marka === 'Marka') {

        }
        
        elseif ($marka) {
            $args['tax_query'][] = array(
                'taxonomy' => 'pa_marka',
                'field'    => 'slug',
                'terms'    => $marka,
            );
        }
        if($model && $model === 'Model') {

        }

        if ($model) {
            $args['tax_query'][] = array(
                'taxonomy' => 'pa_model',
                'field'    => 'slug',
                'terms'    => $model,
            );
        }

        if ($product_type && $product_type === 'Nadwozie') {

        }
    
        elseif ($product_type) {
            $args['tax_query'][] = array(
                'taxonomy' => 'pa_typ_podwozia',
                'field'    => 'slug',
                'terms'    => $product_type,
            );
        }
        if ($product_color && $product_color === 'Kolor') {

        }
    
        elseif ($product_color) {
            $args['tax_query'][] = array(
                'taxonomy' => 'pa_kolor',
                'field'    => 'slug',
                'terms'    => $product_color,
            );
        }

        if ($priceMin || $priceMax) {
            $meta_query = array('relation' => 'AND');

            $sanitized_price_min = str_replace([' ', ','], '', $priceMin);
            $sanitized_price_max = str_replace([' ', ','], '', $priceMax);
            
            if ($priceMin) {
                $meta_query[] = array(
                    'key'     => '_regular_price',
                    'value'   => $sanitized_price_min,
                    'compare' => '>=',
                    'type'    => 'NUMERIC',
                );
            }
        
            if ($priceMax) {
                $meta_query[] = array(
                    'key'     => '_regular_price',
                    'value'   => $sanitized_price_max,
                    'compare' => '<=',
                    'type'    => 'NUMERIC',
                );
            }
        
            $args['meta_query'] = $meta_query;
        }
        
    
        if ($rocznikMin || $rocznikMax) {
            $rocznikMin = is_numeric($rocznikMin) ? (int)$rocznikMin : '';
            $rocznikMax = is_numeric($rocznikMax) ? (int)$rocznikMax : '';
        
            if ($rocznikMin && $rocznikMax) {
                $years = range($rocznikMin, $rocznikMax);
                $years = array_map('strval', $years);
        
                $args['tax_query'][] = array(
                    'taxonomy' => 'pa_rocznik',
                    'field'    => 'slug',
                    'terms'    => $years,
                    'operator' => 'IN',
                );
            }
        }

        $output = '<div class="katalog-frame" id="target-ajax">';

        $query = new  \WP_Query($args);

        error_log('product query: ' . print_r($query,true));
            ob_start();
        if ($query->have_posts()) :

        
            while ($query->have_posts()) :
                $query->the_post();
        
                $post_id = get_the_ID();
                $permalink = get_permalink($post_id);
                $img_src = get_the_post_thumbnail_url($post_id, 'medium');
        
                $product_rocznik   = implode(', ', wp_get_post_terms($post_id, 'pa_rocznik', array('fields' => 'names')));
                $product_marka     = implode(', ', wp_get_post_terms($post_id, 'pa_marka', array('fields' => 'names')));
                $product_model     = implode(', ', wp_get_post_terms($post_id, 'pa_model', array('fields' => 'names')));
                $product_pojemnosc = implode(', ', wp_get_post_terms($post_id, 'pa_pojemnosc', array('fields' => 'names')));
                $product_przebieg  = implode(', ', wp_get_post_terms($post_id, 'pa_przebieg', array('fields' => 'names')));
                $product_paliwo    = implode(', ', wp_get_post_terms($post_id, 'pa_paliwo', array('fields' => 'names')));
                $product_nazwa     = implode(', ', wp_get_post_terms($post_id, 'pa_nazwa_auta', array('fields' => 'names')));
        
                $price = get_post_meta($post_id, '_regular_price', true);
                $price = (float) $price;
                $formatted_price = number_format($price, 2, ',', ' ');

                $output .= '<div class="auto-product-container">';
               
                $output .= '<div class="post-image">';
                if ($img_src) {
                    $alt_text = !empty($product_nazwa) ? esc_attr($product_nazwa) : 'Brak nazwy';
                    $output .= '<a href="' . esc_url($permalink) . '" class="post-content" rel="tag">';
                    $output .= '<img class="auto-img" src="' . esc_url($img_src) . '" alt="' . $alt_text . '"/>';
                    $output .= '</a>';
                } else {
                    $output .= '<p>Obraz nie został ustawiony</p>';
                }
                $output .= '</div>';
        
                $output .= '<div class="main-row">';
        
                $output .= '<div class="title-holder">';
                $output .= '<h2>' . esc_html($product_marka) . '</h2>';
                $output .= '<h1>' . esc_html($product_model) . '</h1>';
                if ($price) {
                    $output .= '<p>' . esc_html($formatted_price) . ' zł</p>';
                } else {
                    $output .= '<p>Cena nie została ustawiona</p>';
                }
                $output .= '</div>';
        
                $output .= '<div class="img-holder">';
                $output .= '<img src="' . PS_URL . 'assets/images/cart.png' . '" alt=""/>';
                $output .= '</div>';
                $output .= '</div>';
        
                $output .= '<div class="desc-line">';
                $output .= '<div class="feature-line"><img src="' . PS_URL . 'assets/images/rocznik.png' .'" alt="" class="img-rocznik"></div><div class="rocznik">' . esc_html($product_rocznik) . '</div>';
                $output .= '<div class="feature-line"><img src="' . PS_URL . 'assets/images/pojemnosc.png' .'" alt="" class="img-pojemnosc"></div><div class="pojemnosc">' . esc_html($product_pojemnosc) . '</div>';
                $output .= '<div class="feature-line"><img src="' . PS_URL . 'assets/images/przebieg.png' .'" alt="" class="img-przebieg"></div><div class="przebieg">' . esc_html($product_przebieg) . '</div>';
                $output .= '<div class="feature-line"><img src="' . PS_URL . 'assets/images/paliwo.png' .'" alt="" class="img-paliwo"></div><div class="paliwo">' . esc_html($product_paliwo) . '</div>';
                $output .= '</div>';
        
        
                $output .= '</div>';
            endwhile;
            $output .= '</div>';
        
            $pages = paginate_links([
                'current'  => max(1, $paged),
                'total'    => $query->max_num_pages,
                'format'   => '%#%',
                'prev_text' => __('Poprzedni'),
                'next_text' => __('Następne'),
                'type'      => 'array',
            ]);
        
            if (!empty($pages)) {
                $pagination = '<ul class="custom-pagination">';
            
                foreach ($pages as $index => $page) {
                    preg_match('/href=["\']?([^"\']+)/', $page, $matches);
                    $url = isset($matches[1]) ? $matches[1] : '';
            
                    $pageNumber = basename(parse_url($url, PHP_URL_PATH));
            
                    $isActive = strpos($page, 'current') !== false ? ' active' : '';
        
                    if ($index == 0 && strpos($page, 'current') !== false) {
                        $page = str_replace('<span ', '<span data-page="' . $pageNumber . '" class="page-link current" ', $page);
                        $page = str_replace('</span>', '</span>', $page);
                    }
            
                    $pagination  .= '<li class="page-item' . $isActive . '">';
                    $pagination  .= str_replace(
                        ['href=', 'page-numbers'], 
                        ['data-page=', 'page-link'], 
                        preg_replace('/<a /', '<span ', $page)
                    );
                    $pagination .= '</li>';
                }
            
                $pagination  .= '</ul>';
            }
            
 
            wp_reset_postdata();

        else : 

        $output = '<div>Przepraszamy nic nie znaleziono</div>';

        endif;
        
    $response = ob_get_clean();
    $response = [
        'auto_product'  => $output,
        'auto_pagination' => $pagination ?? '',
    ];

    wp_send_json_success($response);
    }

    public function change_elementor_cat() {
        require_once PS_PATH . 'callbacks/elementor-widget.php';
        require_once PS_PATH . 'callbacks/elementor-filters.php';
    }

    public function load_configurator() {
        require_once PS_PATH . 'filters/filters-method.php';
        require_once PS_PATH . 'filters/ajax_filter_method.php';
    }
 
    public function plugin_enqueue() {
        if (is_page('katalog')) {
            wp_register_script('filters-price', PS_URL . 'assets/js/filters-price.js', array('jquery'), PS_VERSION, true);
            wp_localize_script('filters-price', 'psCodesAjax', [
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('psCodesAjax')
            ]);
            wp_enqueue_script('filters-price');
            wp_enqueue_script('lazy-loading', PS_URL . 'assets/js/file-loading.js', array('jquery'), PS_VERSION, true);
        }
    }    
 }
 new Product_ShortCodes(); 
 

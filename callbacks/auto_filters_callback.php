<?php 

namespace MainStart\ProductViewer\KatalogFilters\Filters;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use \WP_Query;

if ( ! defined( 'WPINC' ) ) {
	die;
}
global $wpdb;
$filter = new Filters($wpdb,true);

class AutoKatalogFilters extends Widget_Base{


public function get_name(){
	return "Auto Katalog Filters";
}

public function get_title() {
 	return "Auto Katalog Filters";
}

public function get_icon() {
	return "eicon-skill-bar";
}

public function get_categories()
{
	return ['general'];
}

protected function _register_controls()
{

	$this->start_controls_section(
	'section_content',
	[
		'label'=>'Katalog Aut Filters',
	]);

	$this->end_controls_section();
	
}

protected function render() {

    $markas  = Filters::marka(); 
    $prices  = Filters::product_price_filter(); 
    $rocznik = Filters::product_rocznik();
    $kolors  = Filters::product_color();
    $types   = Filters::product_type();
    
    ob_start(); 

    $output = '<div class="panel">';

    $output .= '<div class="marka-column">';
    $output .= '<select id="marka" class="select-target">';
    $output .= '<option class="selected" selected>Marka</option>';

    foreach ($markas as $marka => $marka_id) {
        if (!empty($marka)) {
            $output .= '<option class="brand-select" value="' . esc_attr($marka) . '" data-id="' . esc_attr($marka_id) . '">' . esc_html($marka) . '</option>';
        }
    }
    $output .= '</select>';

    $output .= '<select id="model" class="select-target">';
    $output .= '<option class="selected" selected>Model</option>';
    $output .= '</select>';
    $output .= '</div>';


    $output .= '<div class="kolor-column">';
    $output .= '<select id="kolor" class="select-target">';
    $output .= '<option class="selected" selected>Kolor</option>';

    foreach ($kolors as $kolor => $kolor_id) {
        if (!empty($kolor)) {
            $output .= '<option class="kolor-select" value="' . esc_html($kolor) . '" data-id="' . esc_attr($kolor_id) . '">' . esc_html($kolor) . '</option>';
        }
    }
    $output .= '</select>';

    $output .= '<select id="type" class="select-target">';
    $output .= '<option class="selected" selected>Nadwozie</option>';

    foreach ($types as $type => $type_id) {
        if (!empty($type)) {
            $output .= '<option class="type-select" value="' . esc_html($type) . '" data-id="' . esc_attr($type_id) . '">' . esc_html($type) . '</option>';
        }
    }
    $output .= '</select>';
    $output .= '</div>';

    $output .= '<div class="sliders">';

    if (!empty($prices)) {
        $output .= '<div id="price-block" class="form-range-filter">';
        $output .= '<span class="price-title">Cena</span>';
        $output .= '<div class="slider-container">';
        $output .= '<div id="slider-range-price" class="strap-price"></div>';
        $output .= '<div class="part-price-1"></div>';
        $output .= '<div class="part-price-2"></div>';
        $output .= '</div>';
        $output .= '<div class="price-range">';
        $output .= '<div id="min-price" class="minimum_price" data-value="' . $prices['min'] . '"></div><div>&nbspzł&nbsp</div>';
        $output .= '<p>&nbsp — &nbsp</p>';
        $output .= '<div id="max-price" class="maximum_price" data-value="' . $prices['max'] . '"></div><div>&nbspzł&nbsp</div>';
        $output .= '</div>';
        $output .= '</div>';
    }

    if (!empty($rocznik)) {
        $output .= '<div id="rocznik-block" class="form-range-filter">';
        $output .= '<span class="rocznik-title">Rocznik</span>';
        $output .= '<div class="slider-container">';
        $output .= '<div id="slider-range-rocznik" class="strap-rocznik"></div>';
        $output .= '<div class="part-rocznik-1"></div>';
        $output .= '<div class="part-rocznik-2"></div>';
        $output .= '</div>';
        $output .= '<div class="rocznik-range">';
        $output .= '<div id="min-rocznik" class="minimum_rocznik" data-value="' . $rocznik['min'] . '"></div>';
        $output .= '<p>&nbsp — &nbsp</p>';
        $output .= '<div id="max-rocznik" class="maximum_rocznik" data-value="' . $rocznik['max'] . '"></div>';
        $output .= '</div>';
        $output .= '</div>';
    }

    $output .= '</div>';
    $output .= '</div>';


    ob_end_clean(); 
    echo $output;
}

}
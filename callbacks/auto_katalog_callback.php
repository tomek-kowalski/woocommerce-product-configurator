<?php 

namespace Main\ProductViewer\Callback;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use \WP_Query;

if ( ! defined( 'WPINC' ) ) {
	die;
}

class AutoKatalog extends Widget_Base{


public function get_name(){
	return "Auto Katalog";
}

public function get_title() {
 	return "Auto Katalog";
}

public function get_icon() {
	return "eicon-product-info";
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
		'label'=>'Katalog Aut Viewer',
	]);

	$this->end_controls_section();
	
}

protected function render() {
$output = '<div id="initial">'; 
$output .= '<div class="katalog-frame initial" id="target-ajax">'; 

$paged = isset($_POST['paged']) ? sanitize_text_field($_POST['paged']) : 1;

$args = array(
    'paged'          => max(1, $paged),
    'post_status'    => 'publish',
    'post_type'      => 'product',
    'posts_per_page' => 28,
    'orderby'        => 'rand',
);

$query = new WP_Query($args);
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
            $output .= '<p>' . esc_html( $formatted_price) . ' zł</p>';
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
    wp_reset_postdata();
    $output .= '</div>';
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
        $output .= '<div class="pagination-frame"><ul class="custom-pagination">';
    
        foreach ($pages as $index => $page) {
            preg_match('/href=["\']?([^"\']+)/', $page, $matches);
            $url = isset($matches[1]) ? $matches[1] : '';
    
            $pageNumber = basename(parse_url($url, PHP_URL_PATH));
    
            $isActive = strpos($page, 'current') !== false ? ' active' : '';

            if ($index == 0 && strpos($page, 'current') !== false) {
                $page = str_replace('<span ', '<span data-page="' . $pageNumber . '" class="page-link current" ', $page);
                $page = str_replace('</span>', '</span>', $page);
            }
    
            $output .= '<li class="page-item' . $isActive . '">';
            $output .= str_replace(
                ['href=', 'page-numbers'], 
                ['data-page=', 'page-link'], 
                preg_replace('/<a /', '<span ', $page)
            );
            $output .= '</li>';
        }
    
        $output .= '</ul></div>';
    }

endif;
ob_get_clean();
echo $output;

}

}
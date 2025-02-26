<?php

namespace MainStart\ProductViewer\KatalogFilters\Filters;

class AjaxFilters extends Product_ShortCodes {

    private static function get_term_id_by_name($term_name, $taxonomy) {
        global $wpdb;
        $query = "
            SELECT t.term_id
            FROM {$wpdb->terms} t
            INNER JOIN {$wpdb->term_taxonomy} tt ON tt.term_id = t.term_id
            WHERE t.name = %s AND tt.taxonomy = %s
            LIMIT 1
        ";
        return $wpdb->get_var($wpdb->prepare($query, $term_name, $taxonomy));
    }

    public static function get_models_by_marka($marka_name) {
        global $wpdb;
    

    
        $marka_term_id = self::get_term_id_by_name($marka_name, 'pa_marka');
        if (!$marka_term_id) {
            //error_log("No marka term found for: $marka_name");
            return [];
        }
    
        $model_query = "
            SELECT DISTINCT t.term_id, t.name
            FROM {$wpdb->term_relationships} tr
            INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
            INNER JOIN {$wpdb->terms} t ON tt.term_id = t.term_id
            WHERE tt.taxonomy = 'pa_model'
            AND tr.object_id IN (
                SELECT tr2.object_id
                FROM {$wpdb->term_relationships} tr2
                INNER JOIN {$wpdb->term_taxonomy} tt2 ON tr2.term_taxonomy_id = tt2.term_taxonomy_id
                WHERE tt2.term_id = %d AND tt2.taxonomy = 'pa_marka'
            )
        ";
        $models = $wpdb->get_results($wpdb->prepare($model_query, $marka_term_id));
    
        $models_data   = [];
        $min_price    = null;
        $max_price    = null;
        $price_html   = null;


        if ($models) {
            foreach ($models as $model) {
                $model_id = $model->term_id;
    
                $price_query = "
                    SELECT pm.meta_value
                    FROM {$wpdb->postmeta} pm
                    WHERE pm.meta_key = '_regular_price'
                    AND pm.post_id IN (
                        SELECT object_id
                        FROM {$wpdb->term_relationships} tr
                        WHERE tr.term_taxonomy_id IN (
                            SELECT term_taxonomy_id
                            FROM {$wpdb->term_taxonomy}
                            WHERE term_id = %d AND taxonomy = 'pa_model'
                        )
                    )
                ";
    
                $price_results = $wpdb->get_results($wpdb->prepare($price_query, $model_id));
    
                $prices = [];
                foreach ($price_results as $price) {
                    $price_value = floatval(str_replace([' ', ','], '', $price->meta_value));
    
                    if ($price_value > 0) {
                        $prices[] = $price_value;
    
                        if ($min_price === null || $price_value < $min_price) {
                            $min_price = $price_value;
                        }
                        if ($max_price === null || $price_value > $max_price) {
                            $max_price = $price_value;
                        }
                    }
                }

                //error_log('models data: ' . print_r($models_data,true));
    
                if (!empty($prices)) {

                    $models_data[$model->name] = [
                        'term_id' => $model->term_id,
                        'prices' => $prices,
                    ];
                }
            }
        }
        if ($min_price !== null) {
            $min_price = floor($min_price / 1000) * 1000;
        }
        
        if ($max_price !== null) {
            $max_price = ceil($max_price / 1000) * 1000;
        }

        if($min_price && $max_price) {
            $price_html .= '<span class="price-title">Cena</span>';
           
            $price_html .= '<div class="slider-container">';
            $price_html .= '<div id="slider-range-price" class="strap-price"></div>';
            $price_html .= '<div class="part-price-1"></div>';
            $price_html .= '<div class="part-price-2"></div>';
            $price_html .= '</div>';

            $price_html .= '<div class="price-range">';
            $price_html.= '<div id="min-price" class="minimum_price" data-value="' . $min_price . '"></div><div>&nbspzł&nbsp</div>';
            $price_html .= '<p>&nbsp — &nbsp</p>';
            $price_html .= '<div id="max-price" class="maximum_price" data-value="' . $max_price. '"></div><div>&nbspzł&nbsp</div>';
            $price_html .= '</div>';
        }

    
        $colors = [];
    
        $color_query = "
            SELECT DISTINCT t.term_id, t.name
            FROM {$wpdb->term_relationships} tr
            INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
            INNER JOIN {$wpdb->terms} t ON tt.term_id = t.term_id
            WHERE tt.taxonomy = 'pa_kolor'
            AND tr.object_id IN (
                SELECT tr2.object_id
                FROM {$wpdb->term_relationships} tr2
                INNER JOIN {$wpdb->term_taxonomy} tt2 ON tr2.term_taxonomy_id = tt2.term_taxonomy_id
                WHERE tt2.term_id = %d AND tt2.taxonomy = 'pa_marka'
            )
        ";

        $colors= $wpdb->get_results($wpdb->prepare($color_query, $marka_term_id));

        $types = [];
    
        $type_query = "
            SELECT DISTINCT t.term_id, t.name
            FROM {$wpdb->term_relationships} tr
            INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
            INNER JOIN {$wpdb->terms} t ON tt.term_id = t.term_id
            WHERE tt.taxonomy = 'pa_typ_podwozia'
            AND tr.object_id IN (
                SELECT tr2.object_id
                FROM {$wpdb->term_relationships} tr2
                INNER JOIN {$wpdb->term_taxonomy} tt2 ON tr2.term_taxonomy_id = tt2.term_taxonomy_id
                WHERE tt2.term_id = %d AND tt2.taxonomy = 'pa_marka'
            )
        ";

        $types= $wpdb->get_results($wpdb->prepare($type_query, $marka_term_id));

        $max_rocznik = null;
        $min_rocznik = null;
        $rocznik_html = null;

        $rocznik_query = "
            SELECT DISTINCT t.term_id, t.name
            FROM {$wpdb->term_relationships} tr
            INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
            INNER JOIN {$wpdb->terms} t ON tt.term_id = t.term_id
            WHERE tt.taxonomy = 'pa_rocznik'
            AND tr.object_id IN (
                SELECT tr2.object_id
                FROM {$wpdb->term_relationships} tr2
                INNER JOIN {$wpdb->term_taxonomy} tt2 ON tr2.term_taxonomy_id = tt2.term_taxonomy_id
                WHERE tt2.term_id = %d AND tt2.taxonomy = 'pa_marka'
        )
    ";
    
        $rocznik_terms = $wpdb->get_results($wpdb->prepare($rocznik_query, $marka_term_id));
    
        if ($rocznik_terms) {
            foreach ($rocznik_terms as $term) {
                $rocznik_value = intval($term->name);
            
                if ($min_rocznik === null || $rocznik_value < $min_rocznik) {
                    $min_rocznik = $rocznik_value;
                }
            
                if ($max_rocznik === null || $rocznik_value > $max_rocznik) {
                    $max_rocznik = $rocznik_value;
                }
            }
        }

        if($min_rocznik && $max_rocznik) {
            $rocznik_html .= '<span class="rocznik-title">Rocznik</span>';
                   
            $rocznik_html .= '<div class="slider-container">';
            $rocznik_html .= '<div id="slider-range-rocznik" class="strap-rocznik"></div>';
            $rocznik_html .= '<div class="part-rocznik-1"></div>';
            $rocznik_html .= '<div class="part-rocznik-2"></div>';
            $rocznik_html .= '</div>';

            $rocznik_html .= '<div class="rocznik-range">';
            $rocznik_html .= '<div id="min-rocznik" class="minimum_rocznik" data-value="' . $min_rocznik . '"></div>';
            $rocznik_html .= '<p>&nbsp — &nbsp</p>';
            $rocznik_html .= '<div id="max-rocznik" class="maximum_rocznik" data-value="' . $max_rocznik . '"></div>';
            $rocznik_html .= '</div>';
        }

        return [
            'models'      => $models_data,
            'colors'      => $colors,
            'types'       => $types,
            'price'       => $price_html, 
            'rocznik'     => $rocznik_html,
        ];
    }

    public static function models_query($marka_name, $model_name) {
        global $wpdb;
    
        $marka_term_id = self::get_term_id_by_name($marka_name, 'pa_marka');
        if (!$marka_term_id) {
            return [];
        }

        $model_term_id = self::get_term_id_by_name($model_name, 'pa_model');
        if (!$model_term_id) {
            return [];
        }
    

        $product_ids_query = "
            SELECT tr.object_id
            FROM {$wpdb->term_relationships} tr
            INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
            WHERE tt.taxonomy = 'pa_model' AND tt.term_id = %d
            AND tr.object_id IN (
                SELECT tr2.object_id
                FROM {$wpdb->term_relationships} tr2
                INNER JOIN {$wpdb->term_taxonomy} tt2 ON tr2.term_taxonomy_id = tt2.term_taxonomy_id
                WHERE tt2.term_id = %d AND tt2.taxonomy = 'pa_marka'
            )
        ";
    
        $product_ids = $wpdb->get_col($wpdb->prepare($product_ids_query, $model_term_id, $marka_term_id));
    
        if (empty($product_ids)) {
            return [];
        }
    
        $min_price = null;
        $max_price = null;
    
        $price_query = "
            SELECT pm.post_id, pm.meta_value as price
            FROM {$wpdb->postmeta} pm
            WHERE pm.meta_key = '_regular_price'
            AND pm.post_id IN (" . implode(",", $product_ids) . ")
        ";
    
        $price_results = $wpdb->get_results($price_query);
    
        foreach ($price_results as $price) {
            $price_value = floatval(str_replace([' ', ','], '', $price->price));
    
            if ($price_value > 0) {
                if ($min_price === null || $price_value < $min_price) {
                    $min_price = $price_value;
                }
                if ($max_price === null || $price_value > $max_price) {
                    $max_price = $price_value;
                }
    
                $models_data[] = [
                    'post_id' => $price->post_id,
                    'price' => $price_value
                ];
            }
        }

        if ($min_price !== null) {
            $min_price = floor($min_price / 1000) * 1000;
        }
        
        if ($max_price !== null) {
            $max_price = ceil($max_price / 1000) * 1000;
        }
    
        $price_html = '';
        if ($min_price && $max_price) {
            $price_html .= '<span class="price-title">Cena</span>';
            $price_html .= '<div class="slider-container">';
            $price_html .= '<div id="slider-range-price" class="strap-price"></div>';
            $price_html .= '<div class="part-price-1"></div>';
            $price_html .= '<div class="part-price-2"></div>';
            $price_html .= '</div>';
    
            $price_html .= '<div class="price-range">';
            $price_html .= '<div id="min-price" class="minimum_price" data-value="' . $min_price . '"></div><div>&nbspzł&nbsp</div>';
            $price_html .= '<p>&nbsp — &nbsp</p>';
            $price_html .= '<div id="max-price" class="maximum_price" data-value="' . $max_price . '"></div><div>&nbspzł&nbsp</div>';
            $price_html .= '</div>';
        }

        $max_rocznik = null;
        $min_rocznik = null;
        $rocznik_html = null;

        $rocznik_query = "
        SELECT DISTINCT t.term_id, t.name
        FROM {$wpdb->term_relationships} tr
        INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
        INNER JOIN {$wpdb->terms} t ON tt.term_id = t.term_id
        WHERE tt.taxonomy = 'pa_rocznik'
        AND tr.object_id IN (
            SELECT tr2.object_id
            FROM {$wpdb->term_relationships} tr2
            INNER JOIN {$wpdb->term_taxonomy} tt2 ON tr2.term_taxonomy_id = tt2.term_taxonomy_id
            INNER JOIN {$wpdb->posts} pm ON pm.ID = tr2.object_id  -- Join wp_posts here
            WHERE tt2.term_id = %d AND tt2.taxonomy = 'pa_marka'
            AND pm.ID IN (" . implode(",", array_map('intval', $product_ids)) . ")
        )
    ";
    
        $rocznik_terms = $wpdb->get_results($wpdb->prepare($rocznik_query, $marka_term_id));
    
        if ($rocznik_terms) {
            foreach ($rocznik_terms as $term) {
                $rocznik_value = intval($term->name);
            
                if ($min_rocznik === null || $rocznik_value < $min_rocznik) {
                    $min_rocznik = $rocznik_value;
                }
            
                if ($max_rocznik === null || $rocznik_value > $max_rocznik) {
                    $max_rocznik = $rocznik_value;
                }
            }
        }

        if($min_rocznik && $max_rocznik) {
            $rocznik_html .= '<span class="rocznik-title">Rocznik</span>';
                   
            $rocznik_html .= '<div class="slider-container">';
            $rocznik_html .= '<div id="slider-range-rocznik" class="strap-rocznik"></div>';
            $rocznik_html .= '<div class="part-rocznik-1"></div>';
            $rocznik_html .= '<div class="part-rocznik-2"></div>';
            $rocznik_html .= '</div>';

            $rocznik_html .= '<div class="rocznik-range">';
            $rocznik_html .= '<div id="min-rocznik" class="minimum_rocznik" data-value="' . $min_rocznik . '"></div>';
            $rocznik_html .= '<p>&nbsp — &nbsp</p>';
            $rocznik_html .= '<div id="max-rocznik" class="maximum_rocznik" data-value="' . $max_rocznik . '"></div>';
            $rocznik_html .= '</div>';
        }
    
        $colors = [];
        $color_query = "
            SELECT DISTINCT t.term_id, t.name
            FROM {$wpdb->term_relationships} tr
            INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
            INNER JOIN {$wpdb->terms} t ON tt.term_id = t.term_id
            WHERE tt.taxonomy = 'pa_kolor'
            AND tr.object_id IN (" . implode(",", $product_ids) . ")
        ";
        $colors = $wpdb->get_results($color_query);
    
        $types = [];
        $type_query = "
            SELECT DISTINCT t.term_id, t.name
            FROM {$wpdb->term_relationships} tr
            INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
            INNER JOIN {$wpdb->terms} t ON tt.term_id = t.term_id
            WHERE tt.taxonomy = 'pa_typ_podwozia'
            AND tr.object_id IN (" . implode(",", $product_ids) . ")
        ";
        $types = $wpdb->get_results($type_query);
        
        wp_send_json_success([
            'colors' => $colors,
            'types'  => $types,
            'price'  => $price_html,
            'rocznik'=> $rocznik_html,
        ]);
    }

    public static function models_query_onLoad($marka_name, $model_name) {
        global $wpdb;
    

    
        $marka_term_id = self::get_term_id_by_name($marka_name, 'pa_marka');
        if (!$marka_term_id) {
            //error_log("No marka term found for: $marka_name");
            return [];
        }
    
        $model_query = "
            SELECT DISTINCT t.term_id, t.name
            FROM {$wpdb->term_relationships} tr
            INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
            INNER JOIN {$wpdb->terms} t ON tt.term_id = t.term_id
            WHERE tt.taxonomy = 'pa_model'
            AND tr.object_id IN (
                SELECT tr2.object_id
                FROM {$wpdb->term_relationships} tr2
                INNER JOIN {$wpdb->term_taxonomy} tt2 ON tr2.term_taxonomy_id = tt2.term_taxonomy_id
                WHERE tt2.term_id = %d AND tt2.taxonomy = 'pa_marka'
            )
        ";
        $models = $wpdb->get_results($wpdb->prepare($model_query, $marka_term_id));
    
        $models_data   = [];
        $min_price    = null;
        $max_price    = null;
        $price_html   = null;


        if ($models) {
            foreach ($models as $model) {
                $model_id = $model->term_id;
    
                $price_query = "
                    SELECT pm.meta_value
                    FROM {$wpdb->postmeta} pm
                    WHERE pm.meta_key = '_regular_price'
                    AND pm.post_id IN (
                        SELECT object_id
                        FROM {$wpdb->term_relationships} tr
                        WHERE tr.term_taxonomy_id IN (
                            SELECT term_taxonomy_id
                            FROM {$wpdb->term_taxonomy}
                            WHERE term_id = %d AND taxonomy = 'pa_model'
                        )
                    )
                ";
    
                $price_results = $wpdb->get_results($wpdb->prepare($price_query, $model_id));
    
                $prices = [];
                foreach ($price_results as $price) {
                    $price_value = floatval(str_replace([' ', ','], '', $price->meta_value));
    
                    if ($price_value > 0) {
                        $prices[] = $price_value;
    
                        if ($min_price === null || $price_value < $min_price) {
                            $min_price = $price_value;
                        }
                        if ($max_price === null || $price_value > $max_price) {
                            $max_price = $price_value;
                        }
                    }
                }

                //error_log('models data: ' . print_r($models_data,true));
    
                if (!empty($prices)) {

                    $models_data[$model->name] = [
                        'term_id' => $model->term_id,
                        'prices' => $prices,
                    ];
                }
            }
        }
        if ($min_price !== null) {
            $min_price = floor($min_price / 1000) * 1000;
        }
        
        if ($max_price !== null) {
            $max_price = ceil($max_price / 1000) * 1000;
        }

        if($min_price && $max_price) {
            $price_html .= '<span class="price-title">Cena</span>';
           
            $price_html .= '<div class="slider-container">';
            $price_html .= '<div id="slider-range-price" class="strap-price"></div>';
            $price_html .= '<div class="part-price-1"></div>';
            $price_html .= '<div class="part-price-2"></div>';
            $price_html .= '</div>';

            $price_html .= '<div class="price-range">';
            $price_html.= '<div id="min-price" class="minimum_price" data-value="' . $min_price . '"></div><div>&nbspzł&nbsp</div>';
            $price_html .= '<p>&nbsp — &nbsp</p>';
            $price_html .= '<div id="max-price" class="maximum_price" data-value="' . $max_price. '"></div><div>&nbspzł&nbsp</div>';
            $price_html .= '</div>';
        }

    
        $colors = [];
    
        $color_query = "
            SELECT DISTINCT t.term_id, t.name
            FROM {$wpdb->term_relationships} tr
            INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
            INNER JOIN {$wpdb->terms} t ON tt.term_id = t.term_id
            WHERE tt.taxonomy = 'pa_kolor'
            AND tr.object_id IN (
                SELECT tr2.object_id
                FROM {$wpdb->term_relationships} tr2
                INNER JOIN {$wpdb->term_taxonomy} tt2 ON tr2.term_taxonomy_id = tt2.term_taxonomy_id
                WHERE tt2.term_id = %d AND tt2.taxonomy = 'pa_marka'
            )
        ";

        $colors= $wpdb->get_results($wpdb->prepare($color_query, $marka_term_id));

        $types = [];
    
        $type_query = "
            SELECT DISTINCT t.term_id, t.name
            FROM {$wpdb->term_relationships} tr
            INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
            INNER JOIN {$wpdb->terms} t ON tt.term_id = t.term_id
            WHERE tt.taxonomy = 'pa_typ_podwozia'
            AND tr.object_id IN (
                SELECT tr2.object_id
                FROM {$wpdb->term_relationships} tr2
                INNER JOIN {$wpdb->term_taxonomy} tt2 ON tr2.term_taxonomy_id = tt2.term_taxonomy_id
                WHERE tt2.term_id = %d AND tt2.taxonomy = 'pa_marka'
            )
        ";

        $types= $wpdb->get_results($wpdb->prepare($type_query, $marka_term_id));

        $max_rocznik = null;
        $min_rocznik = null;
        $rocznik_html = null;

        $rocznik_query = "
            SELECT DISTINCT t.term_id, t.name
            FROM {$wpdb->term_relationships} tr
            INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
            INNER JOIN {$wpdb->terms} t ON tt.term_id = t.term_id
            WHERE tt.taxonomy = 'pa_rocznik'
            AND tr.object_id IN (
                SELECT tr2.object_id
                FROM {$wpdb->term_relationships} tr2
                INNER JOIN {$wpdb->term_taxonomy} tt2 ON tr2.term_taxonomy_id = tt2.term_taxonomy_id
                WHERE tt2.term_id = %d AND tt2.taxonomy = 'pa_marka'
        )
    ";
    
        $rocznik_terms = $wpdb->get_results($wpdb->prepare($rocznik_query, $marka_term_id));
    
        if ($rocznik_terms) {
            foreach ($rocznik_terms as $term) {
                $rocznik_value = intval($term->name);
            
                if ($min_rocznik === null || $rocznik_value < $min_rocznik) {
                    $min_rocznik = $rocznik_value;
                }
            
                if ($max_rocznik === null || $rocznik_value > $max_rocznik) {
                    $max_rocznik = $rocznik_value;
                }
            }
        }

        if($min_rocznik && $max_rocznik) {
            $rocznik_html .= '<span class="rocznik-title">Rocznik</span>';
                   
            $rocznik_html .= '<div class="slider-container">';
            $rocznik_html .= '<div id="slider-range-rocznik" class="strap-rocznik"></div>';
            $rocznik_html .= '<div class="part-rocznik-1"></div>';
            $rocznik_html .= '<div class="part-rocznik-2"></div>';
            $rocznik_html .= '</div>';

            $rocznik_html .= '<div class="rocznik-range">';
            $rocznik_html .= '<div id="min-rocznik" class="minimum_rocznik" data-value="' . $min_rocznik . '"></div>';
            $rocznik_html .= '<p>&nbsp — &nbsp</p>';
            $rocznik_html .= '<div id="max-rocznik" class="maximum_rocznik" data-value="' . $max_rocznik . '"></div>';
            $rocznik_html .= '</div>';
        }

        wp_send_json_success([
            'models' => $models_data,
            'colors' => $colors,
            'types'  => $types,
            'price'  => $price_html,
            'rocznik'=> $rocznik_html,
        ]);
    }
    
    
    
    public static function product_price_filter_query($marka, $model, $rocznikMin, $rocznikMax, $product_type, $product_color) {
        global $wpdb;

        $query = "
            SELECT pm_price.meta_value 
            FROM {$wpdb->postmeta} pm_price
            INNER JOIN {$wpdb->postmeta} pm_rocznik ON pm_price.post_id = pm_rocznik.post_id 
            WHERE pm_price.meta_key = '_regular_price' 
            AND pm_rocznik.meta_key = 'pa_rocznik'
            AND pm_marka.meta_key = 'pa_marka'
            AND pm_model.meta_key = 'pa_model'
            AND pa_typ_podwozia.meta_key = 'pa_typ_podwozia'
            AND pa_kolor.meta_key = 'pa_kolor'
            AND pm_price.meta_value > %d 
            AND pm_price.meta_value < %d 
            AND pm_rocznik.meta_value > %d 
            AND pm_rocznik.meta_value < %d
            AND pm_marka.meta_value = %s
            AND pm_model.meta_value = %s
            AND pa_typ_podwozia.meta_value = %s
            AND pa_kolor.meta_value = %s
        ";
    
        $prepared_query = $wpdb->prepare($query, $rocznikMin, $rocznikMax, $marka, $model, $product_type, $product_color);
        $results = $wpdb->get_col($prepared_query);
    
        if (!empty($results)) {
            $prices = array_map('floatval', $results);
            return array(
                'min' => min($prices),
                'max' => max($prices),
            );
        }
    
        return false;
    }
    

    public static function product_rocznik_query($marka, $model, $priceMin, $priceMax, $product_type, $product_color) {
        global $wpdb;
    
        $query = "
            SELECT DISTINCT t.name 
            FROM {$wpdb->terms} t
            INNER JOIN {$wpdb->term_taxonomy} tt ON t.term_id = tt.term_id
            INNER JOIN {$wpdb->term_relationships} tr ON tt.term_id = tr.term_taxonomy_id
            INNER JOIN {$wpdb->postmeta} pm_price ON tr.object_id = pm_price.post_id
            INNER JOIN {$wpdb->postmeta} pm_marka ON tr.object_id = pm_marka.post_id
            INNER JOIN {$wpdb->postmeta} pm_model ON tr.object_id = pm_model.post_id
            INNER JOIN {$wpdb->postmeta} pm_typ ON tr.object_id = pm_typ.post_id
            INNER JOIN {$wpdb->postmeta} pm_kolor ON tr.object_id = pm_kolor.post_id
            WHERE tt.taxonomy = 'pa_rocznik'
            AND pm_price.meta_key = '_regular_price' 
            AND pm_marka.meta_key = 'pa_marka'
            AND pm_model.meta_key = 'pa_model'
            AND pm_typ.meta_key = 'pa_typ_podwozia'
            AND pm_kolor.meta_key = 'pa_kolor'
            AND pm_price.meta_value BETWEEN %d AND %d
            AND (%s = '' OR pm_marka.meta_value = %s)
            AND (%s = '' OR pm_model.meta_value = %s)
            AND (%s = '' OR pm_typ.meta_value = %s)
            AND (%s = '' OR pm_kolor.meta_value = %s)
        ";
    
        $prepared_query = $wpdb->prepare($query, 
            $priceMin, $priceMax, 
            $marka, $marka, 
            $model, $model, 
            $product_type, $product_type, 
            $product_color, $product_color
        );
    
        $results = $wpdb->get_col($prepared_query);
    
        if (!empty($results)) {
            $years = array_map('intval', $results);
            return array(
                'min' => min($years),
                'max' => max($years),
            );
        }
    
        return false;
    }
    
    
    public static function product_type_query($marka, $model, $priceMin, $priceMax, $rocznikMin, $rocznikMax, $product_color) {
        $taxonomy = 'pa_typ_podwozia'; 
        $args = array(
            'taxonomy'   => $taxonomy,
            'hide_empty' => true,
        );
        
        $terms = get_terms($args);
        $term_data = array();
    
        if (!empty($terms) && !is_wp_error($terms)) {
            foreach ($terms as $term) {
                if (self::has_products($term->term_id, $marka, $model, $priceMin, $priceMax, $rocznikMin, $rocznikMax, $product_color)) {
                    $term_data[$term->name] = $term->term_id;
                }
            }
        }
        return $term_data; 
    }

    public static function product_color_query($marka, $model, $priceMin, $priceMax, $rocznikMin, $rocznikMax, $product_type) {
        $taxonomy = 'pa_kolor';
        $args = array(
            'taxonomy'   => $taxonomy,
            'hide_empty' => true,
        );
        
        $terms = get_terms($args);
        $term_data = array();
    
        if (!empty($terms) && !is_wp_error($terms)) {
            foreach ($terms as $term) {
                if (self::has_products($term->term_id, $marka, $model, $priceMin, $priceMax, $rocznikMin, $rocznikMax, $product_type)) {
                    $term_data[$term->name] = $term->term_id;
                }
            }
        }
        return $term_data;
    }
    
    private static function has_products($term_id, $priceMin, $priceMax, $rocznikMin, $rocznikMax, $product_type, $product_color) {
        global $wpdb;
    
        $query = "
            SELECT COUNT(*) FROM {$wpdb->posts} p
            INNER JOIN {$wpdb->term_relationships} tr ON p.ID = tr.object_id
            INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
            INNER JOIN {$wpdb->postmeta} pm_price ON p.ID = pm_price.post_id AND pm_price.meta_key = '_regular_price'
            INNER JOIN {$wpdb->postmeta} pm_rocznik ON p.ID = pm_rocznik.post_id AND pm_rocznik.meta_key = 'pa_rocznik'
            WHERE p.post_status = 'publish'
            AND tt.term_id = %d
        ";
    
        $query_params = [$term_id];
    
        if (!empty($priceMin)) {
            $query .= " AND pm_price.meta_value >= %d";
            $query_params[] = $priceMin;
        }
        if (!empty($priceMax)) {
            $query .= " AND pm_price.meta_value <= %d";
            $query_params[] = $priceMax;
        }
    
        if (!empty($rocznikMin)) {
            $query .= " AND pm_rocznik.meta_value >= %d";
            $query_params[] = $rocznikMin;
        }
        if (!empty($rocznikMax)) {
            $query .= " AND pm_rocznik.meta_value <= %d";
            $query_params[] = $rocznikMax;
        }

        if (!empty($product_type)) {
            $query .= "
                AND EXISTS (
                    SELECT 1 FROM {$wpdb->term_relationships} tr2
                    INNER JOIN {$wpdb->term_taxonomy} tt2 ON tr2.term_taxonomy_id = tt2.term_taxonomy_id
                    WHERE tr2.object_id = p.ID
                    AND tt2.taxonomy = 'pa_typ_podwozia'
                    AND tt2.term_id = %d
                )";
            $query_params[] = $product_type;
        }
    
        if (!empty($product_color)) {
            $query .= "
                AND EXISTS (
                    SELECT 1 FROM {$wpdb->term_relationships} tr3
                    INNER JOIN {$wpdb->term_taxonomy} tt3 ON tr3.term_taxonomy_id = tt3.term_taxonomy_id
                    WHERE tr3.object_id = p.ID
                    AND tt3.taxonomy = 'pa_kolor'
                    AND tt3.term_id = %d
                )";
            $query_params[] = $product_color;
        }
    
        $prepared_query = $wpdb->prepare($query, ...$query_params);
        $count = $wpdb->get_var($prepared_query);
    
        return $count > 0;
    }
}
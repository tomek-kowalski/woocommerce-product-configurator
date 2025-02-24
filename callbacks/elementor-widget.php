<?php

namespace MainStart\ProductViewer;
use Elementor\Widget_Base;
use Elementor\Controls_Manager;

if ( ! defined( 'WPINC' ) ) {
    die;
}

class Katalog_Loader {
    private static $_instance = null;

    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    private function include_widgets_files() {
        require_once(__DIR__ . '/auto_katalog_callback.php');
    }

    public function register_widgets($widgets_manager) {
        $this->include_widgets_files();
        $widgets_manager->register(new \Main\ProductViewer\Callback\AutoKatalog()); 
    }

    public function __construct() {
        add_action('elementor/widgets/register', [$this, 'register_widgets'], 10);
    }
}


Katalog_Loader::instance();

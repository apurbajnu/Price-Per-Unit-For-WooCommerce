<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       apurba.me
 * @since      1.0.0
 *
 * @package    Price_Per_Unit_For_Woocommerce
 * @subpackage Price_Per_Unit_For_Woocommerce/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Price_Per_Unit_For_Woocommerce
 * @subpackage Price_Per_Unit_For_Woocommerce/admin
 * @author     Apurba <apurba.jnu@gmail.com>
 */
class Price_Per_Unit_For_Woocommerce_Admin
{
    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $plugin_name The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $version The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     *
     * @param      string $plugin_name The name of this plugin.
     * @param      string $version The version of this plugin.
     */
    public function __construct($plugin_name, $version)
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->init();
    }

    public function init()
    {
        $this->metafield = new Ap_custom_Metabox();
        $this->metafield->set_meta_fields($this->ranger_meta_field_settings());
        add_action('admin_menu', [$this, 'ppu_settings_page']);
        add_filter(
            'range_meta_boxes_pro_meta_info_ranger_slider_address',
            [$this, 'ranger_address_field_settings'],
            10,
            2
        );
    }

    public function ranger_address_field_settings($option, $post)
    {
        $product = wc_get_product($post->ID);
        $childs = $product->get_children();
        $childs = array_combine($childs, $childs);
        $childs[0] = __('No Variation', 'price-per-unit-for-woocommerce');
        return $childs;
    }

    public function ppu_settings_page()
    {
        add_menu_page(
            __('Price Per Unit', 'price-per-unit-for-woocommerce'),
            __('Price Per Unit', 'price-per-unit-for-woocommerce'),
            'manage_options',
            'bds_plugin',
            [
                $this,
                'bds_plugin_page_cb',
            ],
            'dashicons-share-alt',
            99
        );
    }

    public function bds_plugin_page_cb()
    {
        require_once __DIR__ . '/partials/price-per-unit-for-woocommerce-admin-display.php';
    }

    //create ranger metafield
    public function ranger_meta_field_settings()
    {
        $fields = [

            [
                'label' => __('Price Per Unit Metabox\'s', 'price-per-unit-for-woocommerce'),
                'id' => 'range_meta_boxes_pro',
                'post_options' => [
                    'post_type' => 'product',
                    'context' => 'normal',
                    'priority' => 'default',
                ],
                'inputs' => [

                    [
                        'name' => 'ranger_slider_status',
                        'label' => __('Status', 'price-per-unit-for-woocommerce'),
                        'class' => 'ranger_slider_status',
                        'type' => 'select',
                        'input_option' => [
                            'inactive' => __('Inactive', 'price-per-unit-for-woocommerce'),
                            'active' => __('Active', 'price-per-unit-for-woocommerce'),
                        ],

                    ],

                    [
                        'name' => 'range_slider_measurement_type',
                        'type' => 'select',
                        'label' => __('Measurement Type', 'price-per-unit-for-woocommerce'),
                        'input_option' => [
                            'l' => __('Length', 'price-per-unit-for-woocommerce'),
                            'w' => __('Weight', 'price-per-unit-for-woocommerce'),
                            'v' => __('//Volume', 'price-per-unit-for-woocommerce'),
                            'a' => __('//Area', 'price-per-unit-for-woocommerce'),
                        ],

                    ],

                    [
                        'name' => 'range_slider_unit',
                        'type' => 'text',
                        'label' => __('Unit', 'price-per-unit-for-woocommerce'),
                        'placeholder' => 'm|cm|custom',
                        'value' => '',
                    ],

                    [
                        'name' => 'range_slider_view',
                        'type' => 'select',
                        'label' => 'Slider View',
                        'input_option' => ['slider' => __('Slider', 'price-per-unit-for-woocommerce'), 'numeric' => __('Numeric', 'price-per-unit-for-woocommerce')],

                    ],

                    [
                        'name' => 'meta_info',
                        'label' => __('Price Per Unit Repetitive Fields', 'price-per-unit-for-woocommerce'),
                        'type' => 'repeater',
                        'child_inputs' => [

                            [
                                'name' => 'ranger_slider_dimension',
                                'placeholder' => __('Select Range Slider Dimension', 'price-per-unit-for-woocommerce'),
                                'label' => __('Dimension ', 'price-per-unit-for-woocommerce'),
                                'class' => 'ranger_slider_dimension',
                                'type' => 'select',
                                'input_option' => [
                                    'one_dimension' => __('One Dimension', 'price-per-unit-for-woocommerce'),
                                    'two_dimension' => __('//Two Dimension', 'price-per-unit-for-woocommerce'),
                                    'three_dimension' => __('//Three Dimension', 'price-per-unit-for-woocommerce'),
                                ],

                            ],

                            [
                                'name' => 'range_slider_title_value_for_x',
                                'type' => 'text',
                                'label' => __('Title for X axis', 'price-per-unit-for-woocommerce'),
                                'placeholder' => __('Title', 'price-per-unit-for-woocommerce'),
                                'value' => '',
                                'class' => 'range_slider_title_value_for_x',
                                'dependency' => [
                                    'name' => 'ranger_slider_dimension',
                                    'value' => ['one_dimension', 'two_dimension', 'three_dimension'],
                                ],
                            ],

                            [
                                'name' => 'range_slider_min_value_x',
                                'type' => 'text',
                                'label' => __('Min Value for X axis', 'price-per-unit-for-woocommerce'),
                                'placeholder' => __('Min Value', 'price-per-unit-for-woocommerce'),
                                'value' => '',
                                'class' => 'range_slider_min_value_x',
                                'dependency' => [
                                    'name' => 'ranger_slider_dimension',
                                    'value' => ['one_dimension', 'two_dimension', 'three_dimension'],
                                ],
                            ],
                            [
                                'name' => 'range_slider_max_value_x',
                                'type' => 'text',
                                'label' => __('Max Value for X axis', 'price-per-unit-for-woocommerce'),
                                'placeholder' => __('Max Value', 'price-per-unit-for-woocommerce'),
                                'value' => '',
                                'class' => 'range_slider_max_value_x',
                                'dependency' => [
                                    'name' => 'ranger_slider_dimension',
                                    'value' => ['one_dimension', 'two_dimension', 'three_dimension'],
                                ],
                            ],
                            [
                                'name' => 'range_slider_step_value_x',
                                'type' => 'text',
                                'label' => __('Step Value for X axis', 'price-per-unit-for-woocommerce'),
                                'placeholder' => __('Steps', 'price-per-unit-for-woocommerce'),
                                'value' => '',
                                'class' => 'range_slider_step_value_x',
                                'dependency' => [
                                    'name' => 'ranger_slider_dimension',
                                    'value' => ['one_dimension', 'two_dimension', 'three_dimension'],
                                ],
                            ],

                            [
                                'name' => 'ranger_slider_labels',
                                'type' => 'textarea',
                                'label' => __('Responsibilities', 'price-per-unit-for-woocommerce'),
                                'placeholder' => __('Seperate  value and price by "|" and Price Difference by comma"," ex (1000|5,3000|4)'),
                            ],

                        ],
                    ],

                ],

            ],

        ];

        return apply_filters('ap_meta_apply', $fields);
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles()
    {
        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Range_slider_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Range_slider_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        wp_enqueue_style(
            $this->plugin_name,
            plugin_dir_url(__FILE__) . 'css/price-per-unit-for-woocommerce-admin.css',
            [],
            $this->version,
            'all'
        );
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts()
    {
        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Range_slider_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Range_slider_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        wp_enqueue_script(
            $this->plugin_name,
            plugin_dir_url(__FILE__) . 'js/price-per-unit-for-woocommerce-admin.js',
            ['jquery'],
            $this->version,
            false
        );
    }
}

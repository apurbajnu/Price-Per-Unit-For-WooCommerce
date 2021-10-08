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
class Price_Per_Unit_For_Woocommerce_Admin {

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
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;
		$this->init();
	}

	public function init() {

		$this->metafield = new Ap_custom_Metabox();
		$this->metafield->set_meta_fields( $this->ranger_meta_field_settings() );
		add_action('admin_menu',array($this,'ppu_settings_page'));
		add_filter( 'range_meta_boxes_pro_meta_info_ranger_slider_address',
			array($this,'ranger_address_field_settings'),
			10,
			2 );

	}

	public function ranger_address_field_settings($option, $post){
			$product    = wc_get_product( $post->ID );
			$childs     = $product->get_children();
			$childs     = array_combine( $childs, $childs );
			$childs [0] = __( 'No Variation', 'ppu' );
			return $childs;

	}

	public function ppu_settings_page() {
		add_menu_page( __( 'Price Per Unit', 'ppu' ),
			__( 'Price Per Unit', 'ppu' ),
			'manage_options',
			'bds_plugin',
			array(
				$this,
				'bds_plugin_page_cb',
			),
			'dashicons-share-alt',
			99 );
	}


	public function bds_plugin_page_cb() {
		require_once (__DIR__.'/partials/price-per-unit-for-woocommerce-admin-display.php');
	}


	//create ranger metafield
	public function ranger_meta_field_settings() {

		$fields = array(


			array(
				'label'        => __( 'Price Per Unit Metabox\'s', 'ppu' ),
				'id'           => 'range_meta_boxes_pro',
				'post_options' => array(
					'post_type' => 'product',
					'context'   => 'normal',
					'priority'  => 'default',
				),
				'inputs'       => array(

					array(
						'name'         => 'ranger_slider_status',
						'label'        => __( 'Status', 'ppu' ),
						'class'        => 'ranger_slider_status',
						'type'         => 'select',
						'input_option' => [						
							'inactive' => __( 'Inactive', 'ppu' ),
							'active'   => __( 'Active', 'ppu' ),
						],

					),

					array(
						'name'         => 'range_slider_measurement_type',
						'type'         => 'select',
						'label'        => __( 'Measurement Type', 'ppu' ),
						'input_option' => [
							'l' => __( 'Length', 'ppu' ),
							'w' => __( 'Weight', 'ppu' ),
							'v' => __( '//Volume', 'ppu' ),
							'a' => __( '//Area', 'ppu' ),
						],

					),

					array(
						'name'        => 'range_slider_unit',
						'type'        => 'text',
						'label'       => __( 'Unit', 'ppu' ),
						'placeholder' => 'm|cm|custom',
						'value'       => '',
					),

					array(
						'name'         => 'range_slider_view',
						'type'         => 'select',
						'label'        => 'Slider View',
						'input_option' => [ 'slider' => 'Slider','numeric' => 'Numeric' ],

					),


					array(
						'name'         => 'meta_info',
						'label'        => __( 'Price Per Unit Repetitive Fields', 'ppu' ),
						'type'         => 'repeater',
						'child_inputs' => array(

							array(
								'name'         => 'ranger_slider_dimension',
								'placeholder'  => __( 'Select Range Slider Dimension', 'ppu' ),
								'label'        => __( 'Dimension ', 'ppu' ),
								'class'        => 'ranger_slider_dimension',
								'type'         => 'select',
								'input_option' => [
									'one_dimension'   => __( 'One Dimension', 'ppu' ),
									'two_dimension'   => __( '//Two Dimension', 'ppu' ),
									'three_dimension' => __( '//Three Dimension', 'ppu' ),
								],

							),

							array(
								'name'        => 'range_slider_title_value_for_x',
								'type'        => 'text',
								'label'       => __( 'Title for X axis', 'ppu' ),
								'placeholder' => __( 'Title', 'ppu' ),
								'value'       => '',
								'class'       => 'range_slider_title_value_for_x',
								'dependency'  => array(
									'name'  => 'ranger_slider_dimension',
									'value' => [ 'one_dimension', 'two_dimension', 'three_dimension' ],
								),
							),

							array(
								'name'        => 'range_slider_min_value_x',
								'type'        => 'text',
								'label'       => __( 'Min Value for X axis', 'ppu' ),
								'placeholder' => __( 'Min Value', 'ppu' ),
								'value'       => '',
								'class'       => 'range_slider_min_value_x',
								'dependency'  => array(
									'name'  => 'ranger_slider_dimension',
									'value' => [ 'one_dimension', 'two_dimension', 'three_dimension' ],
								),
							),
							array(
								'name'        => 'range_slider_max_value_x',
								'type'        => 'text',
								'label'       => __( 'Max Value for X axis', 'ppu' ),
								'placeholder' => __( 'Max Value', 'ppu' ),
								'value'       => '',
								'class'       => 'range_slider_max_value_x',
								'dependency'  => array(
									'name'  => 'ranger_slider_dimension',
									'value' => [ 'one_dimension', 'two_dimension', 'three_dimension' ],
								),
							),
							array(
								'name'        => 'range_slider_step_value_x',
								'type'        => 'text',
								'label'       => __( 'Step Value for X axis', 'ppu' ),
								'placeholder' => __( 'Steps', 'ppu' ),
								'value'       => '',
								'class'       => 'range_slider_step_value_x',
								'dependency'  => array(
									'name'  => 'ranger_slider_dimension',
									'value' => [ 'one_dimension', 'two_dimension', 'three_dimension' ],
								),
							),

							array(
								'name'        => 'ranger_slider_labels',
								'type'        => 'textarea',
								'label'       => __( 'Responsibilities', 'ppu' ),
								'placeholder' => __( 'Seperate  value and price by "|" and Price Difference by comma"," ex (1000|5,3000|4)' ),
							),


						),
					),

				),


			),

		);

		return apply_filters( 'ap_meta_apply', $fields );

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

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

		wp_enqueue_style( $this->plugin_name,
			plugin_dir_url( __FILE__ ) . 'css/price-per-unit-for-woocommerce-admin.css',
			array(),
			$this->version,
			'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

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

		wp_enqueue_script( $this->plugin_name,
			plugin_dir_url( __FILE__ ) . 'js/price-per-unit-for-woocommerce-admin.js',
			array( 'jquery' ),
			$this->version,
			false );

	}

}

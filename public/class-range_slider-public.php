<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       apurba.me
 * @since      1.0.0
 *
 * @package    Range_slider
 * @subpackage Range_slider/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 * @package    Range_slider
 * @subpackage Range_slider/public
 * @author     Apurba <apurba.jnu@gmail.com>
 */
class Range_slider_Public {

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
	 * @param      string $plugin_name The name of the plugin.
	 * @param      string $version The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
		add_shortcode( 'ap-range-slider', array( $this, 'ap_range_slider_callback' ) );
		add_action( 'woocommerce_before_calculate_totals', array( $this, 'set_cart_item_sale_price' ), 99, 1 );
		add_action( 'woocommerce_before_add_to_cart_button', array( $this, 'insertRangeSlider' ), 10 );

		/*add new tab woocommerce description */
		add_filter( 'woocommerce_product_tabs', array( $this, 'add_price_info_details' ) );
		/**
		 * Output engraving field.
		 */
		add_filter( 'woocommerce_add_cart_item_data', array( $this, 'insert_custom_slider_data_to_cart' ), 10, 3 );
		add_action( 'woocommerce_checkout_create_order_line_item',
			array( $this, 'save_cart_item_custom_meta_as_order_item_meta' ),
			10,
			4 );
		add_filter( 'woocommerce_get_item_data', array( $this, 'ranger_slider_point_display' ), 10, 2 );
		add_filter( 'woocommerce_get_price_html', array( $this, 'ranger_slider_price_html_callback' ), 10, 2 );
		add_action( 'wp_ajax_get_slider_value',
			array( $this, 'get_range_slider_value_on_variation_id' ) );
		add_action( 'wp_ajax_nopriv_get_slider_value',
			array( $this, 'get_range_slider_value_on_variation_id' ) );

		add_action( 'wp_ajax_get_slider_value_for_simple_product',
			array( $this, 'get_range_slider_value_on_simple_product' ) );
		add_action( 'wp_ajax_nopriv_get_slider_value_for_simple_product',
			array( $this, 'get_range_slider_value_on_simple_product' ) );

	}


	function ranger_slider_price_html_callback( $price_html, $product ) {
		$product_id  = $product->get_id();
		$status      = 0;
		$measurement = 'l';
		$unit        = '';
		$value       = get_post_meta( $product_id, 'range_meta_boxes_pro', true );
		if ( is_array( $value ) && array_key_exists( 'range_slider_unit', $value ) ) {
			$unit = $value['range_slider_unit'];
		}
		$price = $product->get_price();
		if ( ! empty( $unit ) ) {
			$unit = '/' . $unit;
		}
		if ( is_array( $value ) && array_key_exists( 'ranger_slider_status', $value ) ) {
			$status = $value['ranger_slider_status'];
		}

		if ( is_array( $value ) && array_key_exists( 'range_slider_measurement_type', $value ) ) {
			$measurement = $value['range_slider_measurement_type'];
		}
		if ( $status === 'active' ) {
		    $html = __( "Minimum Price", 'ppu' );
			$html .= sprintf( get_woocommerce_price_format(),
				'<span class="woocommerce-Price-currencySymbol">'.' ' . get_woocommerce_currency_symbol() . '</span>',
				$price );
			$sup  = '';
			switch ( $measurement ) {
				case 'v':
					$sup = '<sup>3</sup>';
					break;
				case 'a':
					$sup = '<sup>2</sup>';
					break;
			}
			


			$price_html = "<span class='amount'> $html $unit $sup</span>";
		}

		return $price_html;
	}

	/**
	 * Add 2 custom product data tabs
	 */
	function add_price_info_details( $tabs ) {

		// Adds the new tab
		$tabs['price_per_unit_tab'] = array(
			'title'    => __( 'Price Information', 'ppu' ),
			'priority' => 50,
			'callback' => array( $this, 'price_info_build' ),
		);

		return $tabs;

	}

	function price_info_build() {
		// The new tab content
		echo '<p class="ppu-loaded"> loading...</p>';
	}


	public
	function get_range_slider_value_on_simple_product() {
		$nonce = $_POST['nonce'];

		if ( ! wp_verify_nonce( $nonce, 'ranger-nonce' ) ) {
			die ( __( 'You are not allowed', 'ppu' ) );
		}
		$product_id = $_POST['product_id'];
		$product    = wc_get_product( $product_id );
		if ( $product->is_type( 'simple' ) ) {
			$data = $this->get_slider_range_post_meta( null, $product_id );
			wp_send_json( $data );
		}
		wp_die( __(  'Slider Not Available', 'ppu' ) );
	}

	public function get_slider_range_post_meta( $id, $product_id = null ) {


		$value           = get_post_meta( $product_id, 'range_meta_boxes_pro', true );
		$product         = $id ? wc_get_product( $id ) : wc_get_product( $product_id );
		$primitive_price = $product->get_price();
		$data            = [];
		$instance_meta   = null;


		if ( gettype( $value ) == "array" ) {
			$unit        = $value['range_slider_unit'];
			$status      = $value['ranger_slider_status'];
			$measurement = $value['range_slider_measurement_type'];
			if ( array_key_exists( 'meta_info', $value ) ) {
				$value        = $value['meta_info'];
				$encoded_val  = json_decode( $value,
					JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE );
				$encoded_vals = $encoded_val ['range_meta_boxes_pro_meta_info_parent'];

				if ( is_array( $encoded_vals ) && ! empty( $encoded_vals ) ) {
					$instance_meta = $encoded_vals[0];
				}
			}
		}

		if ( is_array( $instance_meta ) && $status === 'active' ) {


			$x_dimension = [];
			$y_dimension = [];
			$z_dimension = [];


			$dimension       = $instance_meta['ranger_slider_dimension'];
			$min_x           = $instance_meta['range_slider_min_value_x'];
			$max_x           = $instance_meta['range_slider_max_value_x'];
			$title_x         = $instance_meta['range_slider_title_value_for_x'];
			$step_x          = $instance_meta['range_slider_step_value_x'];
			$x_labels_sorted = null;

			$x_dimension = [
				'title'  => $title_x,
				'min'    => $min_x,
				'max'    => $max_x,
				'step'   => $step_x,
				'values' => $x_labels_sorted,
			];

			$count_param = $instance_meta['ranger_slider_labels'];
			$count_param = explode( ',', $count_param );

			$price = [];
			foreach ( $count_param as $item ) {
				$item = explode( '|', $item );
				//$labels[ $item[1] ] = $item[0];
				$price[ $item[0] ] = $item[1];
			}

			$woo_price_format     = sprintf( get_woocommerce_price_format(),
				'<span class="woocommerce-Price-currencySymbol">' . get_woocommerce_currency_symbol() . '</span>',
				'given_price' );
			$data['range_slider'] = [
				'dimension'       => $dimension,
				'unit'            => $unit,
				'x_dimension'     => $x_dimension,
				'prices'          => $price,
				'primitive_price' => $primitive_price,
				'measurement'     => $measurement,
				'status'          => $status,
				'woo_price'       => '<span class="ppu-price-format">' . $woo_price_format . '</span>',
			];


		} else {
			$data['range_slider'] = 'Slider Not Available';
		}


		return $data;


	}

	public
	function get_range_slider_value_on_variation_id() {

		$nonce = $_POST['nonce'];

		if ( ! wp_verify_nonce( $nonce, 'ranger-nonce' ) ) {
			die ( __( 'You are not allowed', 'ppu' ) );
		}
		$vaiation_id = $_POST['variation_id'];
		$product_id  = $_POST['product_id'];
		$data        = $this->get_slider_range_post_meta( $vaiation_id, $product_id );
		wp_send_json( $data );

	}

	function ranger_slider_point_display( $item_data, $cart_item ) {


		$unit        = '';
		$measurement = '';
		$sup         = '';

		if ( ! empty( $cart_item['ranger_slider_measurement'] ) ) {
			$measurement = $cart_item['ranger_slider_measurement'];

			switch ( $measurement ) {
				case 'Volume':
					$sup = '3';
					break;
				case 'Area':
					$sup = '2';
					break;
			}


		}

		if ( ! empty( $cart_item['ranger_slider_unit'] ) ) {
			$raw_unit = $cart_item['ranger_slider_unit'];
			if ( ! empty( $sup ) ) {
				$unit = $raw_unit . '<sup>' . $sup . '</sup>';
			} else {
				$unit = $raw_unit;
			}

		}

		if ( ! empty( $cart_item['ranger_slider_total_point'] ) ) {
			$item_data[] = array(
				'key'     => __( 'Total', 'ppu' ) . ' ' . $measurement,
				'value'   => wc_clean( $cart_item['ranger_slider_total_point'] ) . $unit,
				'display' => '',
			);
		}

		return $item_data;
	}

	function save_cart_item_custom_meta_as_order_item_meta( $item, $cart_item_key, $values, $order ) {

		if ( isset( $values['ranger_slider_min_x'] ) && isset( $values['ranger_slider_max_x'] ) && isset( $values['ranger_slider_min_y'] ) && isset( $values['ranger_slider_max_y'] ) && isset( $values['ranger_slider_min_z'] ) && isset( $values['ranger_slider_max_z'] ) && isset( $values['ranger_slider_unit'] ) && isset( $values['ranger_slider_measurement'] ) ) {
			$item->update_meta_data( 'range_slider_data',
				[
					$values['ranger_slider_min_x'],
					$values['ranger_slider_max_x'],
					$values['ranger_slider_total_point'],
					$values['ranger_slider_unit'],
					$values['ranger_slider_measurement'],
				] );
		}

	}

	function set_cart_item_sale_price( $cart ) {
		if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
			return;
		}

//
//		if ( did_action( 'woocommerce_before_calculate_totals' ) >= 2 ) {
//			return;
//		}

		// Iterate through each cart item
		foreach ( $cart->get_cart() as $cart_item ) {
			//pri_dump( $cart_item );

			$get_price = $cart_item['data']->get_sale_price(); // get sale price
			$set_price = $this->calculate_price(
				$cart_item['ranger_slider_min_x'],
				$cart_item['ranger_slider_max_x'],
				$cart_item['variation_id'],
				$cart_item['product_id'] );
			if ( ! empty( $set_price ) ) {
				$cart_item['data']->set_price( number_format( (float) $set_price, 2, '.', '' ) ); // Set the sale price
			}


		}


	}

	function calculate_price(
		$current_min_x,
		$current_max_x,
		$varient_id,
		$product_id
	) {
		$data = $this->get_slider_range_post_meta( $varient_id, $product_id );
		$data = $data['range_slider'];

		if ( $data == 'Slider Not Available' ) {
			return '';
		}
		$product         = ( $varient_id ) ? wc_get_product( $varient_id ) : wc_get_product( $product_id );
		$primitive_price = 0;//(float) $product->get_price();
		$current_max     = 0;
		$dimension       = $data['dimension'];


		if ( $dimension == 'one_dimension' ) {
			$min = $current_min_x;
			$max = $current_max_x;
		}
		$current_max = $max;

//        pri_dump($price);

		if ( is_array( $data ) ) {
			$prices = $data['prices'];
		}

//		extract( $data ); // prices and //labels


		if ( is_array( $data ) ) {


			$prices_array_keys = array_keys( $prices );
			$currentPoint      = $current_max;
			$cachePoint        = 0;
			$previousPoint     = null;
			$startCachePoint   = null;
			$finishCachePoint  = null;


			foreach ( $prices_array_keys as $key => $value ) {

				$starting_price = $prices[ $prices_array_keys[ $key ] ]; //1
				$starting_point = $prices_array_keys[ $key ]; //10
				$next_point     = null;

				if ( $key !== count( $prices_array_keys ) - 1 ) {
					$next_point = $prices_array_keys[ $key + 1 ]; // 10
				}


				if ( $currentPoint > $starting_point && $next_point !== null ) {
					continue;
				} else {

					$price = (float) $starting_price * (float) $currentPoint;
					break;
				}

				if ( $currentPoint <= $starting_point ) {
					$price = $starting_price * $currentPoint;
					break;
				}

			}


		}
		$price = $primitive_price + $price;

		return $price;

	}

	function insert_custom_slider_data_to_cart( $cart_item_data, $product_id, $variation_id ) {
		$product             = wc_get_product( $product_id );
		$default_max_x       = '';
		$default_min_x       = '';
		$default_total_point = '';
		$default_unit        = '';
		$default_measurement = '';

		if ( $product->is_type( 'simple' ) ) {
			$data = $this->get_slider_range_post_meta( null, $product_id );
			if ( array_key_exists( 'range_slider', (array) $data ) ) {
				$data = $data['range_slider'];
			}


			if ( is_array( $data ) && array_key_exists( 'status', $data ) && $data['status'] == 'active' ) {

				$default_min_x       = $data['x_dimension']['min'];
				$default_max_x       = ( $default_min_x > 0 ) ? $default_min_x : $data['x_dimension']['step'];
				$total_x_point       = $default_max_x;
				$default_total_point = $total_x_point;
				$measurement_unit    = '';
				switch ( $data['measurement'] ) {
					case 'v':
						$measurement_unit = __( 'Volume', 'ppu' );
						break;
					case 'a':
						$measurement_unit = __( 'Area', 'ppu' );
						break;
					case 'l':
						$measurement_unit = __( 'Length', 'ppu' );
						break;
					case 'w':
						$measurement_unit = __( 'Weight', 'ppu' );
						break;
				}
				$default_unit        = $data['unit'];
				$default_measurement = $measurement_unit;
			}
		}
		$ranger_slider_max_x       = filter_input( INPUT_POST, 'ranger_slider_max_x' );
		$ranger_slider_min_x       = filter_input( INPUT_POST, 'ranger_slider_min_x' );
		$ranger_slider_total_point = filter_input( INPUT_POST, 'ranger_slider_total_point' );
		$ranger_slider_unit        = filter_input( INPUT_POST, 'ranger_slider_unit' );
		$ranger_slider_measurement = filter_input( INPUT_POST, 'ranger_slider_measurement' );


		$cart_item_data['ranger_slider_min_x']       = empty( $ranger_slider_min_x ) ? $default_min_x : $ranger_slider_min_x;
		$cart_item_data['ranger_slider_max_x']       = empty( $ranger_slider_max_x ) ? $default_max_x : $ranger_slider_max_x;
		$cart_item_data['ranger_slider_total_point'] = empty( $ranger_slider_total_point ) ? $default_total_point : $ranger_slider_total_point;
		$cart_item_data['ranger_slider_unit']        = empty( $ranger_slider_unit ) ? $default_unit : $ranger_slider_unit;
		$cart_item_data['ranger_slider_measurement'] = empty( $ranger_slider_measurement ) ? $default_measurement : $ranger_slider_measurement;

		return $cart_item_data;
	}

	public
	function insertRangeSlider() {
		echo $this->ap_range_slider_callback( [] );
	}

	public
	function ap_range_slider_callback(
		$options = []
	) {
		$atts = array_merge( [
			'values'      => [ 0, 10 ],
			'one_slider'  => false,
			'min'         => 1000,
			'max'         => 6000,
			'range'       => true,
			'step'        => 1,
			'title'       => '',
			'description' => '',
			'price'       => [ 1000 => '0.5', 3000 => '0.6', 4000 => '0.7', 4500 => 0.8 ],
			'container'   => '.woocommerce-Price-amount',
			'labels'      => [ 1000 => 'start', 2000 => 'Low', 3000 => 'mid', 4000 => 'up', 5000 => 'fin' ],
			'varients'    => []
			//            'labels' => ['start','Low','mid','up','fin','hbj','sdf']

		],
			$options );

		$atts['min']      = (int) $atts['min'];
		$atts['max']      = (int) $atts['max'];
		$atts['step']     = (int) $atts['step'];
		$handaler_classes = 'ui-slider-handle';
		if ( $atts['one_slider'] == true ) {
			$handaler_classes .= ' ui-slider-handle-disabled';
		}

		global $product;
		$current_product_id = $product->get_id();
		$value              = get_post_meta( $current_product_id, 'range_meta_boxes_pro', true );
		$measurement        = '';
		$measurement_unit   = '';
		$unit               = '';
		if ( is_array( $value ) && array_key_exists( 'range_slider_measurement_type', $value ) ) {
			$measurement = $value['range_slider_measurement_type'];
		}
		if ( is_array( $value ) && array_key_exists( 'range_slider_unit', $value ) ) {
			$unit = $value['range_slider_unit'];
		}

		switch ( $measurement ) {
			case 'v':
				$measurement_unit = __( 'Volume', 'ppu' );
				break;
			case 'a':
				$measurement_unit = __( 'Area', 'ppu' );
				break;
			case 'l':
				$measurement_unit = __( 'Length', 'ppu' );
				break;
			case 'w':
				$measurement_unit = __( 'Weight', 'ppu' );
				break;
		}


		ob_start();
		?>

        <div class="ap-range-slider-container" data-product_id="<?php echo $current_product_id; ?>">

            <input type="hidden" class="ranger_slider_total_point" name="ranger_slider_total_point"
                   value="">
            <input type="hidden" name="ranger_slider_unit" class="ranger_slider_unit"
                   value="<?php echo $unit ?>">

            <input type="hidden" name="ranger_slider_measurement" class="ranger_slider_measurement"
                   value="<?php echo $measurement_unit; ?>">

            <div class="x-axis">
                <h3 class="range-slider-title"><?php echo $atts['title'] ?></h3>
                <input style="display:none;" type="number" class="ap-range-slider" name="example_name" value=""/>
                <div class="ranger_slider_fields">
                    <input type="hidden" class="ranger_slider_min_x" name="ranger_slider_min_x"
                           value="0" maxlength="10">
                    <input type="hidden" class="ranger_slider_max_x" name="ranger_slider_max_x"
                           value="0" maxlength="10">
                </div>
            </div>


            <div class="price-per-unit-details" style="display: none">
                <table border="0">
                    <tr>
                        <td><?php echo esc_html__( 'total ',  'ppu' ) . $measurement_unit ?></td>
                        <td class="ppu-total-area">0</td>
                    </tr>
                    <tr>
                        <td><?php echo esc_html__( 'total Cost','ppu' ) ?></td>
                        <td class="ppu-total-cost">0</td>
                    </tr>
                </table>
            </div>


        </div>


		<?php
		$output = ob_get_clean();

		return $output;

	}


	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public
	function enqueue_styles() {

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
		$wp_scripts = wp_scripts();
		wp_enqueue_style( 'plugin_name-admin-ui-css',
			'http://ajax.googleapis.com/ajax/libs/jqueryui/' . $wp_scripts->registered['jquery-ui-core']->ver . '/themes/smoothness/jquery-ui.css',
			false,
			$this->version,
			false );
		wp_enqueue_style( 'range_slider-css',
			plugin_dir_url( __FILE__ ) . 'css/ion.rangeSlider.min.css',
			array(),
			$this->version,
			'all' );
		wp_enqueue_style( $this->plugin_name,
			plugin_dir_url( __FILE__ ) . 'css/range_slider-public.css',
			array(),
			$this->version,
			'all' );


	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public
	function enqueue_scripts() {

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
		wp_enqueue_script( 'jquery-ui-core' );
//        wp_enqueue_script('jquery-ui-slider');
//        wp_enqueue_script('jqury-ui-touch', plugin_dir_url(__FILE__) . 'js/ui.touch.js', array('jquery'), $this->version, false);
		wp_enqueue_script( 'range_slider-js',
			plugin_dir_url( __FILE__ ) . 'js/ion.rangeSlider.js',
			array( 'jquery' ),
			$this->version,
			false );

		wp_enqueue_script( $this->plugin_name,
			plugin_dir_url( __FILE__ ) . 'js/range-slider-public.js',
			array(
				'jquery',
				'jquery-ui-core',
				//'jqury-ui-touch',
				'range_slider-js',
				//'jquery-ui-slider'
			),
			$this->version,
			true );

		wp_localize_script( $this->plugin_name, 'woo_currency_symbol', get_woocommerce_currency_symbol() );
		wp_localize_script( $this->plugin_name,
			'ranger_data',
			[

				'unit'      => esc_html__( 'Unit', 'ppu' ),
				'price'     => esc_html__( 'Price', 'ppu' ),
				'min_price' => esc_html__( 'Minimum Price', 'ppu' ),
				'container' => '.woocommerce-Price-amount',
				'url'       => admin_url( 'admin-ajax.php' ),
				'nonce'     => wp_create_nonce( 'ranger-nonce' ),
			] );

	}

}

if ( ! function_exists( 'pri_dump' ) ) {
	function pri_dump( $data ) {
		echo '<pre>';
		if ( is_object( $data ) || is_array( $data ) ) {
			print_r( $data );
		} else {
			var_dump( $data );
		}
		echo '</pre>';
	}
}

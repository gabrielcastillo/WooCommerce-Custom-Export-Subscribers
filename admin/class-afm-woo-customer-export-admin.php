<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://gabrielcastillo.net
 * @since      1.0.0
 *
 * @package    Afm_Woo_Customer_Export
 * @subpackage Afm_Woo_Customer_Export/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Afm_Woo_Customer_Export
 * @subpackage Afm_Woo_Customer_Export/admin
 * @author     Gabriel Castillo <gabriel@gabrielcastillo.net>
 */
class Afm_Woo_Customer_Export_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;


	protected $db;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		global $wpdb;

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->db = $wpdb;
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
		 * defined in Afm_Woo_Customer_Export_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Afm_Woo_Customer_Export_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/afm-woo-customer-export-admin.css', array(), $this->version, 'all' );

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
		 * defined in Afm_Woo_Customer_Export_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Afm_Woo_Customer_Export_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/afm-woo-customer-export-admin.js', array( 'jquery' ), $this->version, false );

	}

	/**
	 * Register Admin Page
	 * @return void
	 */
	public function ncgasa_register_admin_page()
	{

		add_menu_page( __( 'AFM WOO CUSTOMER EXPORT', 'afm-woo-customer-export' ), __( 'Export Sub List', 'afm-woo-customer-export' ), 'manage_options', 'afm-woo-customer-export', array( $this, 'ncgasa_admin_page_display' ), plugins_url( 'afm-woo-customer-export/images/icon.png' ), 20 );
	}

	/**
	 * Displa Admin Page
	 * @return void
	 */
	public function ncgasa_admin_page_display()
	{

		if ( ! current_user_can( 'edit_users' ) ) {
			echo 'Not allowed!';
		}

		$product_id = "";
		$from_date 	= null;
		$to_date 	= null;

		if ( isset( $_POST ) ) {

			if ( ! isset( $_POST['start_date'] ) || empty( $_POST['start_date'] ) ) {
				//echo $this->ncgasa_set_admin_notice( 'Please enter start date', 'error' );
			} else {
				$from_date = sanitize_key( $_POST['start_date'] );
			}

			if ( ! isset( $_POST['end_date'] ) || empty( $_POST['end_date'] ) ) {
				//echo $this->ncgasa_set_admin_notice( 'Please enter end date', 'error' );
			} else {
				$to_date = sanitize_key( $_POST['end_date'] );
			}
		}

		$product_list = $this->ncgasa_get_product_list();
		
		$form_nonce = wp_create_nonce( 'ncgasa_admin_nonce' );
		?>
		<div class="wrap">
			<h3>Export Subscriber List</h3>
			
			<div class="ncgasa-app">
				<div class="ncgasa-sidebar">
					<form action="<?php echo $_SERVER['PHP_SELF']; ?>?page=afm-woo-customer-export" method="POST" id="">
						<input type="hidden" name="ncgasa_admin_nonce" value="<?php echo $form_nonce; ?>" />
						<!-- <div>
							<label for="productID">Select Product</label>
							<select id="productID" name="productID">
							<?php foreach ( $product_list as $product ) :?>
								<option value="<?php echo $product['product_id']; ?>" <?php echo ( isset( $product_id ) ) ? 'selected="selected"' : ''; ?>><?php echo $product['name']; ?></option>
							<?php endforeach; ?>
							</select>
						</div> -->
						<div class="input-group">
							<label for="start_date">From Date</label>
							<input type="date" id="start_date" name="start_date" value="<?php echo ( isset( $from_date ) ) ? $from_date : null; ?>" />
						</div>
						<div class="input-group">
							<label for="end_date">To Date</label>
							<input type="date" id="end_date" name="end_date" value="<?php echo ( isset( $to_date ) ) ? $to_date : null; ?>" />
						</div>
						<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="submit" />
					</form>
					<?php if ( ! empty($from_date) ) :?>
					<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="POST">
						<input type="hidden" name="action" value="ncgasa_export_csv_file" />
						<input type="hidden" name="ncgasa_admin_nonce" value="<?php echo $form_nonce; ?>" />
						<input type="hidden" name="productID" value="<?php echo $product_id; ?>" />
						<input type="hidden" name="start_date" value="<?php echo $from_date; ?>" />
						<input type="hidden" name="end_date" value="<?php echo $to_date; ?>" />
						<input type="submit" id="submit" class="button button-primary" name="submit" value="Download CSV File" />
					</form>
					<?php endif; ?>
				</div>
				<div class="ncgasa-content">
					<?php 

					$subscribers = $this->ncgasa_get_subscriber_list( $product_id, $from_date, $to_date );
					
					if ( !empty( $subscribers ) ) {
						echo "<style>
						.subscription_list th, .subscription_list td{border:solid 1px #666; padding:2px 5px;}
						.subscription_list th{font-weight:bold}
						.subscription_list td{text-align:center}
						</style>";

						echo "<table class='subscription_list_table subscription_list'>
						<thead>
							<tr>
								<th>" . __( 'Order ID', 'afm-woo-customer-export' ) . "</th>
								<th>" . __( 'Date', 'afm-woo-customer-export' ) . "</th>
								<th>" . __( 'Customer Email', 'afm-woo-customer-export' ) . "</th>
								<th>" . __( 'Customer Name', 'afm-woo-customer-export' ) . "</th>
								<th>" . __( 'Customer Phone', 'afm-woo-customer-export' ) . "</th>
								<th>" . __( 'Subscription Type', 'afm-woo-customer-export' ) . "</th>
							</tr>
						</thead><tbody>";


						foreach ( $subscribers as $idx => $subscriber ) {
							//echo '<li>' . print_r($subscriber, true) . '</li>';

							echo "</tr>
								<td>$subscriber->order_id</td>
								<td>$subscriber->post_date</td>
								<td>$subscriber->billing_email</td>
								<td>$subscriber->_billing_first_name $subscriber->_billing_last_name</td>
								<td>$subscriber->billing_phone</td>
								<td>$subscriber->order_items</td>
							</tr>";
						}
						echo '<tbody></table>';
					}
				 ?>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Get id, name of products
	 * @return array product list (product_id, name)
	 */
	public function ncgasa_get_product_list()
	{
		$products_arr = array();


	    $products_subscr = get_posts( array(
	        'numberposts' => -1,
	        'post_status' => 'publish',
	        'post_type'   => array( 'product', 'product_variation' ),
	        'meta_key' => '_subscription_price',
	    ) );

	    foreach( $products_subscr as $idx => $prod_subs ) {
	        $products_arr[$idx]['product_id'] = $prod_subs->ID;
	        $products_arr[$idx]['name'] = $prod_subs->post_title;
	    }

	    return $products_arr;
	}

	/**
	 * @param  string|integer $product_id
	 * @param  string $from_date
	 * @param  string $to_date
	 * @return array return array of objects from query.
	 */
	public function ncgasa_get_subscriber_list( $product_id = null, $from_date, $to_date )
	{
		$sql = "SELECT
			        p.ID as order_id,
			        p.post_date,
			        max( CASE WHEN pm.meta_key = '_billing_phone' AND p.ID = pm.post_id THEN pm.meta_value END ) as billing_phone,
			        max( CASE WHEN pm.meta_key = '_billing_email' and p.ID = pm.post_id THEN pm.meta_value END ) as billing_email,
			        max( CASE WHEN pm.meta_key = '_billing_first_name' and p.ID = pm.post_id THEN pm.meta_value END ) as _billing_first_name,
			        max( CASE WHEN pm.meta_key = '_billing_last_name' and p.ID = pm.post_id THEN pm.meta_value END ) as _billing_last_name,
			        max( CASE WHEN pm.meta_key = '_billing_address_1' and p.ID = pm.post_id THEN pm.meta_value END ) as _billing_address_1,
			        max( CASE WHEN pm.meta_key = '_billing_address_2' and p.ID = pm.post_id THEN pm.meta_value END ) as _billing_address_2,
			        max( CASE WHEN pm.meta_key = '_billing_city' and p.ID = pm.post_id THEN pm.meta_value END ) as _billing_city,
			        max( CASE WHEN pm.meta_key = '_billing_state' and p.ID = pm.post_id THEN pm.meta_value END ) as _billing_state,
			        max( CASE WHEN pm.meta_key = '_billing_postcode' and p.ID = pm.post_id THEN pm.meta_value END ) as _billing_postcode,
			        max( CASE WHEN pm.meta_key = '_shipping_first_name' and p.ID = pm.post_id THEN pm.meta_value END ) as _shipping_first_name,
			        max( CASE WHEN pm.meta_key = '_shipping_last_name' and p.ID = pm.post_id THEN pm.meta_value END ) as _shipping_last_name,
			        max( CASE WHEN pm.meta_key = '_shipping_address_1' and p.ID = pm.post_id THEN pm.meta_value END ) as _shipping_address_1,
			        max( CASE WHEN pm.meta_key = '_shipping_address_2' and p.ID = pm.post_id THEN pm.meta_value END ) as _shipping_address_2,
			        max( CASE WHEN pm.meta_key = '_shipping_city' and p.ID = pm.post_id THEN pm.meta_value END ) as _shipping_city,
			        max( CASE WHEN pm.meta_key = '_shipping_state' and p.ID = pm.post_id THEN pm.meta_value END ) as _shipping_state,
			        max( CASE WHEN pm.meta_key = '_shipping_postcode' and p.ID = pm.post_id THEN pm.meta_value END ) as _shipping_postcode,
			        max( CASE WHEN pm.meta_key = '_order_total' and p.ID = pm.post_id THEN pm.meta_value END ) as order_total,
			        max( CASE WHEN pm.meta_key = '_order_tax' and p.ID = pm.post_id THEN pm.meta_value END ) as order_tax,
			        max( CASE WHEN pm.meta_key = '_paid_date' and p.ID = pm.post_id THEN pm.meta_value END ) as paid_date,
			        ( select group_concat( order_item_name separator '|' ) from wp_woocommerce_order_items where order_id = p.ID ) as order_items
			    FROM
			        wp_posts p 
			        join wp_postmeta pm on p.ID = pm.post_id
			        join wp_woocommerce_order_items oi on p.ID = oi.order_id
			    WHERE
			        post_type = 'shop_order' and
			        post_date BETWEEN %s AND %s AND
			        post_status = 'wc-completed'
			    group by
			        p.ID";

		$query = $this->db->get_results($this->db->prepare($sql, array($from_date, $to_date)));
		return $query;
	}

	/**
	 * [ncgasa_set_admin_notice description]
	 * @param  string $message
	 * @param  string $type    info, error, warning, success
	 * @return string
	 */
	public function ncgasa_set_admin_notice( string $message = "", string $type = "" )
	{
		if ( empty( $message ) || empty( $type ) ) {
			return;
		}

		$html = "";

		$html .= '<div class="notice notice-' . esc_attr( $type ) . ' is-dismissible">';
		$html .= wpautop( $message );
		$html .= '</div>';

		return $html;
	}

	/**
	 * Custom redirect with parameters.
	 * @param  array  $query_params GET params sent back to the request uri
	 * @param  string $status       HTTP status code, default 302 temporary
	 * @return void
	 */
	public function ncgasa_redirect( $query_params = array(), string $status = '302')
	{

		wp_safe_redirect( site_url('/wp-admin/admin.php?page=afm-woo-customer-export&'. http_build_query( $query_params ) ), 302 );
	}

	/**
	 * Handle form request, clean sanitize data, get subscriber list
	 * @return void
	 */
	public function ncgasa_export_csv_file()
	{

		if ( isset( $_POST['ncgasa_admin_nonce'] ) && wp_verify_nonce( $_POST['ncgasa_admin_nonce'],'ncgasa_admin_nonce' ) ) {
			
			$product_id = sanitize_key( $_POST['productID'] );
			$from_date 	= sanitize_key( $_POST['start_date'] );
			$to_date 	= sanitize_key( $_POST['end_date'] );

			$subscribers = $this->ncgasa_get_subscriber_list( $product_id, $from_date, $to_date );

			$this->ncgasa_push_csv_download($subscribers);
		}
		return false;
	}

	/**
	 * ncgasa_push_csv_download
	 *
	 * create csv file, and push attachment header with csv file.
	 * @param  array $data Subscriber data
	 * @return file       CSV file attachment
	 */
	public function ncgasa_push_csv_download( $data )
	{
		ob_start();
		header('Content-Type: text/csv; charset=utf-8');
		header('Content-Disposition: attachment; filename=ncgasa-subscriber-list.csv');

		$header_args = array( 'ID', 'Date', 'Name', 'Email', 'Phone', 'Membership' );

		ob_end_clean();

		$output = fopen( 'php://output', 'w' );

		fputcsv( $output, $header_args);

		$payload = [];
		foreach ( $data as $key => $value) {
			$payload[$key]['order_id'] = $value->order_id;
			$payload[$key]['post_date'] = $value->post_date;
			$payload[$key]['name'] = $value->_billing_first_name . ' ' . $value->_billing_last_name;
			$payload[$key]['email'] = $value->billing_email;
			$payload[$key]['phone'] = $value->billing_phone;
			$payload[$key]['membership'] = $value->order_items;
		}

		foreach( $payload as $data_item ) {
			fputcsv( $output, $data_item );
		}

		fclose( $output );
	}

	/**
	 * DEBUGGER
	 *
	 * dump value in pre tags, end script.
	 * @param  mixed $value
	 * @return mixed
	 */
	public function dd($value)
	{
		echo '<pre>' . print_r($value, true) . '</pre>';
		exit;
	}

}

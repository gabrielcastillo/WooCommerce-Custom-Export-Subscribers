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
 *
 * @resource 'https://stackoverflow.com/questions/36729701/programmatically-creating-new-order-in-woocommerce';
 *
 * @package    Afm_Woo_Customer_Export
 * @subpackage Afm_Woo_Customer_Export/admin
 * @author     Gabriel Castillo <gabriel@gabrielcastillo.net>
 */
class Afm_Woo_Customer_Export_Admin
{
    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private string $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private string $version;

    private object $db;

    private object $errors;

    private string $admin_url_export_page;

    private string $admin_url_import_page;

    private string $form_nonce;

    private array $invalid_subscriber_data;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string    $plugin_name       The name of this plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct( string $plugin_name, string $version )
    {
        global $wpdb;

        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->db = $wpdb;
        $this->errors = new WP_Error();
        $this->admin_url_export_page = admin_url(
            "admin.php?page=" . $this->plugin_name
        );
        $this->admin_url_import_page = admin_url(
            "admin.php?page=" . $this->plugin_name . "-import"
        );
        $this->invalid_subscriber_data = [];
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    final public function enqueue_styles()
    {
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

        wp_enqueue_style(
            $this->plugin_name,
            plugin_dir_url(__FILE__) . "css/afm-woo-customer-export-admin.css",
            [],
            $this->version,
            "all"
        );
        return;
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    final public function enqueue_scripts()
    {
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

        wp_enqueue_script(
            $this->plugin_name,
            plugin_dir_url(__FILE__) . "js/afm-woo-customer-export-admin.js",
            ["jquery"],
            $this->version,
            false
        );

        return;
    }

    /**
     * Register Admin Page
     * @return void
     */
    final public function ncgasa_register_admin_page(): void
    {
        add_menu_page(
            __("AFM WOO CUSTOMER EXPORT", $this->plugin_name),
            __("Export Sub List", "afm-woo-customer-export"),
            "manage_options",
            $this->plugin_name,
            [$this, "ncgasa_admin_page_display"],
            plugins_url("afm-woo-customer-export/images/icon.png"),
            20
        );
        add_submenu_page(
            $this->plugin_name,
            __("Import Subscribers", $this->plugin_name),
            __("Import Subscribers", $this->plugin_name),
            "manage_options",
            $this->plugin_name . "-import",
            [$this, "ncgasa_admin_sub_page_display"]
        );

        // Needs to be created after admin page registers.
        $this->form_nonce = wp_create_nonce("ncgasa_admin_nonce");
        return;
    }

    /**
     * Displa Admin Page
     * @return void
     */
    final public function ncgasa_admin_page_display(): void
    {
        if (!current_user_can("edit_users")) {
            echo "Not allowed!";
        }

        $product_id = "";
        $from_date = null;
        $to_date = null;

        if (isset($_POST)) {
            if ( ! isset($_POST["start_date"] ) || empty( $_POST["start_date"] ) ) {
                //echo $this->ncgasa_set_admin_notice( 'Please enter start date', 'error' );
            } else {
                $from_date = sanitize_key($_POST["start_date"]);
            }

            if ( ! isset( $_POST["end_date"] ) || empty( $_POST["end_date"] ) ) {
                //echo $this->ncgasa_set_admin_notice( 'Please enter end date', 'error' );
            } else {
                $to_date = sanitize_key( $_POST["end_date"] );
            }
        }

        $product_list = $this->ncgasa_get_product_list();

        $form_nonce = wp_create_nonce("ncgasa_admin_nonce");
        ?>
        <div class="wrap">
            <h3>Export Subscriber List</h3>

            <div class="ncgasa-app">
                <div class="ncgasa-sidebar">
                    <form action="<?php echo $_SERVER["PHP_SELF"]; ?>?page=afm-woo-customer-export" method="POST" id="">
                        <input type="hidden" name="ncgasa_admin_nonce" value="<?php echo $form_nonce; ?>" />
                        <!-- <div>
							<label for="productID">Select Product</label>
							<select id="productID" name="productID">
							<?php foreach ($product_list as $product): ?>
								<option value="<?php echo $product["product_id"]; ?>" <?php echo isset( $product_id ) ? 'selected="selected"': ""; ?>><?php echo $product["name"]; ?></option>
							<?php endforeach; ?>
							</select>
						</div> -->
                        <div class="input-group">
                            <label for="start_date">From Date</label>
                            <input type="date" id="start_date" name="start_date" value="<?php echo isset( $from_date ) ? $from_date : null; ?>" />
                        </div>
                        <div class="input-group">
                            <label for="end_date">To Date</label>
                            <input type="date" id="end_date" name="end_date" value="<?php echo isset( $to_date ) ? $to_date : null; ?>" />
                        </div>
                        <p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="submit" />
                    </form>
					<?php if (!empty($from_date)): ?>
                        <form action="<?php echo esc_url( admin_url("admin-post.php") ); ?>" method="POST">
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
                        $subscribers = $this->ncgasa_get_subscriber_list( $product_id, $from_date );

                        if (!empty($subscribers)) {
         echo "<style>
						.subscription_list th, .subscription_list td{border:solid 1px #666; padding:2px 5px;}
						.subscription_list th{font-weight:bold}
						.subscription_list td{text-align:center}
						</style>";

         echo "<table class='subscription_list_table subscription_list'>
						<thead>
							<tr>
								<th>" .
             __("Order ID", "afm-woo-customer-export") .
             "</th>
								<th>" .
             __("Date", "afm-woo-customer-export") .
             "</th>
								<th>" .
             __("Customer Email", "afm-woo-customer-export") .
             "</th>
								<th>" .
             __("Customer Name", "afm-woo-customer-export") .
             "</th>
								<th>" .
             __("Customer Phone", "afm-woo-customer-export") .
             "</th>
								<th>" .
             __("Subscription Type", "afm-woo-customer-export") .
             "</th>
							</tr>
						</thead><tbody>";

         foreach ($subscribers as $idx => $subscriber) {
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
         echo "<tbody></table>";
     }


                        ?>
                </div>
            </div>
        </div>
		<?php
    }

    /**
     * Import Sub Page
     * @return void
     */
    final public function ncgasa_admin_sub_page_display(): void
    {
        if (!current_user_can("edit_users")) {
            $this->ncgasa_send_admin_errors($this->admin_url_import_page, [
                "Permissions" => "Access denied!",
            ]);
        }
        $failed_imports = get_transient(
            "ncgasa_failed_import_list_" . get_current_user_id()
        ); // Create CSV export file.
        delete_transient("ncgasa_failed_import_list_" . get_current_user_id());
        $admin_notice = get_transient("_notice_" . get_current_user_id());
        delete_transient("_notice_" . get_current_user_id());
        ?>
        <h3>Import Subscribers</h3>
        <div class="wrap">
            <?php $this->display_admin_errors(); ?>
            <?php $this->display_admin_notices(); ?>
            <form action="<?php echo esc_url(
                admin_url("admin-post.php")
            ); ?>" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="nonce" value="<?php echo $this->form_nonce; ?>" />
                <input type="hidden" name="action" value="ncgasa_import_subscribers" />
                <div class="input-group">
                    <label for="file">Import CSV File</label>
                    <input type="file" id="file" name="file" />
                </div>
                <p><input type="submit" id="submit" name="submit" value="Upload" class="button button-primary"/></p>
            </form>
        </div>
        <?php
        if ($admin_notice) {
            echo "<h3>Admin Notice</h3>";
            foreach ($admin_notice as $notice) {
                if (isset($notice->errors)) {
                    echo "<pre>" . print_r($notice->errors, true) . "</pre>";
                } else {
                    echo "<pre>" . print_r($notice, true) . "</pre>";
                }
            }
        }

        if ($failed_imports) {
            echo "<h3>Failed Imports</h3>";
            echo "<ul>";
            foreach (unserialize($failed_imports) as $key => $import) {
                if (is_array($import)) {
                    echo "<div class='error'>";
                    foreach ($import as $idx => $value) {
                        $key = $value["key"];
                        echo "<p>Please fix errors: missing column value - $key";
                        //echo "<pre>" . print_r($value["data"], true) . "</pre>";
                        echo "<p>" . implode(", ", $value["data"]) . "</p>";
                        echo "<hr />";
                    }
                    echo "</div>";
                }
            }
            echo "</ul>";
        }

        return;
    }

    /**
     * Import Subscribers Handler.
     *
     * handle file upload, move temp file to upload dir, and create
     * array from csv data.
     *
     * @return void
     * @throws Exception
     */
    final public function ncgasa_import_subscribers_handler(): void
    {
        if (!current_user_can("edit_users")) {
            $this->ncgasa_send_admin_errors($this->admin_url_import_page, [
                "permissions" => "Access Denied.",
            ]);
        }

        // Verify nonce, check file is posted.
        if (
            isset($_POST["nonce"]) &&
            wp_verify_nonce($_POST["nonce"], "ncgasa_admin_nonce") &&
            isset($_FILES["file"])
        ) {
            if (!file_exists($_FILES["file"]["tmp_name"])) {
                $this->errors->add("file", "file not found");
                $this->ncgasa_send_admin_errors(
                    $this->admin_url_import_page,
                    $this->errors->errors
                );
            }

            $file = $_FILES["file"];

            // Check file is CSV
            if ($file["type"] !== "text/csv") {
                $this->errors->add(
                    "fileType",
                    "invalid file type, required CSV UTF-8"
                );
                $this->ncgasa_send_admin_errors(
                    $this->admin_url_import_page,
                    $this->errors->errors
                );
            }

            // Send complete error object.?
            if (count($this->errors->get_error_codes())) {
                $this->ncgasa_send_admin_errors(
                    $this->admin_url_import_page,
                    $this->errors
                );
            }

            // Test for _POST action is expected and File Type
            $over_rides = [
                "test_form" => false,
                "test_type" => ["text/csv"],
            ];

            // Upload file to uploads dir
            $move_file = wp_handle_upload($file, $over_rides);

            //Check if any error return from upload handler.
            if (isset($move_file["error"])) {
                $this->errors->add("upload", $move_file["error"]);
                $this->ncgasa_send_admin_errors(
                    $this->admin_url_import_page,
                    $this->errors->errors
                );
            }

            // Check if upload handler return error.
            if ($move_file && !isset($move_file["error"])) {
                $file = $move_file["file"];

                // Parse csv file and create new subscriber record
                $subscribers = $this->ncgasa_convert_csv_file($file);

                // Create Subscriber
                $subscriber_list = [];

                // Create new subscription for each subscriber.
                foreach ($subscribers as $idx => $subscriber) {
                    $subscription = $this->ncgasa_create_new_subscription(
                        $subscriber
                    );
                    if (!is_wp_error($subscription)) {
                        $subscriber_list[$idx] = $subscription;
                    }
                }

                $response = [
                    "status" => true,
                    "arr_count" => count($subscribers),
                    "new_subscribers" => $subscriber_list,
                    "raw_subscribers" => $subscribers,
                ];

                $this->ncgasa_send_admin_notice(
                    $this->admin_url_import_page,
                    $response
                );
            } else {
                $this->ncgasa_send_admin_errors($this->admin_url_import_page, [
                    "file" => $move_file["error"],
                ]);
            }
        }
    }

    /**
     * @param array $subscriber
     *
     * @return object
     * @throws Exception
     */
    final public function ncgasa_create_new_subscription( array $subscriber ): object {
        // Check user permissions
        if (!current_user_can("edit_users")) {
            $this->ncgasa_send_admin_errors($this->admin_url_import_page, [
                "Permission" => "Permission: not allowed.",
            ]);
        }

        /**
         * Get customer ID from email, or create new customer and return ID
         */
        $customer_id = ($user = get_user_by("email", $subscriber["email"]))
            ? $user->ID
            : false;

        if (!$customer_id) {
            $customer_id = wc_create_new_customer(
                $subscriber["email"],
                $subscriber["email"],
                wp_generate_password()
            );

            if (is_wp_error($customer_id)) {
                return new WP_Error(
                    "create-customer-failure",
                    __("Can not create new customer.", $this->plugin_name)
                );
            }
        }

        // Create new order.
        $order = wc_create_order(["customer_id" => $customer_id]);

        if (is_wp_error($order)) {
            return new WP_Error(
                "create-customer-order-failed",
                __("Failed to create customer order.", $this->plugin_name)
            );
        }

        // Get product ID from membership column.
        $product_list = $this->ncgasa_get_product_list();

        // Set product ID
        $product_id = 0;
        foreach ($product_list as $idx => $product) {
            $product_name = strtolower($product["name"]);

            if ("yearly membership - guide membership" == $product_name) {
                $product_id = $product["product_id"];
            }
        }

        $product = wc_get_product($product_id);

        $customer = get_user_by("ID", $customer_id);

        //$variation_id = $this->get_product_variation_by_product_id($product->get_id());

        $address = [
            "first_name" => sanitize_text_field($subscriber["first_name"]),
            "last_name" => sanitize_text_field($subscriber["last_name"]),
            "email" => sanitize_email($subscriber["email"]),
            "phone" => sanitize_text_field($subscriber["phone"]),
            "address_1" => sanitize_text_field($subscriber["street"]),
            "address_2" => "",
            "city" => sanitize_text_field($subscriber["city"]),
            "state" => sanitize_text_field($subscriber["state"]),
            "postcode" => sanitize_text_field($subscriber["zip"]),
            "country" => "",
        ];

        $order->set_address($address, "billing");
        $order->set_address($address, "shipping");
        $order->add_product($product, 1);

        $subscription = wcs_create_subscription([
            "order_id" => $order->get_id(),
            "status" => "active",
            "billing_period" => WC_Subscriptions_Product::get_period($product),
            "billing_interval" => WC_Subscriptions_Product::get_interval(
                $product
            ),
            "customer_id" => $customer_id,
        ]);

        if (is_wp_error($subscription)) {
            $this->errors->add("subscription", "failed to create");
            return $this->errors;
        }

        $payment_date = $this->get_date_from_string(
            sanitize_text_field($subscriber["payment_date"])
        );

        $start_date = gmdate("Y-m-d H:i:s", strtotime($payment_date));

        $subscription->set_address($address, "billing");
        $subscription->set_address($address, "shipping");
        $subscription->add_product($product, 1);

        $dates = [
            "trial_end" => WC_Subscriptions_Product::get_trial_expiration_date(
                $product,
                $start_date
            ),
            "next_payment" => WC_Subscriptions_Product::get_first_renewal_payment_date(
                $product,
                $start_date
            ),
            "end" => WC_Subscriptions_Product::get_expiration_date(
                $product,
                $start_date
            ),
        ];

        $subscription->update_dates($dates);
        $subscription->calculate_totals();

        $note = !empty($note) ? $note : __("Import order and subscription ");

        $order->update_status("completed", $note, true);

        $subscription->update_status("active", $note, true);

        return $subscription;
    }

    /**
     * Return array of subscribers, converted from csv utf-8
     *
     * @param $file
     *
     * @return array|bool
     */
    private function ncgasa_convert_csv_file(mixed $file): array|bool
    {
        $subscriber_keys = [];
        $subscribers = [];
        $counter = 0;
        $failed_import_list = [];

        if (filetype($file) != "file") {
            $this->errors->add("file", "Invalid File Type");
        }

        $fh = fopen($file, "r");

        if (!$fh) {
            $this->errors->add("file", "Can not open file.");
        }

        if (count($this->errors->get_error_codes())) {
            $params = [
                "url" =>
                    "wp-admin/admin.php?page=" . $this->plugin_name . "-import",
                "errors" => true,
            ];

            $this->ncgasa_redirect($params);
        } else {
            $required_params = [
                "first_name",
                "last_name",
                "email",
                "payment_date",
                "membership",
            ];

            while (($data = fgetcsv($fh, 1000, ",")) !== false) {
                // Create list of keys used for building subscribers array. This should be the first row in the CSV file for headings.
                if ($counter === 0) {
                    foreach ($data as $idx => $value) {
                        // Convert invalid characters.
                        $value = mb_convert_encoding(
                            strtolower(str_replace(" ", "_", trim($value))),
                            "ASCII",
                            "auto"
                        );

                        // Use preg_replace to remove invalid char Because of converting charter,
                        // output from some csv file with produce a question mark.
                        $subscriber_keys[$idx] = preg_replace(
                            "/[?]/",
                            "",
                            $value
                        );
                    }
                } else {
                    if (empty($data[$counter])) {
                        continue;
                    }

                    foreach ($subscriber_keys as $idx => $index_key) {
                        if ($index_key == "first_name") {
                            if ($data[$idx] == "") {
                                $failed_import_list[$counter][
                                    "key"
                                ] = $index_key;
                                $failed_import_list[$counter]["data"] = $data;
                            }
                        }
                        if ($index_key == "last_name") {
                            if ($data[$idx] == "") {
                                $failed_import_list[$counter][
                                    "key"
                                ] = $index_key;
                                $failed_import_list[$counter]["data"] = $data;
                            }
                        }

                        if ($index_key == "email") {
                            if ($data[$idx] == "") {
                                $failed_import_list[$counter][
                                    "key"
                                ] = $index_key;
                                $failed_import_list[$counter]["data"] = $data;
                            }
                        }

                        if ($index_key == "payment_date") {
                            if ($data[$idx] == "") {
                                $failed_import_list[$counter][
                                    "key"
                                ] = $index_key;
                                $failed_import_list[$counter]["data"] = $data;
                            }
                        }

                        if ($index_key == "membership") {
                            if ($data[$idx] == "") {
                                $failed_import_list[$counter][
                                    "key"
                                ] = $index_key;
                                $failed_import_list[$counter]["data"] = $data;
                            }
                        }

                        $subscribers[$counter][$index_key] = !empty($data[$idx])
                            ? $data[$idx]
                            : 0;
                    }
                }

                $counter++;
            }
            fclose($fh);
        }

        if (count($failed_import_list) > 0) {
            $response = [
                "fail_count" => count($failed_import_list),
                "data" => $failed_import_list,
            ];
            // Set record of failed import list for later use.
            set_transient(
                "ncgasa_failed_import_list_" . get_current_user_id(),
                serialize($response)
            );

            return wp_redirect(
                admin_url("admin.php?page=" . $this->plugin_name . "-import")
            );
        }

        return $subscribers;
    }

    /**
     * @param string $redirect_url
     * @param array $validation_errors
     *
     * @return boolean
     */
    final public function ncgasa_send_admin_errors( string $redirect_url, mixed $validation_errors ): bool {
        if ($redirect_url == "") {
            $redirect_url = admin_url("admin.php?page=" . $this->plugin_name);
        }

        set_transient(
            "validation_errors_" . get_current_user_id(),
            $validation_errors
        );
        return wp_redirect($redirect_url, 302);
    }

    final public function ncgasa_send_admin_notice( string $redirect_url, mixed $notice ): bool {
        if ($redirect_url == "") {
            $redirect_url = admin_url("admin.php?page=" . $this->plugin_name);
        }

        set_transient("_notice_" . get_current_user_id(), $notice);
        return wp_redirect($redirect_url, 302);
    }

    /**
     * display admin errors with transient record.
     * Delete record after display.
     *
     * @return void
     */
    final public function display_admin_errors(): void
    {
        $errors = get_transient("validation_errors_" . get_current_user_id());

        if ($errors !== false && empty($error)) {
            echo '<div class="errors">Please fix the following errors:';
            echo "<ul>";
            if (is_array($errors)) {
                foreach ($errors as $idx => $error) {
                    if (is_array($error)) {
                        echo "<li>" . implode("</li><li>", $error) . "</li>";
                    } else {
                        echo "<li>" . $error . "</li>";
                    }
                }
            } else {
                echo "<li>" . print_r($errors->errors, true) . "</li>";
            }
            echo "</ul>";
            echo "</div>";
        }
        delete_transient("validation_errors_" . get_current_user_id());
        return;
    }

    /**
     * display admin notices with transient record
     * @return void
     */
    final public function display_admin_notices(): void
    {
        $notice = get_transient("_notice_" . get_current_user_id());

        if ($notice !== false) {
            echo "<pre>" . print_r($notice, true) . "</pre>";
        }
        delete_transient("_notice_" . get_current_user_id());
        return;
    }

    /**
     * Get array of all variations :id, :name, :variation
     *
     * @param $product_id
     *
     * @return array
     */
    private function ncgasa_get_product_variations(int $product_id): array
    {
        $product = new WC_Product_Variable($product_id); // Variable product ID. // Get Available variations?

        $get_variations =
            count($product->get_children()) <=
            apply_filters("woocommerce_ajax_variation_threshold", 30, $product);
        $available_variations = $get_variations
            ? $product->get_available_variations()
            : false;

        $variations_list = [];

        foreach ($available_variations as $idx => $variation) {
            $variations_list[$idx]["variation_id"] = $variation["variation_id"];
            $variations_list[$idx]["variation_label"] =
                $variation["attributes"]["attribute_membership"];
            $variations_list[$idx]["variation"] = $variation;
        }

        return $variations_list;
    }

    /**
     * Get date from string
     *
     * Check if is valid timestamp. Else split into array
     * Use array items to crate new date string
     * If last item in array is 2 chars, insert 20 to make a full year.
     *
     * @param string $date
     *
     * @return string
     */
    final public function get_date_from_string(string $date): string
    {
        $start_date = "";
        if (!$this->is_valid_date($date)) {
            $payload = explode("/", $date);

            if (count($payload) != 3) {
                $this->errors->add("payment_date", "Invalid date");
                $this->ncgasa_send_admin_errors(
                    $this->admin_url_import_page,
                    $this->errors->errors
                );
            } else {
                $day = $payload[0];
                $month = $payload[1];
                $year = $payload[2];

                if (strlen($year) == 2) {
                    $year = "20" . $payload[2];
                }

                $start_date = date(
                    "Y-m-d",
                    strtotime($day . "-" . $month . "-" . $year)
                );
            }
        } else {
            $start_date = date("Y-m-d", strtotime($date));
        }
        return $start_date;
    }

    /**
     * Check if date string is valid date.
     *
     * @param string $date
     *
     * @return bool
     */
    final public function is_valid_date(string $date): bool
    {
        return (bool) strtotime($date);
    }

    /**
     * Get product variation id by product id.
     *
     * Compare subscriber membership column value to variation label.
     *
     * @param int $product_id
     * @param array $subscriber
     *
     * @return mixed
     */
    final public function ncgasa_get_product_variation_by_product_id( int|string $product_id, array $subscriber ): mixed {
        $variation_id = "";
        $product_variation_list = $this->ncgasa_get_product_variations(
            $product_id
        );

        if (!isset($subscriber["membership"])) {
            $membership = "regular";
        } else {
            $membership = $subscriber["membership"];
        }

        $this->dd($membership);

        switch ($membership) {
            case "youth":
                foreach ($product_variation_list as $variation) {
                    if (
                        strtolower($variation["variation_label"]) ==
                        "Youth Membership"
                    ) {
                        $variation_id = $variation["variation_id"];
                    }
                }
                break;
            case "regular":
                foreach ($product_variation_list as $variation) {
                    if (
                        strtolower($variation["variation_label"]) ==
                        "regular membership"
                    ) {
                        $variation_id = $variation["variation_id"];
                    }
                }
                break;
            case "guide":
                foreach ($product_variation_list as $variation) {
                    if (
                        strtolower($variation["variation_label"]) ==
                        "guide membership"
                    ) {
                        $variation_id = $variation["variation_id"];
                    }
                }
                break;
            case "business":
                foreach ($product_variation_list as $variation) {
                    if (
                        strtolower($variation["variation_label"]) ==
                        "business membership"
                    ) {
                        $variation_id = $variation["variation_id"];
                    }
                }
                break;

            case "sponsor":
                foreach ($product_variation_list as $variation) {
                    if (
                        strtolower($variation["variation_label"]) ==
                        "business sponsor"
                    ) {
                        $variation_id = $variation["variation_id"];
                    }
                }
                break;
        }

        if ($variation_id == "") {
            $this->errors->add("subscriber", print_r($subscriber));
            $this->errors->add("variation", "Product variation ID not found");
            $this->ncgasa_send_admin_errors(
                $this->admin_url_import_page,
                $this->errors->errors
            );
        }

        return $variation_id;
    }

    /**
     * Get subscriber list from database by date
     * @param string $from_date
     * @param string $to_date
     *
     * @return array|bool
     */
    final public function ncgasa_get_subscriber_list( string $from_date, string $to_date ): array|bool {
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

        return $this->db->get_results(
            $this->db->prepare($sql, [
                date("Y-m-d 00:00:01", strtotime($from_date)),
                date("Y-m-d 00:00:01", strtotime($to_date)),
            ])
        );
    }

    /**
     * Get id, name of products
     * @return array product list (product_id, name)
     */
    private function ncgasa_get_product_list(): array
    {
        $products_arr = [];

        $products_subscr = get_posts([
            "numberposts" => -1,
            "post_status" => "publish",
            "post_type" => ["product", "product_variation"],
            "meta_key" => "_subscription_price",
        ]);

        foreach ($products_subscr as $idx => $prod_subs) {
            $products_arr[$idx]["product_id"] = $prod_subs->ID;
            $products_arr[$idx]["name"] = $prod_subs->post_title;
        }

        return $products_arr;
    }

	/**
	 * [ncgasa_set_admin_notice description]
	 *
	 * @param $message
	 * @param string|null $type info, error, warning, success
	 *
	 * @return string
	 */
    private function ncgasa_set_admin_notice( string $message = 'Empty notice', string $type = 'info' ): string {

	    $html = '<div class="notice notice-' . esc_attr( $type ) . ' is-dismissible">';
        $html .= wpautop( $message );
        $html .= "</div>";

        return $html;
    }

    /**
     * Custom redirect with parameters.
     *
     * @param array  $query_params GET params sent back to the request uri
     * @param  string $status       HTTP status code, default 302 temporary
     *
     * @return void
     */
    private function ncgasa_redirect( array $query_params = [], string $status = "302" ): void {
        if (in_array("url", array_keys($query_params))) {
            $redirect_url = $query_params["url"];
            unset($query_params["url"]);
        } else {
            $redirect_url = "/wp-admin/admin.php?page=afm-woo-customer-export&";
        }

        if (substr($redirect_url, -1, strlen($redirect_url)) == "&") {
            $redirect_url = substr($redirect_url, 0, strlen($redirect_url) - 1);
        }

        wp_safe_redirect(
            site_url($redirect_url . "&" . http_build_query($query_params)),
            $status
        );
    }

    /**
     * Handle form request, clean sanitize data, get subscriber list
     * @return void
     */
    private function ncgasa_export_csv_file()
    {
        if (
            isset($_POST["ncgasa_admin_nonce"]) &&
            wp_verify_nonce($_POST["ncgasa_admin_nonce"], "ncgasa_admin_nonce")
        ) {
            $product_id = sanitize_key($_POST["productID"]);
            $from_date = sanitize_key($_POST["start_date"]);
            $to_date = sanitize_key($_POST["end_date"]);

            $subscribers = $this->ncgasa_get_subscriber_list( $product_id, $from_date );

            $this->ncgasa_push_csv_download($subscribers);
        }
        return false;
    }

    /**
     * ncgasa_push_csv_download
     *
     * create csv file, and push attachment header with csv file.
     * @param  array $data Subscriber data
     * @return object       CSV file attachment
     */
    final public function ncgasa_push_csv_download(array $data): void
    {
        ob_start();
        header("Content-Type: text/csv; charset=utf-8");
        header(
            "Content-Disposition: attachment; filename=ncgasa-subscriber-list.csv"
        );

        $header_args = ["ID", "Date", "Name", "Email", "Phone", "Membership"];

        ob_end_clean();

        $output = fopen("php://output", "w");

        fputcsv($output, $header_args);

        $payload = [];
        foreach ($data as $key => $value) {
            $payload[$key]["order_id"] = $value->order_id;
            $payload[$key]["post_date"] = $value->post_date;
            $payload[$key]["name"] =
                $value->_billing_first_name . " " . $value->_billing_last_name;
            $payload[$key]["email"] = $value->billing_email;
            $payload[$key]["phone"] = $value->billing_phone;
            $payload[$key]["membership"] = $value->order_items;
        }

        foreach ($payload as $data_item) {
            fputcsv($output, $data_item);
        }

        fclose($output);
        return;
    }

    /**
     * DEBUGGER
     *
     * dump value in pre tags, end script.
     * @param  mixed $value
     * @return mixed
     */
    final public function dd(mixed $value, bool $stop = true): void
    {
        echo "<pre>" . print_r($value, true) . "</pre>";

        if ($stop === true) {
            exit();
        }
    }
}

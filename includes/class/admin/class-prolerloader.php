<?php
/**
 * Role based pricing plugin loader class
 *
 * @package    WordPress
 * @subpackage Multiple Products to Cart for WooCommerce
 * @since      4.0
 */

if ( ! class_exists( 'ProlerLoader' ) ) {
	/**
	 * Plugin loader main class
	 */
	class ProlerLoader {



		/**
		 * Plugin init action hook - main entry of the plugin
		 */
		public function init() {
			register_activation_hook( PROLER, array( $this, 'activate_plugin' ) );
			register_deactivation_hook( PROLER, array( $this, 'deactivate_plugin' ) );

			add_action( 'init', array( $this, 'init_plugin' ) );

			// WooCommerce High-Performance Order Storage (HPOS) compatibility enable.
			add_action(
				'before_woocommerce_init',
				function () {
					if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
						\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', PROLER, true );
					}
				}
			);
		}



		/**
		 * Activate plugin functionality
		 */
		public function activate_plugin() {
			$this->init_plugin();

			do_action( 'proler_init_core_fields' );
			flush_rewrite_rules();
		}

		/**
		 * Deactivate plugin functionality
		 */
		public function deactivate_plugin() {
			global $proler__;

			// Clear the permalinks to remove our post type's rules from the database.
			flush_rewrite_rules();
		}



		/**
		 * Load main functionality of the plugin
		 */
		public function init_plugin() {
			// check prerequisits.
			if ( ! $this->should_activate() ) {
				return;
			}

			// needs to be off the hook in the next version.
			include PROLER_PATH . 'includes/core-data.php';

			include PROLER_PATH . 'includes/class/class-proler.php';
			include PROLER_PATH . 'includes/class/admin/class-proleradmintemplate.php';
			include PROLER_PATH . 'includes/class/admin/class-prolersettings.php';

			$this->check_pro();
			$this->ask_feedback();

			add_filter( 'plugin_action_links_' . plugin_basename( PROLER ), array( $this, 'plugin_action_links' ) );
			add_filter( 'plugin_row_meta', array( $this, 'plugin_desc_meta' ), 10, 2 );

			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'front_enqueue' ) );

			add_action( 'admin_head', array( $this, 'admin_head' ) );

			load_plugin_textdomain( 'product-role-rules', false, plugin_basename( dirname( PROLER ) ) . '/languages' );
		}



		/**
		 * Check if we should load plugin functionlity
		 */
		public function should_activate() {

			// check if is_plugin_active founction not found | rare case.
			if ( ! function_exists( 'is_plugin_active' ) ) {
				include_once ABSPATH . 'wp-admin/includes/plugin.php';
			}

			$plugin = 'product-role-rules/product-role-rules.php';

			$is_wc_active     = is_plugin_active( 'woocommerce/woocommerce.php' );
			$is_plugin_active = is_plugin_active( $plugin );

			if ( ! $is_wc_active && $is_plugin_active ) {
				add_action( 'admin_notices', array( $this, 'wc_missing_notice' ) );
				deactivate_plugins( $plugin );

				return false;
			}

			return true;
		}

		/**
		 * Add plugin action links
		 *
		 * @param array $links given actiion links data.
		 */
		public function plugin_action_links( $links ) {

			global $proler__;

			$action_links             = array();
			$action_links['settings'] = sprintf(
				'<a href="%s">%s</a>',
				esc_url( admin_url( 'admin.php?page=proler-settings' ) ),
				__( 'Settings', 'product-role-rules' )
			);

			if ( 'activated' !== $proler__['prostate'] ) {
				$action_links['premium'] = sprintf(
					'<a href="%s" style="color: #FF8C00;font-weight: bold;text-transform: uppercase;">%s</a>',
					esc_url( $proler__['url']['pro'] ),
					__( 'Get PRO', 'product-role-rules' )
				);
			}

			return array_merge( $action_links, $links );
		}

		/**
		 * Add plugin description metadata
		 *
		 * @param array  $links plugin description links data.
		 * @param string $file  given plugin file name.
		 */
		public function plugin_desc_meta( $links, $file ) {
			global $proler__;

			// if it's not Role Based Product plugin, return.
			if ( plugin_basename( PROLER ) !== $file ) {
				return $links;
			}

			$row_meta = array();
			$row_meta['apidocs'] = sprintf( '<a href="%s">%s</a>', esc_url( $proler__['url']['support'] ), __( 'Support', 'product-role-rules' ) );

			return array_merge( $links, $row_meta );
		}

		/**
		 * Enqueue admin scripts
		 */
		public function admin_enqueue() {
			global $proler__;

			// check scope, without it return.
			if ( ! $this->is_in_scope() ) {
				return;
			}

			// enqueue style.
			wp_register_style( 'proler_admin_style', plugin_dir_url( PROLER ) . 'assets/admin/admin.css', array(), PROLER_VER );
			wp_enqueue_style( 'proler_admin_style' );

			wp_register_script( 'proler_admin_script', plugin_dir_url( PROLER ) . 'assets/admin/admin.js', array( 'jquery', 'jquery-ui-slider', 'jquery-ui-sortable' ), PROLER_VER, false );
			wp_enqueue_script( 'proler_admin_script' );

			$var = array(
				'ajaxurl'         => admin_url( 'admin-ajax.php' ),
				'has_pro'         => $proler__['has_pro'],
				'nonce'           => wp_create_nonce( 'ajax-nonce' ),
				'cat_nonce'       => wp_create_nonce( 'category_nonce' ),
				'settings_page'   => admin_url( 'admin.php?page=proler-settings' ),
				'right_arrow'     => plugin_dir_url( PROLER ) . 'assets/images/right.svg',
				'down_arrow'      => plugin_dir_url( PROLER ) . 'assets/images/down.svg',
				'delete_role_msg' => __( 'Are you sure you want to delete this role?', 'product-role-rules' ),
			);

			// apply hook for editing localized variables in admin script.
			$var = apply_filters( 'proler_admin_update_local_var', $var );

			wp_localize_script( 'proler_admin_script', 'proler', $var );
		}

		/**
		 * Frontend styles and scripts enqueuing
		 */
		public function front_enqueue() {
			global $post;
			global $proler__;

			if( empty( $post ) || ! isset( $post->ID ) ){
				return;
			}

			// enqueue style.
			wp_register_style( 'proler_frontend_style', plugin_dir_url( PROLER ) . 'assets/frontend.css', array(), PROLER_VER );
			wp_enqueue_style( 'proler_frontend_style' );

			wp_register_script( 'proler_frontend_script', plugin_dir_url( PROLER ) . 'assets/frontend.js', array( 'jquery' ), PROLER_VER, true );
			wp_enqueue_script( 'proler_frontend_script' );

			// localized data.
			$data = array(
				'product' => $post->ID
			);

			// localize frontend data.
            wp_localize_script( 'proler_frontend_script', 'proler', $data );
		}

		/**
		 * Admin head functionlity
		 */
		public function admin_head() {
			$this->handle_notice();
			$this->menu_css();
		}



		/**
		 * Check if PRO plugin exists
		 */
		public function check_pro() {
			global $proler__;

			$proler__['has_pro']  = false;
			$proler__['prostate'] = 'none';

			do_action( 'proler_admin_change_pro_state' );
		}

		/**
		 * Ask client feedback about the plugin rating
		 */
		public function ask_feedback() {
			// process feedback data.
			$this->should_ask_feedback();

			$callbacks = array();

			if ( $this->if_show_notice( 'proler_notify_pro' ) ) {
				$callbacks[] = 'pro_notice';
			}

			if ( $this->if_show_notice( 'proler_rating' ) ) {
				$callbacks[] = 'feedback_notice';
			}

			if ( empty( $callbacks ) ) {
				return;
			}

			remove_all_actions( 'admin_notices' );

			foreach ( $callbacks as $callback ) {
				add_action( 'admin_notices', array( $this, $callback ) );
			}
		}

		/**
		 * Check if we should ask for client feedback
		 */
		public function should_ask_feedback() {
			if ( isset( $_GET['prnonce'] ) && ! wp_verify_nonce( sanitize_key( wp_unslash( $_GET['prnonce'] ) ), 'proler_rating_nonce' ) ) {
				return;
			}

			if ( isset( $_GET['proler_rating'] ) ) {
				$task = sanitize_title( wp_unslash( $_GET['proler_rating'] ) );

				if ( 'done' === $task ) {
					update_option( 'proler_rating', 'done' );
				} elseif ( 'cancel' === $task ) {
					update_option( 'proler_rating', gmdate( 'Y-m-d' ) );
				}
			} elseif ( isset( $_GET['proler_notify_pro'] ) ) {
				$task = sanitize_title( wp_unslash( $_GET['proler_notify_pro'] ) );

				if ( 'cancel' === $task ) {
					update_option( 'proler_notify_pro', gmdate( 'Y-m-d' ) );
				}
			}
		}

		/**
		 * If given key name notice should be displayed after calculating interval time
		 *
		 * @param string $key option meta key to determine the saved date.
		 */
		public function if_show_notice( $key ) {
			global $proler__;

			$value = get_option( $key );

			if ( empty( $value ) ) {
				update_option( $key, gmdate( 'Y-m-d' ) );
				return false;
			}

			// if notice is done displaying forever?
			if ( 'done' === $value ) {
				return false;
			}

			// see if interval period passed.
			$difference  = date_diff( date_create( gmdate( 'Y-m-d' ) ), date_create( $value ) );
			$days_passed = (int) $difference->format( '%d' );

			return $days_passed < $proler__['notice_gap'] ? false : true;
		}

		/**
		 * Move all admin notices from the action hooks to global variable
		 */
		public function handle_notice() {
			global $proler__;

			// check scope, without it return.
			if ( ! $this->is_in_scope() ) {
				return;
			}

			// Buffer only the notices.
			ob_start();
			do_action( 'admin_notices' );
			$content = ob_get_contents();
			ob_get_clean();

			// Keep the notices in global $proler__.
			array_push( $proler__['notice'], $content );

			// Remove all admin notices as we don't need to display in it's place.
			remove_all_actions( 'admin_notices' );
		}

		/**
		 * Add admin menu styling
		 */
		public function menu_css() {
			?>
			<style>
				#toplevel_page_proler-settings img {
					width: 20px;
					opacity: 1 !important;
				}
				.notice h3{
					margin-top: .5em;
					margin-bottom: 0;
				}
			</style>
			<?php
		}



		/**
		 * Notice: Trying to activate plugin while WooCommerce is not
		 */
		public function wc_missing_notice() {
			global $proler__;

			$wc = sprintf( '<a href="%s">%s</a>', esc_url( $proler__['url']['wc'] ), __( 'WooCommerce', 'product-role-rules' ) );

			?>
			<div class="error">
				<p>
				<?php

					echo wp_kses_post(
						sprintf(
							// translators: Placeholder %1$s is for the variable WooCommerce plugin link.
							__( 'Please install and activate %1$s first', 'product-role-rules' ),
							wp_kses_post( $wc )
						)
					);

				?>
				</p>
			</div>
			<?php
		}

		/**
		 * Notice: Ask feedback review
		 */
		public function feedback_notice() {

			global $proler__;

			// get current page.
			$page = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_url( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';

			// dynamic extra parameter adding before adding new url parameters.
			$page .= strpos( $page, '?' ) !== false ? '&' : '?';
			$page .= 'prnonce=' . wp_create_nonce( 'proler_rating_nonce' ) . '&';

			$plugin = sprintf(
				'<a href="%s">%s</a>',
				esc_url( $proler__['url']['review'] ),
				esc_html( $proler__['name'] )
			);
			$review = sprintf(
				'<a href="%s">' . __( 'WordPress.org', 'product-role-rules' ) . '</a>',
				esc_url( $proler__['url']['review'] )
			);

			?>
			<div class="notice notice-info is-dismissible">
				<h3><?php echo esc_html( $proler__['name'] ); ?></h3>
				<p>
					<?php

					echo wp_kses_post(
						sprintf(
							// translators: %1$s: plugin name, %2$s: review link.
							__( 'Excellent! You\'ve been using %1$s for a while. We\'d appreciate if you kindly rate us on %2$s.', 'product-role-rules' ),
							wp_kses_post( $plugin ),
							wp_kses_post( $review )
						)
					);

					?>
				</p>
				<p>
					<a href="<?php echo esc_url( $proler__['url']['review'] ); ?>" class="button-primary">
					<?php echo esc_html__( 'Rate it', 'product-role-rules' ); ?>
					</a> <a href="<?php echo esc_url( $page ); ?>proler_rating=done" class="button">
					<?php echo esc_html__( 'Already Did', 'product-role-rules' ); ?>
					</a> <a href="<?php echo esc_url( $page ); ?>proler_rating=cancel" class="button">
					<?php echo esc_html__( 'Cancel', 'product-role-rules' ); ?>
					</a>
				</p>
			</div>
			<?php
		}


		/**
		 * Notice: Inform about PRO plugin
		 */
		public function pro_notice() {
			global $proler__;

			// get current page.
			$page = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_url( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';

			// dynamic extra parameter adding before adding new url parameters.
			$page .= strpos( $page, '?' ) !== false ? '&' : '?';
			$page .= 'prnonce=' . wp_create_nonce( 'proler_rating_nonce' ) . '&';

			?>
			<div class="notice notice-warning is-dismissible">
				<h3>
					<?php echo esc_html( $proler__['name'] ); ?> <?php echo esc_html__( 'PRO', 'product-role-rules' ); ?>
				</h3>
				<p>
					<?php echo esc_html__( 'Get maximum/minimum quantity support with PRO.', 'product-role-rules' ); ?>
				</p>
				<p>
					<a href="<?php echo esc_url( $proler__['url']['pro'] ); ?>" class="button-primary">
						<?php echo esc_html__( 'Get PRO', 'product-role-rules' ); ?>
					</a> <a href="<?php echo esc_url( $page ); ?>proler_notify_pro=cancel" class="button">
						<?php echo esc_html__( 'Cancel', 'product-role-rules' ); ?>
					</a>
				</p>
			</div>
			<?php
		}




		/**
		 * Check if the current access scope is within plugin allowed screens
		 */
		public function is_in_scope() {
			global $proler__;

			$screen = get_current_screen();
			if ( ! $screen ) {
				return false;
			}

			$decoded_screen_id = urldecode( $screen->id );

			// check if '_page_' exists in the screen id.
			$has_page_in_url = strpos( $decoded_screen_id, '_page_' );

			// if screen id doesn't contain '_page_' and its not in plugin screen ids, return false.
			if ( false === $has_page_in_url ) {
				return in_array( $decoded_screen_id, $proler__['screen'], true );
			}

			foreach ( $proler__['screen'] as $suffix ) {

				// if suffix contains '_page_' and it matches partially with current screen id.
				if ( false !== strpos( $suffix, '_page_' ) && false !== strpos( $decoded_screen_id, $suffix ) ) {
					return true;
				}
			}

			return false;
		}
	}
}

$cls = new ProlerLoader();
$cls->init();

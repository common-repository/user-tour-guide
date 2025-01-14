<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://kamalhossan.github.io/
 * @since      1.0.0
 *
 * @package    User_Tour_Guide
 * @subpackage User_Tour_Guide/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    User_Tour_Guide
 * @subpackage User_Tour_Guide/public
 * @author     Kamal Hossan <kamal.hossan35@gmail.com>
 */

if (! defined('ABSPATH')) exit;
class User_Tour_Guide_Public
{

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $user_tour_guide    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The name of the DB of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $user_tour_guide_db_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct($plugin_name, $version, $db_name)
	{

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->version = $version;
		$this->user_tour_guide_db_name = $db_name;

		//register shortcode
		add_shortcode('utgk-guide', array($this, 'utgk_user_tour_guide_callback'));
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles()
	{

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Plugin_Name_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Plugin_Name_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */



		// wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/user-tour-guide-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts()
	{

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Plugin_Name_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Plugin_Name_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		// Enqueue your script here
		// wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/user-tour-guide-public.js', array( 'jquery' ), $this->version, false );

	}

	public function utgk_user_tour_guide_callback($atts)
	{

		$atts = shortcode_atts(array(
			'tour' => 'user-tour-guide',
		), $atts, 'utg-user-tour-guide');


		$tour_name = $atts['tour'];

		if (get_option('utg_tour_option', true)) {
			return;
		}

		ob_start();

		if (get_option('show_begin_tour', true)) { ?>
			<script>
				window.addEventListener('DOMContentLoaded', function() {
					const newDiv = document.createElement("div");
					newDiv.className = "utg-guide";
					newDiv.innerHTML = `<button id="<?php echo esc_html($tour_name) ?>" class="utg-tour-start"><?php echo esc_html(__('Begin Tour', 'user-tour-guide')); ?></button>`;
					document.body.appendChild(newDiv);
				});
			</script>
<?php
		}

		if (get_option('auto_start_for_new_user', true)) {
			$user_status = get_user_meta(get_current_user_id(), 'complete_or_skip_tour', true);
			if (!$user_status) {
				$complete = true;
			}
		} elseif (get_option('start_immidiately', true)) {
			$complete = true;
		} else {
			$complete = false;
		}
		$settings = array(
			'closeButton' => (get_option('show_close_button')) ? true :  false,
			'showStepDots' => (get_option('show_dots')) ? true :  false,
			'hidePrev' => (get_option('show_prev_button')) ? false : true,
			'hideNext' => (get_option('show_next_button')) ? false : true,
			'showStepProgress' => (get_option('show_step_progress')) ? true : false,
			'keyboardControls' => (get_option('enable_keyboard_control')) ? true : false,
			'exitOnEscape' => (get_option('exit_on_escape')) ? true : false,
			'exitOnClickOutside' => (get_option('exit_on_click_outside')) ? true : false,
			'nextLabel' => __('Next', 'user-tour-guide'),
			'prevLabel' => __('Previous', 'user-tour-guide'),
			'finishLabel' => __('Finish', 'user-tour-guide'),
		);

		wp_enqueue_style('intro-style', plugin_dir_url(__FILE__) . 'css/tour.min.css', array(), $this->version, 'all');
		wp_enqueue_style('user-tour-guide-style', plugin_dir_url(__FILE__) . 'css/user-tour-guide-public.css', array(), $this->version, 'all');
		wp_enqueue_script('intro-script', plugin_dir_url(__FILE__) . 'js/tourguide.min.js', array(), $this->version, false);
		wp_enqueue_script('user-tour-public', plugin_dir_url(__FILE__) . 'js/user-tour-guide-public.js', array('jquery', 'intro-script'), $this->version, false);

		wp_localize_script(
			'user-tour-public',
			'utg_public_object',
			array(
				'ajax_url' => admin_url('admin-ajax.php'),
				'nonce' => wp_create_nonce('utg_public_nonce'),
				'complete' => $complete,
				'setting' => $settings,
			)
		);
		return ob_get_clean();
	}

	public function utgk_get_user_tour_data_from_db()
	{

		check_ajax_referer('utg_public_nonce', 'nonce');

		$all_tour_step = wp_cache_get('all_tour_step');

		if (false === $all_tour_step) {
			global $wpdb;
			$results = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}utg_user_tour_guide", ARRAY_A);
			wp_cache_set('all_tour_step', $results, '', 7200);
			header('Content-Type: application/json');
			echo wp_json_encode($results);
			die();
		} else {
			header('Content-Type: application/json');
			echo wp_json_encode($all_tour_step);
			die();
		}
	}

	public function utgk_change_user_meta()
	{

		check_ajax_referer('utg_public_nonce', 'nonce');

		update_user_meta(get_current_user_id(), 'complete_or_skip_tour', true);

		echo true;

		die();
	}
}

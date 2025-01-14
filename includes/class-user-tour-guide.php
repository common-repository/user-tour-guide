<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link      https://kamalhossan.github.io/
 * @since      1.0.0
 *
 * @package    User_Tour_Guide
 * @subpackage User_Tour_Guide/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    User_Tour_Guide
 * @subpackage User_Tour_Guide/includes
 * @author     Kamal Hossan <kamal.hossan35@gmail.com>
 */

if (! defined('ABSPATH')) exit;
class User_Tour_Guide
{

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      User_Tour_Guide_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $user_tour_guide    The string used to uniquely identify this plugin.
	 */
	protected $user_tour_guide;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $user_tour_guide    The string used to uniquely identify this plugin.
	 */
	protected $user_tour_guide_db_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct()
	{
		if (defined('USER_TOUR_GUIDE_VERSION')) {
			$this->version = USER_TOUR_GUIDE_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->user_tour_guide = 'user-tour-guide';
		$this->user_tour_guide_db_name = 'utg_user_tour_guide';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - User_Tour_Guide_Loader. Orchestrates the hooks of the plugin.
	 * - User_Tour_Guide_i18n. Defines internationalization functionality.
	 * - User_Tour_Guide_Admin. Defines all hooks for the admin area.
	 * - User_Tour_Guide_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies()
	{

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-user-tour-guide-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-user-tour-guide-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-user-tour-guide-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'public/class-user-tour-guide-public.php';

		$this->loader = new User_Tour_Guide_Loader();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the user_tour_guide_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale()
	{

		$plugin_i18n = new User_Tour_Guide_i18n();

		$this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks()
	{

		$plugin_admin = new User_Tour_Guide_Admin($this->get_user_tour_guide(), $this->get_version(), $this->get_db_name());

		$this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
		$this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');

		//Register menu page
		$this->loader->add_action('admin_menu', $plugin_admin, 'utgk_user_tour_guide_settings_page');
		$this->loader->add_action('init', $plugin_admin, 'utgk_registering_session_for_tabs');

		//Register settings
		// $this->loader->add_action( 'admin_menu', $plugin_admin, 'utg_add_settings_page');
		$this->loader->add_action('admin_init', $plugin_admin, 'utgk_user_tour_guide_register_settings');

		// Filter hooks
		$this->loader->add_filter(
			'plugin_action_links_' . plugin_basename(USER_TOUR_GUIDE_PLUGIN_FILE),
			$plugin_admin,
			'utgk_add_settings_link_to_plugin_list',
		);

		// load translation
		$this->loader->add_action('plugin_loaded', $plugin_admin, 'utg_load_plugin_textdomain');

		// Ajax Actions
		$this->loader->add_action('wp_ajax_utg_get_tour_data_from_db', $plugin_admin, 'utg_get_tour_data_from_db');
		$this->loader->add_action('wp_ajax_nopriv_utg_get_tour_data_from_db', $plugin_admin, 'utg_get_tour_data_from_db');
		$this->loader->add_action('wp_ajax_utg_add_steps_to_db', $plugin_admin, 'utgk_add_steps_to_db');
		$this->loader->add_action('wp_ajax_nopriv_utg_add_steps_to_db', $plugin_admin, 'utgk_add_steps_to_db');
		$this->loader->add_action('wp_ajax_utg_edit_steps_to_db', $plugin_admin, 'utgk_edit_steps_to_db');
		$this->loader->add_action('wp_ajax_nopriv_utg_edit_steps_to_db', $plugin_admin, 'utgk_edit_steps_to_db');
		$this->loader->add_action('wp_ajax_utg_remove_steps_from_db', $plugin_admin, 'utgk_remove_steps_from_db');
		$this->loader->add_action('wp_ajax_nopriv_utg_remove_steps_from_db', $plugin_admin, 'utgk_remove_steps_from_db');
		$this->loader->add_action('wp_ajax_utg_admin_tour_skip', $plugin_admin, 'utgk_admin_tour_skip');
		$this->loader->add_action('wp_ajax_nopriv_utg_admin_tour_skip', $plugin_admin, 'utgk_admin_tour_skip');
		$this->loader->add_action('wp_ajax_save_active_tab', $plugin_admin, 'utgk_save_active_tab_with_session');
		$this->loader->add_action('wp_ajax_nopriv_save_active_tab', $plugin_admin, 'utgk_save_active_tab_with_session');
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks()
	{

		$plugin_public = new User_Tour_Guide_Public($this->get_user_tour_guide(), $this->get_version(), $this->get_db_name());

		$this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
		$this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');

		// Ajax Actions
		$this->loader->add_action('wp_ajax_utg_t', $plugin_public, 'utgk_get_user_tour_data_from_db');
		$this->loader->add_action('wp_ajax_nopriv_utg_t', $plugin_public, 'utgk_get_user_tour_data_from_db');
		$this->loader->add_action('wp_ajax_utg_change_user_meta', $plugin_public, 'utgk_change_user_meta');
		$this->loader->add_action('wp_ajax_nopriv_utg_change_user_meta', $plugin_public, 'utgk_change_user_meta');
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run()
	{
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_user_tour_guide()
	{
		return $this->user_tour_guide;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    User_Tour_Guide_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader()
	{
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version()
	{
		return $this->version;
	}

	public function get_db_name()
	{
		return $this->user_tour_guide_db_name;
	}
}

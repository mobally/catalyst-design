<?php
/*
Plugin Name: SQ KLEO Monetizer
Plugin URL: http://seventhqueen.com/
Description: A plugin that gives you possibility to add banners to predefined zones in KLEO THEME.
Version: 1.0.0
Author: SeventhQueen
Author URI: http://seventhqueen.com/
Text Domain: sq-kleo-monetizer
Domain Path: /languages
*/

/**
 * Class Kleo_Monetizer
 * Adds banner zones to certain Kleo theme locations
 */
class Kleo_Monetizer {

	/**
	 * @var Kleo_Monetizer The single instance of the class
	 * @since 1.0.0
	 */
	protected static $_instance = null;

	public function __construct() {
		if ( is_admin() ) {
			add_action( 'after_setup_theme', array( $this, 'register_options' ), 20 );
		}
		add_action( 'template_redirect', array( $this, 'register_render_actions' ) );
	}

	/**
	 * Main Instance
	 *
	 * Ensures only one instance is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @see Kleo_Monetizer()
	 * @return Kleo_Monetizer - Main instance
	 */
	public static function instance( $args = array() ) {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self( $args );
		}

		return self::$_instance;
	}

	public function register_options() {
		if ( ! class_exists( 'Kleo' ) || ! class_exists( 'Redux' ) ) {
			return;
		}
		$section = array(
			'icon'       => 'el-icon-eur',
			'icon_class' => 'icon-large',
			'title'      => esc_html__( 'Kleo Monetizer', 'sq-kleo-monetizer' ),
			'customizer' => false,
			'desc'       => '<p class="description">' . esc_html__( 'Monetize Kleo Plugin', 'sq-kleo-monetizer' ) . '</p>',
			'fields'     => array(
				array(
					'id'       => 'km_before_single_content',
					'type'     => 'textarea',
					'title'    => esc_html__( 'Banner Zone - General Before Content', 'sq-kleo-monetizer' ),
					'subtitle' => esc_html__( 'Add your adsense code with script tags [ Will not be rendered in front-page and in buddypress]', 'sq-kleo-monetizer' ),
					'default'  => '',
				),
				array(
					'id'       => 'km_after_single_content',
					'type'     => 'textarea',
					'title'    => esc_html__( 'Banner Zone - General After Content ', 'sq-kleo-monetizer' ),
					'subtitle' => esc_html__( 'Add your adsense code with script tags [ Will not be rendered in front-page and in buddypress ]', 'sq-kleo-monetizer' ),
					'default'  => '',
				),
				array(
					'id'       => 'km_before_single_inner_content',
					'type'     => 'textarea',
					'title'    => esc_html__( 'Banner Zone - Before Article Inner Content', 'sq-kleo-monetizer' ),
					'subtitle' => esc_html__( 'Add your adsense code with script tags [ Will be rendered in single post page ]', 'sq-kleo-monetizer' ),
					'default'  => '',
				),
				array(
					'id'       => 'km_after_single_inner_main_content',
					'type'     => 'textarea',
					'title'    => esc_html__( 'Banner Zone - After Article Inner Content', 'sq-kleo-monetizer' ),
					'subtitle' => esc_html__( 'Add your adsense code with script tags [ Will be rendered in single post page ]', 'sq-kleo-monetizer' ),
					'default'  => '',
				),
				array(
					'id'       => 'km_before_single_archive_content',
					'type'     => 'textarea',
					'title'    => esc_html__( 'Banner Zone - Before Archive Content', 'sq-kleo-monetizer' ),
					'subtitle' => esc_html__( 'Add your adsense code with script tags [ Will be rendered in archive pages ]', 'sq-kleo-monetizer' ),
					'default'  => '',
				),
				array(
					'id'       => 'km_after_single_archive_content',
					'type'     => 'textarea',
					'title'    => esc_html__( 'Banner Zone - After Archive Content', 'sq-kleo-monetizer' ),
					'subtitle' => esc_html__( 'Add your adsense code with script tags [ Will be rendered in archive pages ]', 'sq-kleo-monetizer' ),
					'default'  => '',
				),
				array(
					'id'       => 'km_before_blog_outer_content',
					'type'     => 'textarea',
					'title'    => esc_html__( 'Banner zone - Before Blog Content', 'sq-kleo-monetizer' ),
					'subtitle' => esc_html__( 'Add your adsense code with script tags [ Will be rendered in main blog page ]', 'sq-kleo-monetizer' ),
					'default'  => '',
				),
				array(
					'id'       => 'km_after_blog_outer_content',
					'type'     => 'textarea',
					'title'    => esc_html__( 'Banner zone - After Blog Content', 'sq-kleo-monetizer' ),
					'subtitle' => esc_html__( 'Add your adsense code with script tags [ Will be rendered in main blog page ]', 'sq-kleo-monetizer' ),
					'default'  => '',
				),
			),
		);

		Redux::setSection( 'kleo_' . KLEO_DOMAIN, $section );
	}

	public function register_render_actions() {
		if ( ! class_exists( 'Kleo' ) ) {
			return;
		}

		if ( sq_option( 'km_before_single_content' ) ) {
			add_action( 'kleo_before_content', array( $this, 'render_before_single_content' ) );
		}

		if ( sq_option( 'km_after_single_content' ) ) {
			add_action( 'kleo_after_main_content', array( $this, 'render_after_main_content' ) );
		}

		if ( sq_option( 'km_before_single_inner_content' ) ) {
			add_action( 'kleo_before_inner_article_loop', array( $this, 'render_before_single_inner_content' ) );
		}

		if ( sq_option( 'km_after_single_inner_main_content' ) ) {
			add_action( 'kleo_after_inner_article_loop', array( $this, 'render_after_single_inner_main_content' ) );
		}

		if ( sq_option( 'km_before_single_archive_content' ) ) {
			add_action( 'kleo_before_archive_content', array( $this, 'render_before_single_archive_content' ) );
		}

		if ( sq_option( 'km_after_single_archive_content' ) ) {
			add_action( 'kleo_after_archive_content', array( $this, 'render_after_single_archive_content' ) );
		}

		if ( sq_option( 'km_before_blog_outer_content' ) ) {
			add_action( 'kleo_before_blog_outer_content', array( $this, 'render_before_blog_outer_content' ) );
		}

		if ( sq_option( 'km_after_blog_outer_content' ) ) {
			add_action( 'kleo_after_blog_outer_content', array( $this, 'render_after_blog_outer_content' ) );
		}
	}


	public function render_before_single_content() {
		if ( ! is_front_page() && ! is_buddypress() && ! is_archive() && ! is_home() && is_singular( 'post' ) ) {
			echo '<div style="margin: 10px auto;text-align:center;">' . sq_option( 'km_before_single_content' ) . '</div>';
		}
	}
	public function render_after_main_content() {
		if ( ! is_front_page() && ! is_buddypress() && ! is_archive() && ! is_home() && is_singular( 'post' )  ) {
			$output = '';
			$output .= '<div style="margin: 10px auto;text-align:center;">' . sq_option( 'km_after_single_content' ) . '</div>';
			echo $output;
		}
	}
	public function render_before_single_inner_content() {
		if ( is_single() && ! is_front_page() && ! is_buddypress() && is_singular( 'post' ) ) {
			$output = '';
			$output .= '<div style="margin: 10px auto;text-align:center;">' . sq_option( 'km_before_single_inner_content' ) . '</div>';
			echo $output;
		}
	}
	public function render_after_single_inner_main_content() {
		if ( is_single() && ! is_front_page() && ! is_buddypress() && is_singular( 'post' ) ) {
			$output = '';
			$output .= '<div style="margin: 10px auto;text-align:center;">' . sq_option( 'km_after_single_inner_main_content' ) . '</div>';
			echo $output;
		}
	}
	public function render_before_single_archive_content() {
		if ( is_archive() ) {
			$output = '';
			$output .= '<div style="margin: 10px auto;text-align:center;">' . sq_option( 'km_before_single_archive_content' ) . '</div>';
			echo $output;
		}
	}
	public function render_after_single_archive_content() {
		if ( is_archive() ) {
			$output = '';
			$output .= '<div style="margin: 10px auto;text-align:center;">' . sq_option( 'km_after_single_archive_content' ) . '</div>';
			echo $output;
		}
	}
	public function render_before_blog_outer_content() {
		if ( is_home() ) {
			$output = '';
			$output .= '<div style="margin: 10px auto;text-align:center;">' . sq_option( 'km_before_blog_outer_content' ) . '</div>';
			echo $output;
		}
	}
	public function render_after_blog_outer_content() {
		if ( is_home() ) {
			$output = '';
			$output .= '<div style="margin: 10px auto;text-align:center;">' . sq_option( 'km_after_blog_outer_content' ) . '</div>';
			echo $output;
		}
	}
}

/* Initialize our class */
Kleo_Monetizer::instance();

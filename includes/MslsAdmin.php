<?php
/**
 * MslsAdmin
 * @author Dennis Ploetner <re@lloc.de>
 * @since 0.9.8
 */

/**
 * Administration of the options
 * @package Msls
 */
class MslsAdmin extends MslsMain {

	protected $languages = array();

	/**
	 * Init
	 * @return MslsAdmin
	 */
	public static function init() {
		wp_enqueue_style(
			'msls-styles',
			plugins_url( 'css/msls.css', MSLS_PLUGIN__FILE__ ),
			array(),
			MSLS_PLUGIN_VERSION
		);

		$obj = new self();

		if ( MslsOptions::instance()->activate_autocomplete ) {
			wp_enqueue_script(
				'msls-autocomplete',
				plugins_url( 'js/msls.min.js', MSLS_PLUGIN__FILE__ ),
				array( 'jquery-ui-autocomplete' ),
				MSLS_PLUGIN_VERSION
			);
		}

		add_options_page(
			__( 'Multisite Language Switcher', 'msls' ),
			__( 'Multisite Language Switcher', 'msls' ),
			'manage_options',
			__CLASS__,
			array( $obj, 'render' )
		);

		add_action( 'admin_init',    array( $obj, 'register' ) );
		add_action( 'admin_notices', array( $obj, 'has_problems' ) );

		return $obj;
	}

	/**
	 * There is something wrong? Here comes the message...
	 * @return boolean
	 */
	public function has_problems() {
		$message = '';

		if ( current_user_can( 'manage_options' ) ) {
			if ( 1 == count( $this->languages ) ) {
				$message = sprintf(
					__( 'There are no language files installed. You can <a href="%s">manually install some language files</a> or you could use a <a href="%s">plugin</a> to download these files automatically.' ),
					esc_url( 'http://codex.wordpress.org/Installing_WordPress_in_Your_Language#Manually_Installing_Language_Files' ),
					esc_url( 'http://wordpress.org/plugins/wp-native-dashboard/' )
				);
			}
			elseif ( MslsOptions::instance()->is_empty() ) {
				$message = sprintf(
					__( 'Multisite Language Switcher is almost ready. You must <a href="%s">complete the configuration process</a>.' ),
					esc_url( admin_url( '/options-general.php?page=MslsAdmin' ) )
				);
			}
		}

		return MslsPlugin::message_handler( $message, 'updated fade' );
	}

	/**
	 * Render the options-page
	 */
	public function render() {
		printf(
			'<div class="wrap"><div class="icon32" id="icon-options-general"><br></div><h2>%s</h2>%s<form class="clear" action="options.php" method="post"><p>%s</p>',
			__( 'Multisite Language Switcher Options', 'msls' ),
			$this->subsubsub(),
			__( 'To achieve maximum flexibility, you have to configure each blog separately.', 'msls' )
		);
		settings_fields( 'msls' );
		do_settings_sections( __CLASS__ );
		printf(
			'<p class="submit"><input name="Submit" type="submit" class="button-primary" value="%s" /></p></form></div>',
			( MslsOptions::instance()->is_empty() ? __( 'Configure', 'msls' ) : __( 'Update', 'msls' ) )
		);
	}

	/**
	 * Create a submenu which contains links to all blogs of the current user
	 * @return string
	 */
	public function subsubsub() {
		$blogs = MslsBlogCollection::instance();
		$arr   = array();
		foreach ( $blogs->get_plugin_active_blogs() as $blog ) {
			$arr[] = sprintf(
				'<a href="%s"%s>%s / %s</a>',
				get_admin_url( $blog->userblog_id, '/options-general.php?page=MslsAdmin' ),
				( $blog->userblog_id == $blogs->get_current_blog_id() ? ' class="current"' : '' ),
				$blog->blogname,
				$blog->get_description()
			);
		}
		return(
			empty( $arr ) ?
			'' :
			sprintf(
				'<ul class="subsubsub"><li>%s</li></ul>',
				implode( ' | </li><li>', $arr )
			)
		);
	}

	/**
	 * Register the form-elements
	 */
	public function register() {
		register_setting( 'msls', 'msls', array( $this, 'validate' ) );

		$this->languages = array( 'en_US' => format_code_lang( 'en_US' ) );
		foreach ( get_available_languages() as $language ) {
			$this->languages[ esc_attr( $language ) ] = format_code_lang( $language );
		}
		$this->languages = (array) apply_filters( 'msls_admin_register_languages', $this->languages );

		add_settings_section(
			'language_section',
			__( 'Language Settings', 'msls' ),
			array( $this, 'language_section' ),
			__CLASS__
		);

		add_settings_field( 'blog_language', __( 'Blog Language', 'msls' ), array( $this, 'blog_language' ), __CLASS__, 'language_section' );
		add_settings_field( 'admin_language', __( 'Admin Language', 'msls' ), array( $this, 'admin_language' ), __CLASS__, 'language_section' );

		add_settings_section(
			'main_section',
			__( 'Main Settings', 'msls' ),
			array( $this, 'main_section' ),
			__CLASS__
		);

		add_settings_field( 'display', __( 'Display', 'msls' ), array( $this, 'display' ), __CLASS__, 'main_section' );
		add_settings_field( 'sort_by_description', __( 'Sort output by description', 'msls' ), array( $this, 'sort_by_description' ), __CLASS__, 'main_section' );
		add_settings_field( 'output_current_blog', __( 'Display link to the current language', 'msls' ), array( $this, 'output_current_blog' ), __CLASS__, 'main_section' );
		add_settings_field( 'only_with_translation', __( 'Show only links with a translation', 'msls' ), array( $this, 'only_with_translation' ), __CLASS__, 'main_section' );

		add_settings_field( 'description', __( 'Description', 'msls' ), array( $this, 'description' ), __CLASS__, 'main_section' );
		add_settings_field( 'before_output', __( 'Text/HTML before the list', 'msls' ), array( $this, 'before_output' ), __CLASS__, 'main_section' );
		add_settings_field( 'after_output', __( 'Text/HTML after the list', 'msls' ), array( $this, 'after_output' ), __CLASS__, 'main_section' );
		add_settings_field( 'before_item', __( 'Text/HTML before each item', 'msls' ), array( $this, 'before_item' ), __CLASS__, 'main_section' );
		add_settings_field( 'after_item', __( 'Text/HTML after each item', 'msls' ), array( $this, 'after_item' ), __CLASS__, 'main_section' );

		add_settings_field( 'content_filter', __( 'Add hint for available translations', 'msls' ), array( $this, 'content_filter' ), __CLASS__, 'main_section' );
		add_settings_field( 'content_priority', __( 'Hint priority', 'msls' ), array( $this, 'content_priority' ), __CLASS__, 'main_section' );

		add_settings_section(
			'advanced_section',
			__( 'Advanced Settings', 'msls' ),
			array( $this, 'advanced_section' ),
			__CLASS__
		);

		add_settings_field( 'activate_autocomplete', __( 'Activate experimental autocomplete inputs', 'msls' ), array( $this, 'activate_autocomplete' ), __CLASS__, 'advanced_section' );
		add_settings_field( 'image_url', __( 'Custom URL for flag-images', 'msls' ), array( $this, 'image_url' ), __CLASS__, 'advanced_section' );
		add_settings_field( 'reference_user', __( 'Reference user', 'msls' ), array( $this, 'reference_user' ), __CLASS__, 'advanced_section' );
		add_settings_field( 'exclude_current_blog', __( 'Exclude this blog from output', 'msls' ), array( $this, 'exclude_current_blog' ), __CLASS__, 'advanced_section' );
	}

	/**
	 * language_section is just a placeholder for now
	 */
	public function language_section() { }

	/**
	 * main_section is just a placeholder for now
	 */
	public function main_section() { }

	/**
	 * advanced_section is just a placeholder for now
	 */
	public function advanced_section() { }

	/**
	 * Shows the select-form-field 'blog_language'
	 */
	public function blog_language() {
		echo $this->render_select(
			'blog_language',
			$this->languages,
			get_option( 'WPLANG', 'en_US' )
		); // xss ok
	}

	/**
	 * Shows the select-form-field 'admin_language'
	 */
	public function admin_language() {
		echo $this->render_select(
			'admin_language',
			$this->languages,
			MslsOptions::instance()->admin_language
		); // xss ok
	}

	/**
	 * Shows the select-form-field 'display'
	 */
	public function display() {
		echo $this->render_select(
			'display',
			MslsLink::get_types_description(),
			MslsOptions::instance()->display
		); // xss ok
	}

	/**
	 * Shows the select-form-field 'reference_user'
	 */
	public function reference_user() {
		$users = array();
		foreach ( MslsBlogCollection::instance()->get_users() as $user ) {
			$users[ $user->ID ] = $user->user_nicename;
		}
		echo $this->render_select(
			'reference_user',
			$users,
			MslsOptions::instance()->reference_user
		); // xss ok
	}

	/**
	 * Activate autocomplete
	 *
	 * You can decide if you want to activate the experimental autocomplete
	 * input fields in the backend instead of the traditional select-menus.
	 */
	public function activate_autocomplete() {
		echo $this->render_checkbox( 'activate_autocomplete' ); // xss ok
	}

	/**
	 * Show sort_by_description-field
	 *
	 * You can decide that the ouput will be sorted by the description. If not
	 * the output will be sorted by the language-code.
	 */
	public function sort_by_description() {
		echo $this->render_checkbox( 'sort_by_description' ); // xss ok
	}

	/**
	 * Exclude the current blog
	 *
	 * You can exclude a blog explicitly. All your settings will be safe but the
	 * plugin will ignore this blog while this option is active.
	 */
	public function exclude_current_blog() {
		echo $this->render_checkbox( 'exclude_current_blog' ); // xss ok
	}

	/**
	 * Show only a link  if a translation is available
	 *
	 * Some user requested this feature. Shows only links to available
	 * translations.
	 */
	public function only_with_translation() {
		echo $this->render_checkbox( 'only_with_translation' ); // xss ok
	}

	/**
	 * Show a link to the current blog
	 *
	 * Some user requested this feature. If active the plugin will place also a
	 * link to the current blog.
	 */
	public function output_current_blog() {
		echo $this->render_checkbox( 'output_current_blog' ); // xss ok
	}

	/**
	 * The description for the current blog
	 *
	 * The language will be used ff there is no description.
	 */
	public function description() {
		echo $this->render_input( 'description', '40' ); // xss ok
	}

	/**
	 * A String which will be placed before the output of the list
	 */
	public function before_output() {
		echo $this->render_input( 'before_output' ); // xss ok
	}

	/**
	 * A String which will be placed after the output of the list
	 */
	public function after_output() {
		echo $this->render_input( 'after_output' ); // xss ok
	}

	/**
	 * A String which will be placed before every item of the list
	 */
	public function before_item() {
		echo $this->render_input( 'before_item' ); // xss ok
	}

	/**
	 * A String which will be placed after every item of the list
	 */
	public function after_item() {
		echo $this->render_input( 'after_item' ); // xss ok
	}

	/**
	 * The output can be placed after the_content
	 */
	public function content_filter() {
		echo $this->render_checkbox( 'content_filter' ); // xss ok
	}

	/**
	 * If the output in the_content is active you can set the priority too
	 *
	 * Default is 10. But may be there are other plugins active and you run into
	 * trouble. So you can decide a higher (from 1) or a lower (to 100) priority
	 * for the output
	 */
	public function content_priority() {
		$temp    = array_merge( range( 1, 10 ), array( 20, 50, 100 ) );
		$arr     = array_combine( $temp, $temp );
		$options = MslsOptions::instance();

		$selected = (
			empty( $options->content_priority ) ?
			10 :
			$options->content_priority
		);
		echo $this->render_select( 'content_priority', $arr, $selected ); // xss ok
	}

	/**
	 * Alternative image-url
	 *
	 * @todo This is a value of a directory-url which should be more clear
	 */
	public function image_url() {
		echo $this->render_input( 'image_url' ); // xss ok
	}

	/**
	 * Render form-element (checkbox)
	 *
	 * @param string $key Name and ID of the form-element
	 * @return string
	 */
	public function render_checkbox( $key ) {
		return sprintf(
			'<input type="checkbox" id="%1$s" name="msls[%1$s]" value="1" %2$s/>',
			$key,
			checked( 1, MslsOptions::instance()->$key, false )
		);
	}

	/**
	 * Render form-element (text-input)
	 *
	 * @param string $key Name and ID of the form-element
	 * @param string $size Size-attribute of the input-field
	 * @return string
	 */
	public function render_input( $key, $size = '30' ) {
		return sprintf(
			'<input id="%1$s" name="msls[%1$s]" value="%2$s" size="%3$s"/>',
			$key,
			esc_attr( MslsOptions::instance()->$key ),
			$size
		);
	}

	/**
	 * Render form-element (select)
	 * @uses selected
	 * @param string $key Name and ID of the form-element
	 * @param array $arr Options as associative array
	 * @param string $selected Values which should be selected
	 * @return string
	 */
	public function render_select( $key, array $arr, $selected = '' ) {
		$options = array();
		foreach ( $arr as $value => $description ) {
			$options[] = sprintf(
				'<option value="%s" %s>%s</option>',
				$value,
				selected( $value, $selected, false ),
				$description
			);
		}
		return sprintf(
			'<select id="%1$s" name="msls[%1$s]">%2$s</select>',
			$key,
			implode( '', $options )
		);
	}

	/**
	 * Validates input before saving it
	 *
	 * @param array $arr Values of the submitted form
	 * @return array Validated input
	 */
	public function validate( array $arr ) {
		if ( isset( $arr['blog_language'] ) ) {
			update_option( 'WPLANG', $arr['blog_language'] );
			unset( $arr['blog_language'] );
		}
		$arr['display'] = (
			isset( $arr['display'] ) ?
			(int) $arr['display'] :
			0
		);
		if ( isset( $arr['image_url'] ) ) {
			$arr['image_url'] = esc_url( rtrim( $arr['image_url'], '/' ) );
		}
		return $arr;
	}

}

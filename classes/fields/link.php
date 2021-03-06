<?php
require_once( PODS_DIR . 'classes/fields/website.php' );

/**
 * @package Pods\Fields
 */
class PodsField_Link extends PodsField_Website {

	/**
	 * Field Type Group
	 *
	 * @var string
	 * @since 2.7
	 */
	public static $group = 'Text';

	/**
	 * Field Type Identifier
	 *
	 * @var string
	 * @since 2.7
	 */
	public static $type = 'link';

	/**
	 * Field Type Label
	 *
	 * @var string
	 * @since 2.7
	 */
	public static $label = 'Link';

	/**
	 * Field Type Preparation
	 *
	 * @var string
	 * @since 2.7
	 */
	public static $prepare = '%s';

	/**
	 * Do things like register/enqueue scripts and stylesheets
	 *
	 * @since 2.7
	 */
	public function __construct() {
		self::$label = __( 'Link', 'pods' );
	}

	/**
	 * Add options and set defaults to
	 *
	 * @param array $options
	 *
	 * @since 2.7
	 */
	public function options() {

		$options = array(
			self::$type . '_format'            => array(
				'label'   => __( 'Format', 'pods' ),
				'default' => 'normal',
				'type'    => 'pick',
				'data'    => array(
					'none'              => __( 'No URL format restrictions', 'pods' ),
					'normal'            => __( 'http://example.com/', 'pods' ),
					'no-www'            => __( 'http://example.com/ (remove www)', 'pods' ),
					'force-www'         => __( 'http://www.example.com/ (force www if no sub-domain provided)', 'pods' ),
					'no-http'           => __( 'example.com', 'pods' ),
					'no-http-no-www'    => __( 'example.com (force removal of www)', 'pods' ),
					'no-http-force-www' => __( 'www.example.com (force www if no sub-domain provided)', 'pods' )
				)
			),
			self::$type . '_select_existing'   => array(
				'label'      => __( 'Enable Selecting from Existing Links?', 'pods' ),
				'default'    => 1,
				'type'       => 'boolean',
				'dependency' => true
			),
			self::$type . '_new_window'      => array(
				'label'      => __( 'Open in a new window by default?', 'pods' ),
				'default'    => 0,
				'type'       => 'boolean',
				'dependency' => false
			),
			'output_options'                   => array(
				'label' => __( 'Link Text Output Options', 'pods' ),
				'group' => array(
					self::$type . '_allow_shortcode' => array(
						'label'      => __( 'Allow Shortcodes?', 'pods' ),
						'default'    => 0,
						'type'       => 'boolean',
						'dependency' => true
					),
					self::$type . '_allow_html'      => array(
						'label'      => __( 'Allow HTML?', 'pods' ),
						'default'    => 0,
						'type'       => 'boolean',
						'dependency' => true
					)
				)
			),
			self::$type . '_allowed_html_tags' => array(
				'label'      => __( 'Allowed HTML Tags', 'pods' ),
				'depends-on' => array( self::$type . '_allow_html' => true ),
				'default'    => 'strong em a ul ol li b i',
				'type'       => 'text'
			),
			/*self::$type . '_max_length' => array(
				'label' => __( 'Maximum Length', 'pods' ),
				'default' => 255,
				'type' => 'number',
				'help' => __( 'Set to -1 for no limit', 'pods' )
			),*/
			self::$type . '_html5'             => array(
				'label'   => __( 'Enable HTML5 Input Field?', 'pods' ),
				'default' => apply_filters( 'pods_form_ui_field_html5', 0, self::$type ),
				'type'    => 'boolean'
			)/*,
			self::$type . '_size' => array(
				'label' => __( 'Field Size', 'pods' ),
				'default' => 'medium',
				'type' => 'pick',
				'data' => array(
					'small' => __( 'Small', 'pods' ),
					'medium' => __( 'Medium', 'pods' ),
					'large' => __( 'Large', 'pods' )
				)
			)*/
		);

		return $options;

	}

	/**
	 * Define the current field's schema for DB table storage
	 *
	 * @param array $options
	 *
	 * @return array
	 * @since 2.7
	 */
	public function schema( $options = null ) {

		$schema = 'LONGTEXT';

		return $schema;

	}

	/**
	 * Change the value of the field
	 *
	 * @param mixed  $value
	 * @param string $name
	 * @param array  $options
	 * @param array  $pod
	 * @param int    $id
	 *
	 * @return mixed|null|string
	 * @since 2.3
	 */
	public function value( $value = null, $name = null, $options = null, $pod = null, $id = null ) {

		return $value;

	}

	/**
	 * Change the way the value of the field is displayed with Pods::get
	 *
	 * @param mixed  $value
	 * @param string $name
	 * @param array  $options
	 * @param array  $pod
	 * @param int    $id
	 *
	 * @return mixed|null|string
	 * @since 2.7
	 */
	public function display( $value = null, $name = null, $options = null, $pod = null, $id = null ) {

		// Validate for an array because display is also used for the get_post_meta filters along the function chain
		if ( ! is_array( $value ) ) {
			return $value;
		}

		// Ensure proper format
		$value = $this->pre_save( $value, $id, $name, $options, null, $pod );

		if ( ! empty( $value['text'] ) ) {
			$value['text'] = $this->strip_html( $value['text'], $options );
		}

		if ( ! empty( $value['url'] ) ) {

			$link = '<a href="%s"%s>%s</a>';

			// Build the URL
			$url = $this->build_url( parse_url( $value['url'] ) );

			// Display URL as text by default. If text provided, use the text input
			$text = $url;

			if ( ! empty( $value['text'] ) ) {
				$text = $value['text'];
			}

			$atts = '';

			if ( ! empty( $value['target'] ) || ( ! isset( $value['target'] ) && 1 == pods_var( self::$type . '_new_window', $options ) ) ) {
				// Possible support for other targets in future
				$atts .= ' target="' . esc_attr( $value['target'] ) . '"';
			}

			// Do shortcodes if this is enabled
			if ( 1 == pods_var( self::$type . '_allow_shortcode', $options ) ) {
				$text = do_shortcode( $text );
			}

			// Return the value
			$value = sprintf( $link, esc_url( $url ), $atts, $text );

		} elseif ( ! empty( $value['text'] ) ) {
			// No URL data found (probably database error), return text is this is available
			$value = $value['text'];
		}

		// Return database value or display value if above conditions are met
		return $value;

	}

	/**
	 * Customize output of the form field
	 *
	 * @param string $name
	 * @param mixed  $value
	 * @param array  $options
	 * @param array  $pod
	 * @param int    $id
	 *
	 * @since 2.7
	 */
	public function input( $name, $value = null, $options = null, $pod = null, $id = null ) {

		$options         = (array) $options;
		$form_field_type = PodsForm::$field_type;
		$field_type      = 'link';

		// Update from a single (non array) input field (like website) if the field updates
		if ( is_string( $value ) ) {
			$value = array( 'url' => $value );
		}

		pods_view( PODS_LINK_FIELD_DIR . 'ui/fields/' . $field_type . '.php', compact( array_keys( get_defined_vars() ) ) );

	}

	/**
	 * Validate a value before it's saved
	 *
	 * @param mixed  $value
	 * @param string $name
	 * @param array  $options
	 * @param array  $fields
	 * @param array  $pod
	 * @param int    $id
	 *
	 * @since 2.7
	 */
	public function validate( $value, $name = null, $options = null, $fields = null, $pod = null, $id = null, $params = null ) {

		$errors = array();

		$label = strip_tags( pods_var_raw( 'label', $options, ucwords( str_replace( '_', ' ', $name ) ) ) );

		$check = $this->pre_save( $value, $id, $name, $options, $fields, $pod, $params );

		$check = $check['url'];

		if ( is_array( $check ) ) {
			$errors = $check;
		} else {
			if ( ! empty( $value['url'] ) && 0 < strlen( $value['url'] ) && strlen( $check ) < 1 ) {
				if ( 1 == pods_var( 'required', $options ) ) {
					$errors[] = sprintf( __( 'The %s field is required.', 'pods' ), $label );
				} else {
					$errors[] = sprintf( __( 'Invalid link provided for the field %s.', 'pods' ), $label );
				}
			}
		}

		if ( ! empty( $errors ) ) {
			return $errors;
		}

		return true;

	}

	/**
	 * Change the value or perform actions after validation but before saving to the DB
	 *
	 * @param mixed  $value
	 * @param int    $id
	 * @param string $name
	 * @param array  $options
	 * @param array  $fields
	 * @param array  $pod
	 * @param object $params
	 *
	 * @since 2.7
	 */
	public function pre_save( $value, $id = null, $name = null, $options = null, $fields = null, $pod = null, $params = null ) {

		$options = (array) $options;

		$value = array_merge( array( 'url' => '', 'text' => '', 'target' => '' ), (array) $value );

		// Start URL format
		if ( ! empty( $value['url'] ) ) {
			$value['url'] = $this->validate_url( $value['url'], $options );
		}

		// Start Title format
		if ( ! empty( $value['text'] ) ) {
			$value['text'] = $this->strip_html( $value['text'], $options );
		}

		// Start Target format
		if ( ! empty( $value['target'] ) ) {
			$value['target'] = $this->validate_target( $value['target'], $options );
		} elseif ( ! isset( $value['target'] ) && 1 == pods_var( self::$type . '_new_window', $options ) ) {
			$value['target'] = '_blank';
		}

		return $value;

	}

	/**
	 * Validate an URL with the options
	 *
	 * @see This method is places in the "website" field in Pods 2.7
	 *
	 * @param string $value
	 * @param array $options
	 *
	 * @since 2.7
	 */
	public function validate_url( $value, $options = null ) {
		if ( empty( $value ) )
			return $value;

		if ( 'none' != pods_var( self::$type . '_format', $options ) ) {
			if ( is_array( $value ) ) {
				if ( isset( $value[ 'scheme' ] ) )
					$value = $this->build_url( $value );
				else
					$value = implode( '', $value );
			}

			if ( false === strpos( $value, '://' ) && 0 !== strpos( $value, '//' ) )
				$value = 'http://' . $value;

			$url = @parse_url( $value );

			if ( empty( $url ) || count( $url ) < 2 )
				$value = '';
			else {
				$defaults = array(
					'scheme' => 'http',
					'host' => '',
					'path' => '/',
					'query' => '',
					'fragment' => ''
				);

				$url = array_merge( $defaults, $url );

				if ( 'normal' == pods_var( self::$type . '_format', $options ) )
					$value = $this->build_url( $url );
				elseif ( 'no-www' == pods_var( self::$type . '_format', $options ) ) {
					if ( 0 === strpos( $url[ 'host' ], 'www.' ) )
						$url[ 'host' ] = substr( $url[ 'host' ], 4 );

					$value = $this->build_url( $url );
				}
				elseif ( 'force-www' == pods_var( self::$type . '_format', $options ) ) {
					if ( false !== strpos( $url[ 'host' ], '.' ) && false === strpos( $url[ 'host' ], '.', 1 ) )
						$url[ 'host' ] = 'www.' . $url[ 'host' ];

					$value = $this->build_url( $url );
				}
				elseif ( 'no-http' == pods_var( self::$type . '_format', $options ) ) {
					$value = $this->build_url( $url );
					$value = str_replace( trim( $url[ 'scheme' ] . '://', ':' ), '', $value );

					if ( '/' == $url[ 'path' ] )
						$value = trim( $value, '/' );
				}
				elseif ( 'no-http-no-www' == pods_var( self::$type . '_format', $options ) ) {
					if ( 0 === strpos( $url[ 'host' ], 'www.' ) )
						$url[ 'host' ] = substr( $url[ 'host' ], 4 );

					$value = $this->build_url( $url );
					$value = str_replace( trim( $url[ 'scheme' ] . '://', ':' ), '', $value );

					if ( '/' == $url[ 'path' ] )
						$value = trim( $value, '/' );
				}
				elseif ( 'no-http-force-www' == pods_var( self::$type . '_format', $options ) ) {
					if ( false !== strpos( $url[ 'host' ], '.' ) && false === strpos( $url[ 'host' ], '.', 1 ) )
						$url[ 'host' ] = 'www.' . $url[ 'host' ];

					$value = $this->build_url( $url );
					$value = str_replace( trim( $url[ 'scheme' ] . '://', ':' ), '', $value );

					if ( '/' == $url[ 'path' ] )
						$value = trim( $value, '/' );
				}
			}
		} else {
			$value = $this->strip_html( $value, $options );
		}

		$value = esc_url( $value );

		return $value;
	}

	/**
	 * Strip HTML based on options
	 *
	 * @see This method is places in the "website" field in Pods 2.7
	 *
	 * @param string $value
	 * @param array $options
	 *
	 * @return string
	 *
	 * @since 2.7
	 */
	public function strip_html ( $value, $options = null ) {
		if ( is_array( $value ) )
			$value = @implode( ' ', $value );

		$value = trim( $value );

		if ( empty( $value ) )
			return $value;

		$options = (array) $options;

		if ( 1 == pods_var( self::$type . '_allow_html', $options, 0, null, true ) ) {
			$allowed_html_tags = '';

			if ( 0 < strlen( pods_var( self::$type . '_allowed_html_tags', $options ) ) ) {
				$allowed_html_tags = explode( ' ', trim( pods_var( self::$type . '_allowed_html_tags', $options ) ) );
				$allowed_html_tags = '<' . implode( '><', $allowed_html_tags ) . '>';
			}

			if ( !empty( $allowed_html_tags ) && '<>' != $allowed_html_tags )
				$value = strip_tags( $value, $allowed_html_tags );
		}
		else
			$value = strip_tags( $value );

		return $value;
	}

	/**
	 * Validate an target attribute with the options
	 *
	 * @see This method is places in the "website" field in Pods 2.7
	 *
	 * @param string $value
	 * @param array $options
	 *
	 * @since 2.7
	 */
	public function validate_target( $value, $options = null ) {
		if ( ! empty( $value ) && $value == '_blank' ) {
			$value = '_blank';
		} else {
			$value = '';
		}
		return $value;
	}

	/**
	 * Init the editor needed for WP Link modal to work
	 */
	public function validate_link_modal() {

		static $init;

		if ( empty( $init ) ) {
			if ( ! did_action( 'wp_enqueue_editor' ) ) {
				add_action( 'shutdown', array( $this, 'add_link_modal' ) );
			}
		}

		$init = true;

	}

	/**
	 * Echo the link modal code
	 */
	public function add_link_modal() {

		if ( ! class_exists( '_WP_Editors', false ) && file_exists( ABSPATH . WPINC . '/class-wp-editor.php' ) ) {
			require_once( ABSPATH . WPINC . '/class-wp-editor.php' );
		}

		if ( class_exists( '_WP_Editors' ) && method_exists( '_WP_Editors', 'wp_link_dialog' ) ) {
			_WP_Editors::wp_link_dialog();
		} else {
			echo '<div style="display:none;">';
			wp_editor( '', 'pods-link-editor-hidden' );
			echo '</div>';
		}

	}
}
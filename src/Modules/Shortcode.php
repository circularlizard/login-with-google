<?php
/**
 * Shortcode Class.
 *
 * @package Circularlizard\OAuthLogin
 * @since 1.0.0
 */

declare(strict_types=1);

namespace Circularlizard\OAuthLogin\Modules;

use Circularlizard\OAuthLogin\Interfaces\Module as ModuleInterface;
use Circularlizard\OAuthLogin\Utils\Helper;
use Circularlizard\OAuthLogin\Utils\GoogleClient;
use Circularlizard\OAuthLogin\Utils\LoginButtonRenderer;
use function Circularlizard\OAuthLogin\plugin;

/**
 * Class Shortcode
 *
 * @package Circularlizard\OAuthLogin
 */
class Shortcode implements ModuleInterface {

	/**
	 * Shortcode tag.
	 *
	 * @var string
	 */
	const TAG = 'google_login';

	/**
	 * Redirect URL.
	 *
	 * @var string
	 */
	public $redirect_uri;

	/**
	 * Google client instance.
	 *
	 * @var GoogleClient
	 */
	private $gh_client;

	/**
	 * Assets object.
	 *
	 * @var Assets
	 */
	private $assets;

	/**
	 * Login button renderer.
	 *
	 * @var LoginButtonRenderer|null
	 */
	private $button_renderer;

	/**
	 * Set the login button renderer.
	 *
	 * @param LoginButtonRenderer $renderer Button renderer.
	 *
	 * @return void
	 */
	public function set_button_renderer( LoginButtonRenderer $renderer ): void {
		$this->button_renderer = $renderer;
	}

	/**
	 * Shortcode constructor.
	 *
	 * @param GoogleClient $client GH Client object.
	 * @param Assets       $assets Assets object.
	 */
	public function __construct( GoogleClient $client, Assets $assets ) {
		$this->gh_client = $client;
		$this->assets    = $assets;
	}

	/**
	 * Module name.
	 *
	 * @return string
	 */
	public function name(): string {
		return 'shortcode';
	}

	/**
	 * Initialization actions.
	 */
	public function init(): void {
		add_shortcode( self::TAG, array( $this, 'callback' ) );

		/**
		 * Actions.
		 */
		add_filter( 'do_shortcode_tag', array( $this, 'scan_shortcode' ), 10, 3 );

		/**
		 * Filters.
		 */
		// Add filters here.
	}

	/**
	 * Callback function for shortcode rendering.
	 *
	 * @param array $attrs Shortcode attributes.
	 *
	 * @return string
	 */
	public function callback( $attrs = array() ): string {
		$redirect_to = Helper::get_redirect_url();
		$attrs       = shortcode_atts(
			array(
				'button_text'   => __( 'Login with Google', 'oauth-login' ),
				'force_display' => 'no',
				'redirect_to'   => $redirect_to,
			),
			$attrs,
			self::TAG
		);

		if ( ! $this->should_display( $attrs ) ) {
			return '';
		}

		$this->redirect_uri = $attrs['redirect_to'];

		add_filter( 'rtcamp.google_redirect_url', array( $this, 'redirect_url' ) );

		Helper::set_redirect_state_filter( $this->redirect_uri );

		$attrs['login_url'] = $this->gh_client->authorization_url();

		Helper::remove_redirect_state_filter();

		remove_filter( 'rtcamp.google_redirect_url', array( $this, 'redirect_url' ) );

		// Use multi-provider renderer if available.
		if ( null !== $this->button_renderer ) {
			return $this->button_renderer->render( false );
		}

		// Fallback to legacy Google-only button.
		$template = trailingslashit( plugin()->template_dir ) . 'google-login-button.php';

		return Helper::render_template( $template, $attrs, false );
	}

	/**
	 * Check if the current single post or page contains
	 * shortcode. If it does, enqueue the relevant style.
	 *
	 * @param string       $output Shortcode output.
	 * @param string       $tag Shortcode tag being processed.
	 * @param array|string $attrs Shortcode attributes.
	 *
	 * @return string
	 */
	public function scan_shortcode( string $output, string $tag, $attrs ): string {
		if ( ( ! is_single() && ! is_page() ) || self::TAG !== $tag || ! $this->should_display( (array) $attrs ) ) {
			return $output;
		}

		$this->assets->enqueue_login_styles();

		return $output;
	}


	/**
	 * Filter redirect URL as per shortcode param.
	 *
	 * @param string $url Login URL.
	 *
	 * @return string
	 */
	public function redirect_url( string $url ): string {

		return remove_query_arg( 'redirect_to', $url );
	}

	/**
	 * Determines whether to process the shortcode.
	 *
	 * @param array $attrs Shortcode attributes.
	 *
	 * @return bool
	 */
	private function should_display( array $attrs ): bool {
		if ( ! is_user_logged_in() || ( ! empty( $attrs['force_display'] ) && 'yes' === (string) $attrs['force_display'] ) ) {
			return true;
		}

		return false;
	}
}

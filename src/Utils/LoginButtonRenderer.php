<?php
/**
 * Login Button Renderer.
 *
 * Renders login buttons for all enabled OAuth providers.
 *
 * @package Circularlizard\OAuthLogin
 * @since 2.1.0
 */

declare(strict_types=1);

namespace Circularlizard\OAuthLogin\Utils;

use Circularlizard\OAuthLogin\Interfaces\OAuthProvider;
use Circularlizard\OAuthLogin\Utils\OAuthState;
use function Circularlizard\OAuthLogin\plugin;

/**
 * Class LoginButtonRenderer
 *
 * @package Circularlizard\OAuthLogin\Utils
 */
class LoginButtonRenderer {

	/**
	 * Provider registry.
	 *
	 * @var ProviderRegistry
	 */
	private ProviderRegistry $registry;

	/**
	 * Settings module.
	 *
	 * @var object
	 */
	private $settings;

	/**
	 * LoginButtonRenderer constructor.
	 *
	 * @param ProviderRegistry $registry Provider registry.
	 * @param object           $settings Settings module.
	 */
	public function __construct( ProviderRegistry $registry, $settings ) {
		$this->registry = $registry;
		$this->settings = $settings;
	}

	/**
	 * Render login buttons for all enabled providers.
	 *
	 * @param bool $echo Whether to echo or return the output.
	 *
	 * @return string HTML output.
	 */
	public function render( bool $echo = true ): string { // phpcs:ignore Universal.NamingConventions.NoReservedKeywordParameterNames.echoFound
		$providers = $this->get_ordered_enabled_providers();

		if ( empty( $providers ) ) {
			return '';
		}

		ob_start();
		?>
		<div class="oauth-login-separator">
			<span><?php esc_html_e( 'or', 'oauth-login' ); ?></span>
		</div>
		<div class="oauth-login-buttons">
			<?php foreach ( $providers as $provider ) : ?>
				<?php $this->render_provider_button( $provider ); ?>
			<?php endforeach; ?>
		</div>
		<?php
		$output = ob_get_clean();

		if ( $echo ) {
			echo $output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		return $output;
	}

	/**
	 * Render a single provider button.
	 *
	 * @param OAuthProvider $provider Provider instance.
	 *
	 * @return void
	 */
	private function render_provider_button( OAuthProvider $provider ): void {
		$provider_id = $provider->get_provider_id();
		$login_url   = $this->get_authorization_url( $provider );
		$button_text = $this->get_button_text( $provider );
		$icon_url    = $this->get_button_icon( $provider );
		$styles      = $this->get_button_styles( $provider );
		$style_attr  = $this->build_style_attribute( $styles );
		$hover_style = $this->build_hover_style( $provider_id, $styles );

		if ( empty( $login_url ) ) {
			return;
		}
		?>
		<?php if ( ! empty( $hover_style ) ) : ?>
			<style><?php echo esc_html( wp_strip_all_tags( $hover_style ) ); ?></style>
		<?php endif; ?>
		<div class="oauth-login-button-container">
			<a class="oauth-login-button oauth-login-button--<?php echo esc_attr( $provider_id ); ?>"
				href="<?php echo esc_url( $login_url ); ?>"
				style="<?php echo esc_attr( $style_attr ); ?>">
				<?php if ( ! empty( $icon_url ) ) : ?>
					<img class="oauth-login-button__icon" src="<?php echo esc_url( $icon_url ); ?>" alt="<?php echo esc_attr( $provider->get_provider_name() ); ?>" />
				<?php else : ?>
					<span class="oauth-login-button__icon oauth-login-button__icon--<?php echo esc_attr( $provider_id ); ?>"></span>
				<?php endif; ?>
				<span class="oauth-login-button__text"><?php echo esc_html( $button_text ); ?></span>
			</a>
		</div>
		<?php
	}

	/**
	 * Get the authorization URL for a provider.
	 *
	 * @param OAuthProvider $provider Provider instance.
	 *
	 * @return string Authorization URL.
	 */
	private function get_authorization_url( OAuthProvider $provider ): string {
		$provider_id  = $provider->get_provider_id();
		$callback_url = $provider->get_callback_url();

		$state_data = [
			'nonce'    => wp_create_nonce( 'oauth_login_' . $provider_id ),
			'provider' => $provider_id,
		];

		/**
		 * Filter the OAuth login state data.
		 *
		 * @param array  $state_data   State data.
		 * @param string $provider_id  Provider ID.
		 */
		$state_data = apply_filters( 'oauth.login_state', $state_data, $provider_id );

		$args = [
			'client_id'     => $provider->get_client_id(),
			'redirect_uri'  => $callback_url,
			'state'         => OAuthState::encode( $state_data ),
			'scope'         => implode( ' ', $provider->get_scopes() ),
			'response_type' => 'code',
		];

		/**
		 * Filter the OAuth authorization arguments.
		 *
		 * @param array         $args     Authorization arguments.
		 * @param OAuthProvider $provider Provider instance.
		 */
		$args = apply_filters( 'oauth.authorization_args', $args, $provider );

		return $provider->get_authorize_url() . '?' . http_build_query( $args );
	}

	/**
	 * Get button text for a provider, with settings override.
	 *
	 * @param OAuthProvider $provider Provider instance.
	 *
	 * @return string Button text.
	 */
	private function get_button_text( OAuthProvider $provider ): string {
		$provider_id   = $provider->get_provider_id();
		$settings_text = $this->settings->get_provider_setting( $provider_id, 'button_text', '' );

		if ( ! empty( $settings_text ) ) {
			return $settings_text;
		}

		return $provider->get_button_text();
	}

	/**
	 * Get button icon for a provider, with settings override.
	 *
	 * @param OAuthProvider $provider Provider instance.
	 *
	 * @return string Icon URL.
	 */
	private function get_button_icon( OAuthProvider $provider ): string {
		$provider_id   = $provider->get_provider_id();
		$settings_icon = $this->settings->get_provider_setting( $provider_id, 'button_icon', '' );

		if ( ! empty( $settings_icon ) ) {
			return $settings_icon;
		}

		return $provider->get_button_icon();
	}

	/**
	 * Get button styles for a provider, with settings override.
	 *
	 * @param OAuthProvider $provider Provider instance.
	 *
	 * @return array Button styles.
	 */
	private function get_button_styles( OAuthProvider $provider ): array {
		$provider_id     = $provider->get_provider_id();
		$default_styles  = $provider->get_button_styles();
		$settings_styles = $this->settings->get_provider_setting( $provider_id, 'button_styles', [] );

		if ( is_array( $settings_styles ) && ! empty( $settings_styles ) ) {
			// Only override non-empty values from settings.
			$merged = $default_styles;
			foreach ( $settings_styles as $key => $value ) {
				if ( ! empty( $value ) ) {
					$merged[ $key ] = $value;
				}
			}
			return $merged;
		}

		return $default_styles;
	}

	/**
	 * Build inline style attribute from styles array.
	 *
	 * @param array $styles Styles array.
	 *
	 * @return string CSS style string.
	 */
	private function build_style_attribute( array $styles ): string {
		$css = [];

		if ( ! empty( $styles['background_color'] ) ) {
			$css[] = 'background-color:' . $styles['background_color'];
		}
		if ( ! empty( $styles['text_color'] ) ) {
			$css[] = 'color:' . $styles['text_color'];
		}
		if ( ! empty( $styles['border_color'] ) && ! empty( $styles['border_width'] ) ) {
			$css[] = 'border:solid ' . $styles['border_color'];
			$css[] = 'border-width:' . $styles['border_width'] . ' ' . $styles['border_width'] . ' calc(' . $styles['border_width'] . ' + 1px)';
		}
		if ( ! empty( $styles['border_radius'] ) ) {
			$css[] = 'border-radius:' . $styles['border_radius'];
		}
		if ( ! empty( $styles['padding'] ) ) {
			$css[] = 'padding:' . $styles['padding'];
		}
		if ( ! empty( $styles['font_size'] ) ) {
			$css[] = 'font-size:' . $styles['font_size'];
		}

		return implode( ';', $css );
	}

	/**
	 * Build hover style CSS for a provider button.
	 *
	 * @param string $provider_id Provider ID.
	 * @param array  $styles      Styles array.
	 *
	 * @return string CSS rule string.
	 */
	private function build_hover_style( string $provider_id, array $styles ): string {
		$hover_css = [];

		if ( ! empty( $styles['hover_background_color'] ) ) {
			$hover_css[] = 'background-color:' . $styles['hover_background_color'];
		}
		if ( ! empty( $styles['hover_text_color'] ) ) {
			$hover_css[] = 'color:' . $styles['hover_text_color'];
		}
		if ( ! empty( $styles['hover_border_color'] ) ) {
			$hover_css[] = 'border-color:' . $styles['hover_border_color'];
		}

		if ( empty( $hover_css ) ) {
			return '';
		}

		return '.oauth-login-button--' . esc_attr( $provider_id ) . ':hover{' . implode( ';', $hover_css ) . '}';
	}

	/**
	 * Get enabled providers in configured order.
	 *
	 * @return OAuthProvider[] Ordered list of enabled providers.
	 */
	private function get_ordered_enabled_providers(): array {
		$all_providers = $this->registry->get_all();
		$enabled       = [];

		foreach ( $all_providers as $provider_id => $provider ) {
			if ( $this->settings->is_provider_enabled( $provider_id ) ) {
				$enabled[ $provider_id ] = $provider;
			}
		}

		if ( empty( $enabled ) ) {
			return [];
		}

		// Get configured order.
		$order = $this->settings->get_provider_order();

		if ( empty( $order ) ) {
			return array_values( $enabled );
		}

		$ordered = [];

		// Add providers in configured order.
		foreach ( $order as $provider_id ) {
			if ( isset( $enabled[ $provider_id ] ) ) {
				$ordered[] = $enabled[ $provider_id ];
				unset( $enabled[ $provider_id ] );
			}
		}

		// Append any remaining providers not in the order list.
		foreach ( $enabled as $provider ) {
			$ordered[] = $provider;
		}

		return $ordered;
	}
}

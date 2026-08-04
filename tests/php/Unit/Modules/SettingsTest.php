<?php
/**
 * Test settings module class.
 */

declare( strict_types=1 );

namespace Circularlizard\OAuthLogin\Tests\Unit\Modules;

use WP_Mock;
use Circularlizard\OAuthLogin\Interfaces\Module as ModuleInterface;
use Circularlizard\OAuthLogin\Interfaces\OAuthProvider;
use Circularlizard\OAuthLogin\Tests\TestCase;
use Circularlizard\OAuthLogin\Modules\Settings as Testee;
use Circularlizard\OAuthLogin\Utils\ProviderRegistry;

/**
 * Class SettingsTest
 *
 * @coversDefaultClass \Circularlizard\OAuthLogin\Modules\Settings
 *
 * @package Circularlizard\OAuthLogin\Tests\Unit\Modules
 */
class SettingsTest extends TestCase {
	/**
	 * Object in test.
	 *
	 * @var Testee
	 */
	private $testee;

	/**
	 * Run before each test.
	 *
	 * @return void
	 */
	public function setUp(): void {
		$this->testee = new Testee();
	}

	/**
	 * @covers ::name
	 */
	public function testName() {
		$this->assertSame( 'settings', $this->testee->name() );
	}

	public function testImplementsModuleInterface() {
		$this->assertTrue( $this->testee instanceof ModuleInterface );
	}

	/**
	 * @covers ::__get
	 */
	public function testGetWithNull() {
		$value = $this->testee->__get( 'some_test_property' );
		$this->assertEquals( null, $value );
	}

	/**
	 * @covers ::__get
	 */
	public function testGetWithProper() {
		$this->wpMockFunction(
			'get_option',
			[
				'wp_google_login_settings',
				[]
			],
			1,
			[
				'client_id' => 'cid'
			]
		);

		$this->wpMockFunction(
			'get_option',
			[
				'wp_oauth_login_settings',
				[]
			],
			1,
			[]
		);

		WP_Mock::expectActionAdded( 'admin_init', [ $this->testee, 'register_settings' ] );
		WP_Mock::expectActionAdded( 'admin_menu', [ $this->testee, 'settings_page' ] );

		$this->testee->init();
		$value = $this->testee->__get( 'client_id' );
		$this->assertEquals( 'cid', $value );
	}

	/**
	 * @covers ::init
	 */
	public function testInit() {
		$this->wpMockFunction(
			'get_option',
			[
				'wp_google_login_settings',
				[]
			],
			1,
			[]
		);

		$this->wpMockFunction(
			'get_option',
			[
				'wp_oauth_login_settings',
				[]
			],
			1,
			[]
		);

		WP_Mock::expectActionAdded( 'admin_init', [ $this->testee, 'register_settings' ] );
		WP_Mock::expectActionAdded( 'admin_menu', [ $this->testee, 'settings_page' ] );

		$this->testee->init();
		$this->assertConditionsMet();
	}

	/**
	 * @covers ::register_settings
	 */
	public function testRegisterSettings() {
		// Expect both old and new settings to be registered.
		WP_Mock::userFunction(
			'register_setting',
			[
				'times' => 2,
			]
		);

		// Providers + General = 2 sections (One Tap not added without Google enabled).
		WP_Mock::userFunction(
			'add_settings_section',
			[
				'args'  => [
					\WP_Mock\Functions::type( 'string' ),
					\WP_Mock\Functions::type( 'string' ),
					\WP_Mock\Functions::type( 'callable' ),
					\WP_Mock\Functions::type( 'string' ),
				],
				'times' => 2
			]
		);

		// 3 general fields (registration, whitelisted domains, and login message).
		WP_Mock::userFunction(
			'add_settings_field',
			[
				'times' => 3
			]
		);

		$this->testee->register_settings();
		$this->assertConditionsMet();
	}

	/**
	 * @covers ::register_settings
	 * @covers ::register_provider_settings
	 */
	public function testRegisterSettingsWithRegistry() {
		// Create mock provider.
		$provider = \Mockery::mock( OAuthProvider::class );
		$provider->shouldReceive( 'get_provider_id' )->andReturn( 'test_provider' );
		$provider->shouldReceive( 'get_provider_name' )->andReturn( 'Test Provider' );

		$registry = new ProviderRegistry();
		$registry->register( $provider );

		$this->testee->set_registry( $registry );

		WP_Mock::userFunction(
			'register_setting',
			[
				'times' => 2,
			]
		);

		// Providers + General = 2 sections (One Tap not added without Google enabled).
		WP_Mock::userFunction(
			'add_settings_section',
			[
				'times' => 2,
			]
		);

		// 3 general fields (registration, whitelisted domains, and login message).
		WP_Mock::userFunction(
			'add_settings_field',
			[
				'times' => 3,
			]
		);

		$this->testee->register_settings();
		$this->assertConditionsMet();
	}

	/**
	 * @covers ::settings_page
	 */
	public function testSettingsPage() {
		$this->wpMockFunction(
			'add_options_page',
			[
				'OAuth Login Settings',
				'OAuth Login',
				'manage_options',
				'oauth-login',
				[
					$this->testee,
					'output'
				],
			]
		);

		$this->testee->settings_page();
		$this->assertConditionsMet();
	}

	/**
	 * @covers ::output
	 */
	public function testOutput() {
		WP_Mock::userFunction(
			'esc_html_e',
			[
				'times' => 1,
			]
		);

		$this->wpMockFunction(
			'settings_fields',
			[
				'wp_oauth_login',
			],
			1
		);

		$this->wpMockFunction(
			'do_settings_sections',
			[
				'oauth-login',
			],
			1
		);

		$this->wpMockFunction(
			'submit_button',
			[],
			1,
			''
		);

		$this->setOutputCallback(function() {});
		$this->testee->output();
		$this->assertConditionsMet();
	}

	/**
	 * @covers ::set_registry
	 * @covers ::get_registry
	 */
	public function testSetAndGetRegistry() {
		$registry = new ProviderRegistry();

		$this->assertNull( $this->testee->get_registry() );

		$this->testee->set_registry( $registry );

		$this->assertSame( $registry, $this->testee->get_registry() );
	}

	/**
	 * @covers ::get_provider_setting
	 * @covers ::get_provider_settings
	 */
	public function testGetProviderSettings() {
		$this->wpMockFunction(
			'get_option',
			[
				'wp_google_login_settings',
				[]
			],
			1,
			[]
		);

		$this->wpMockFunction(
			'get_option',
			[
				'wp_oauth_login_settings',
				[]
			],
			1,
			[
				'providers' => [
					'google' => [
						'client_id'     => 'test-client-id',
						'client_secret' => 'test-secret',
						'enabled'       => true,
					],
				],
			]
		);

		WP_Mock::expectActionAdded( 'admin_init', [ $this->testee, 'register_settings' ] );
		WP_Mock::expectActionAdded( 'admin_menu', [ $this->testee, 'settings_page' ] );

		$this->testee->init();

		$this->assertEquals( 'test-client-id', $this->testee->get_provider_setting( 'google', 'client_id' ) );
		$this->assertEquals( 'test-secret', $this->testee->get_provider_setting( 'google', 'client_secret' ) );
		$this->assertEquals( 'default', $this->testee->get_provider_setting( 'google', 'nonexistent', 'default' ) );

		$settings = $this->testee->get_provider_settings( 'google' );
		$this->assertArrayHasKey( 'client_id', $settings );
		$this->assertArrayHasKey( 'client_secret', $settings );
	}

	/**
	 * @covers ::is_provider_enabled
	 */
	public function testIsProviderEnabled() {
		$this->wpMockFunction(
			'get_option',
			[
				'wp_google_login_settings',
				[]
			],
			1,
			[]
		);

		$this->wpMockFunction(
			'get_option',
			[
				'wp_oauth_login_settings',
				[]
			],
			1,
			[
				'providers' => [
					'google' => [
						'client_id'     => 'test-id',
						'client_secret' => 'test-secret',
						'enabled'       => true,
					],
					'disabled_provider' => [
						'client_id'     => 'test-id',
						'client_secret' => 'test-secret',
						'enabled'       => false,
					],
					'incomplete_provider' => [
						'client_id' => '',
						'enabled'   => true,
					],
				],
			]
		);

		WP_Mock::expectActionAdded( 'admin_init', [ $this->testee, 'register_settings' ] );
		WP_Mock::expectActionAdded( 'admin_menu', [ $this->testee, 'settings_page' ] );

		$this->testee->init();

		$this->assertTrue( $this->testee->is_provider_enabled( 'google' ) );
		$this->assertFalse( $this->testee->is_provider_enabled( 'disabled_provider' ) );
		$this->assertFalse( $this->testee->is_provider_enabled( 'incomplete_provider' ) );
		$this->assertFalse( $this->testee->is_provider_enabled( 'nonexistent' ) );
	}

	/**
	 * @covers ::sanitize_settings
	 */
	public function testSanitizeSettings() {
		WP_Mock::userFunction(
			'sanitize_key',
			[
				'return_arg' => 0,
			]
		);

		WP_Mock::userFunction(
			'sanitize_text_field',
			[
				'return_arg' => 0,
			]
		);

		WP_Mock::userFunction(
			'wp_unslash',
			[
				'return_arg' => 0,
			]
		);

		WP_Mock::userFunction(
			'wp_salt',
			[
				'return' => 'test-salt-key',
			]
		);

		$input = [
			'providers' => [
				'google' => [
					'enabled'       => '1',
					'client_id'     => 'my-client-id',
					'client_secret' => 'my-secret',
				],
			],
		];

		$result = $this->testee->sanitize_settings( $input );

		$this->assertTrue( $result['providers']['google']['enabled'] );
		$this->assertEquals( 'my-client-id', $result['providers']['google']['client_id'] );
		// Client secret should be encrypted with 'enc:' prefix.
		$this->assertStringStartsWith( 'enc:', $result['providers']['google']['client_secret'] );
	}

	/**
	 * @covers ::sanitize_settings
	 */
	public function testSanitizeSettingsWithCustomProviders() {
		WP_Mock::userFunction(
			'sanitize_key',
			[
				'return_arg' => 0,
			]
		);

		WP_Mock::userFunction(
			'sanitize_text_field',
			[
				'return_arg' => 0,
			]
		);

		WP_Mock::userFunction(
			'wp_unslash',
			[
				'return_arg' => 0,
			]
		);

		WP_Mock::userFunction(
			'esc_url_raw',
			[
				'return_arg' => 0,
			]
		);

		WP_Mock::userFunction(
			'wp_parse_url',
			[
				'return' => function( $url ) {
					return parse_url( $url );
				},
			]
		);

		WP_Mock::userFunction(
			'wp_salt',
			[
				'return' => 'test-salt-key',
			]
		);

		$input = [
			'providers' => [
				'github' => [
					'name'          => 'GitHub',
					'authorize_url' => 'https://github.com/login/oauth/authorize',
					'token_url'     => 'https://github.com/login/oauth/access_token',
					'user_info_url' => 'https://api.github.com/user',
					'scopes'        => 'user:email',
					'client_id'     => 'github-client-id',
					'client_secret' => 'github-secret',
				],
			],
			'new_provider' => [
			],
			'new_provider' => [
				'slug'          => 'facebook',
				'name'          => 'Facebook',
				'authorize_url' => 'https://www.facebook.com/v12.0/dialog/oauth',
				'token_url'     => 'https://graph.facebook.com/v12.0/oauth/access_token',
				'user_info_url' => 'https://graph.facebook.com/me',
				'scopes'        => 'email,public_profile',
				'client_id'     => 'fb-client-id',
				'client_secret' => 'fb-secret',
			],
		];

		$result = $this->testee->sanitize_settings( $input );

		// Check existing provider.
		$this->assertArrayHasKey( 'github', $result['providers'] );
		$this->assertEquals( 'GitHub', $result['providers']['github']['name'] );
		$this->assertEquals( 'https://github.com/login/oauth/authorize', $result['providers']['github']['authorize_url'] );

		// Check new provider was added.
		$this->assertArrayHasKey( 'facebook', $result['providers'] );
		$this->assertEquals( 'Facebook', $result['providers']['facebook']['name'] );
	}

	/**
	 * @covers ::sanitize_settings
	 */
	public function testSanitizeSettingsDeletesMarkedProviders() {
		WP_Mock::userFunction(
			'sanitize_key',
			[
				'return_arg' => 0,
			]
		);

		WP_Mock::userFunction(
			'sanitize_text_field',
			[
				'return_arg' => 0,
			]
		);

		WP_Mock::userFunction(
			'wp_unslash',
			[
				'return_arg' => 0,
			]
		);

		WP_Mock::userFunction(
			'esc_url_raw',
			[
				'return_arg' => 0,
			]
		);

		$input = [
			'providers' => [
				'github' => [
					'name'   => 'GitHub',
					'delete' => '1',
				],
				'facebook' => [
					'name' => 'Facebook',
				],
			],
		];

		$result = $this->testee->sanitize_settings( $input );

		// GitHub should be deleted.
		$this->assertArrayNotHasKey( 'github', $result['providers'] ?? [] );
		// Facebook should remain.
		$this->assertArrayHasKey( 'facebook', $result['providers'] );
	}

	/**
	 * @covers ::client_id_field
	 */
	public function testClientIdField() {
		WP_Mock::userFunction(
			'esc_attr',
			[
				'times'      => 1,
				'return_arg' => 0,
			]
		);

		WP_Mock::userFunction(
			'esc_html__',
			[
				'times'  => 1,
				'return' => 'Create oAuth Client ID and Client Secret at',
			]
		);

		WP_Mock::userFunction(
			'wp_kses_post',
			[
				'times'      => 1,
				'return_arg' => 0,
			]
		);

		$this->setOutputCallback(function() {});
		$this->testee->client_id_field();
		$this->assertConditionsMet();
	}

	/**
	 * @covers ::user_registration
	 */
	public function testUserRegistration() {
		$this->testee->registration_enabled = 'yes';

		WP_Mock::userFunction(
			'checked',
			[
				'times'  => 1,
				'return' => 'checked',
			]
		);

		WP_Mock::userFunction(
			'esc_attr',
			[
				'times'      => 2,
				'return_arg' => 0,
			]
		);

		WP_Mock::userFunction(
			'esc_html_e',
			[
				'times'  => 1,
				'return' => '',
			]
		);

		$this->wpMockFunction(
			'is_multisite',
			[],
			1,
			true
		);

		WP_Mock::userFunction(
			'wp_kses_post',
			[
				'times'      => 1,
				'return_arg' => 0,
			]
		);

		$this->setOutputCallback(function() {});
		$this->testee->user_registration();
		$this->assertConditionsMet();
	}

	/**
	 * @covers ::whitelisted_domains
	 */
	public function testWhitelistedDomains() {
		$this->testee->whitelisted_domains = 'https://example1.com,https://example2.com';

		WP_Mock::userFunction(
			'esc_attr',
			[
				'times'      => 1,
				'return_arg' => 0,
			]
		);

		WP_Mock::userFunction(
			'esc_html',
			[
				'times'      => 1,
				'return_arg' => 0,
			]
		);

		$this->setOutputCallback(function() {});
		$this->testee->whitelisted_domains();
		$this->assertConditionsMet();
	}

	/**
	 * @covers ::client_secret_field
	 */
	public function testClientSecretField() {
		$this->testee->client_secret = 'cis';
		WP_Mock::userFunction(
			'esc_attr',
			[
				'times'      => 1,
				'return_arg' => 0,
			]
		);

		$this->setOutputCallback(function() {});
		$this->testee->client_secret_field();
		$this->assertConditionsMet();
	}

}

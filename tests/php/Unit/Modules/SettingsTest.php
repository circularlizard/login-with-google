<?php
/**
 * Test settings module class.
 */

declare( strict_types=1 );

namespace Circularlizard\OAuthLogin\Tests\Unit\Modules;

use WP_Mock;
use Circularlizard\OAuthLogin\Interfaces\Module as ModuleInterface;
use Circularlizard\OAuthLogin\Tests\TestCase;
use Circularlizard\OAuthLogin\Modules\Settings as Testee;

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

		WP_Mock::expectActionAdded( 'admin_init', [ $this->testee, 'register_settings' ] );
		WP_Mock::expectActionAdded( 'admin_menu', [ $this->testee, 'settings_page' ] );

		$this->testee->init();
		$this->assertConditionsMet();
	}

	/**
	 * @covers ::register_settings
	 */
	public function testRegisterSettings() {
		$this->wpMockFunction(
			'register_setting',
			[
				'wp_google_login',
				'wp_google_login_settings'
			],
			1,
			true
		);

		$this->wpMockFunction(
			'add_settings_section',
			[
				'wp_google_login_section',
				'Log in with Google Settings',
				\Closure::class,
				'oauth-login'
			],
			1
		);

		WP_Mock::userFunction(
			'add_settings_field',
			[
				'args'  => [
					\WP_Mock\Functions::type( 'string' ),
					\WP_Mock\Functions::type( 'string' ),
					\WP_Mock\Functions::type( 'callable' ),
					\WP_Mock\Functions::type( 'string' ),
					\WP_Mock\Functions::type( 'string' ),
					\WP_Mock\Functions::type( 'array' ),
				],
				'times' => 6
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
				'Login with Google settings',
				'Login with Google',
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
		$this->wpMockFunction(
			'settings_fields',
			[
				'wp_google_login',
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

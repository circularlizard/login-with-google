<?php
/**
 * Test Block module class.
 */

declare( strict_types=1 );

namespace Circularlizard\OAuthLogin\Tests\Unit\Modules;

use Circularlizard\OAuthLogin\Interfaces\Module as ModuleInterface;
use Circularlizard\OAuthLogin\Utils\Helper;
use WP_Mock;
use Mockery;
use Circularlizard\OAuthLogin\Modules\Block as Testee;
use Circularlizard\OAuthLogin\Tests\TestCase;
use Circularlizard\OAuthLogin\Utils\GoogleClient;
use Circularlizard\OAuthLogin\Modules\Assets;

/**
 * Class BlockTest
 *
 * @coversDefaultClass \Circularlizard\OAuthLogin\Modules\Block
 *
 * @package Circularlizard\OAuthLogin\Tests\Unit\Modules
 */
class BlockTest extends TestCase {
	/**
	 * @var GoogleClient
	 */
	private $ghClientMock;

	/**
	 * @var Assets
	 */
	private $assetMock;

	/**
	 * @var Testee
	 */
	private $testee;

	/**
	 * Run before each test.
	 *
	 * @return void
	 */
	public function setUp(): void {
		$this->ghClientMock = $this->createMock( GoogleClient::class );
		$this->assetMock       = $this->createMock( Assets::class );
		$this->testee           = new Testee( $this->assetMock, $this->ghClientMock );
	}

	public function tearDown(): void {
		parent::tearDown();
		$this->ghClientMock = null;
		$this->assetMock       = null;
		unset( $this->testee );
	}

	/**
	 * @covers ::name
	 */
	public function testName() {
		$this->assertSame( 'google_login_block', $this->testee->name() );
	}

	public function testImplementsModuleInterface() {
		$this->assertTrue( $this->testee instanceof ModuleInterface );
	}

	/**
	 * @covers ::init
	 */
	public function testInit() {
		WP_Mock::expectActionAdded( 'init', [ $this->testee, 'register' ] );

		$this->testee->init();
		$this->assertConditionsMet();
	}

	/**
	 * @covers ::register
	 */
	public function testRegister() {
		$path = '/test/assets/';

		$this->wpMockFunction(
			'Circularlizard\OAuthLogin\plugin',
			[],
			3,
			(object) [
				'assets_dir' => $path,
			]
		);

		WP_Mock::userFunction(
			'trailingslashit',
			[
				'return_arg' => 0,
			]
		);

		WP_Mock::userFunction(
			'wp_register_block_metadata_collection',
			[
				'return' => true,
			]
		);

		WP_Mock::userFunction(
			'register_block_type',
			[
				'times'  => 1,
				'return' => true,
			]
		);

		$this->testee->register();

		$this->assertConditionsMet();
	}


	/**
	 * @covers ::render_login_button, ::markup
	 */
	public function testRenderLoginButton() {
		$mockAttributes = [
			'login_url'       => '#',
			'custom_btn_text' => 'test',
			'force_display'   => false,
		];

		$this->wpMockFunction(
			'is_user_logged_in',
			[],
			1,
			false
		);

		$this->wpMockFunction(
			'wp_parse_args',
			[],
			1,
			$mockAttributes
		);

		$this->wpMockFunction(
			'wp_kses_post',
			[],
			1,
			''
		);

		$path = dirname( __DIR__, 4 ) . '/templates/';

		$this->wpMockFunction(
			'Circularlizard\OAuthLogin\plugin',
			[],
			1,
			function () use ( $path ) {
				return (object) [
					'template_dir' => $path,
				];
			}
		);

		WP_Mock::userFunction(
			'trailingslashit',
			[
				'times'      => 1,
				'args'       => [ $path ],
				'return_arg' => 0,
			]
		);


		$helperMock = \Mockery::mock( 'alias:' . Helper::class );
		$helperMock->expects( 'get_redirect_url' )->once()->andReturn( 'https://example.com/' );
		$helperMock->expects( 'set_redirect_state_filter' )->once();
		$helperMock->expects( 'render_template' )->once()->withArgs(
			[
				$path . 'google-login-button.php',
				\Mockery::type( 'array' ),
				false,
			]
		)->andReturn( '' );

		$this->ghClientMock->expects( $this->once() )
		                   ->method( 'authorization_url' )
		                   ->willReturn( 'https://google.com/auth/' );

		$markup = $this->testee->render_login_button(
			[
				'buttonText'   => 'test',
				'forceDisplay' => false,
			]
		);

		$this->assertConditionsMet();
	}

	/**
	 * @covers ::render_login_button, ::markup
	 */
	public function testRenderLogoutButton() {
		$mockAttributes = [
			'login_url'       => '#',
			'custom_btn_text' => 'test',
			'force_display'   => true,
		];

		$this->wpMockFunction(
			'wp_parse_args',
			[],
			1,
			$mockAttributes
		);

		$this->wpMockFunction(
			'wp_kses_post',
			[],
			1,
			''
		);

		$path = dirname( __DIR__, 4 ) . '/templates/';

		$this->wpMockFunction(
			'Circularlizard\OAuthLogin\plugin',
			[],
			1,
			function () use ( $path ) {
				return (object) [
					'template_dir' => $path,
				];
			}
		);

		WP_Mock::userFunction(
			'trailingslashit',
			[
				'times'      => 1,
				'args'       => [ $path ],
				'return_arg' => 0,
			]
		);


		$helperMock = \Mockery::mock( 'alias:' . Helper::class );
		$helperMock->expects( 'get_redirect_url' )->once()->andReturn( 'https://example.com/' );
		$helperMock->expects( 'set_redirect_state_filter' )->once();
		$helperMock->expects( 'render_template' )->once()->withArgs(
			[
				$path . 'google-login-button.php',
				\Mockery::type( 'array' ),
				false,
			]
		)->andReturn( '' );

		$this->ghClientMock->expects( $this->once() )
		                   ->method( 'authorization_url' )
		                   ->willReturn( 'https://google.com/auth/' );

		$markup = $this->testee->render_login_button(
			[
				'buttonText'   => 'test',
				'forceDisplay' => true,
			]
		);

		$this->assertConditionsMet();
	}
}


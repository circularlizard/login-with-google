<?php
/**
 * Test GoogleProvider class.
 */

declare(strict_types=1);

namespace Circularlizard\OAuthLogin\Tests\Unit\Providers;

use stdClass;
use Circularlizard\OAuthLogin\Tests\TestCase;
use Circularlizard\OAuthLogin\Providers\GoogleProvider;
use Circularlizard\OAuthLogin\Interfaces\OAuthProvider;

/**
 * Class GoogleProviderTest
 *
 * @coversDefaultClass \Circularlizard\OAuthLogin\Providers\GoogleProvider
 *
 * @package Circularlizard\OAuthLogin\Tests\Unit\Providers
 */
class GoogleProviderTest extends TestCase {

	/**
	 * @var GoogleProvider
	 */
	private $testee;

	/**
	 * Run before each test.
	 *
	 * @return void
	 */
	public function setUp(): void {
		$this->testee = new GoogleProvider( 'test-client-id', 'test-client-secret' );
	}

	/**
	 * @covers ::__construct
	 */
	public function testImplementsOAuthProviderInterface() {
		$this->assertInstanceOf( OAuthProvider::class, $this->testee );
	}

	/**
	 * @covers ::get_provider_id
	 */
	public function testGetProviderId() {
		$this->assertSame( 'google', $this->testee->get_provider_id() );
	}

	/**
	 * @covers ::get_provider_name
	 */
	public function testGetProviderName() {
		$this->wpMockFunction( '__', [], 1, 'Google' );
		$this->assertSame( 'Google', $this->testee->get_provider_name() );
	}

	/**
	 * @covers ::get_authorize_url
	 */
	public function testGetAuthorizeUrl() {
		$this->assertSame(
			'https://accounts.google.com/o/oauth2/v2/auth',
			$this->testee->get_authorize_url()
		);
	}

	/**
	 * @covers ::get_token_url
	 */
	public function testGetTokenUrl() {
		$this->assertSame(
			'https://oauth2.googleapis.com/token',
			$this->testee->get_token_url()
		);
	}

	/**
	 * @covers ::get_user_info_url
	 */
	public function testGetUserInfoUrl() {
		$this->assertSame(
			'https://www.googleapis.com/oauth2/v2/userinfo',
			$this->testee->get_user_info_url()
		);
	}

	/**
	 * @covers ::get_scopes
	 */
	public function testGetScopes() {
		$scopes = $this->testee->get_scopes();

		$this->assertIsArray( $scopes );
		$this->assertContains( 'email', $scopes );
		$this->assertContains( 'profile', $scopes );
		$this->assertContains( 'openid', $scopes );
	}

	/**
	 * @covers ::get_client_id
	 */
	public function testGetClientId() {
		$this->assertSame( 'test-client-id', $this->testee->get_client_id() );
	}

	/**
	 * @covers ::get_client_secret
	 */
	public function testGetClientSecret() {
		$this->assertSame( 'test-client-secret', $this->testee->get_client_secret() );
	}

	/**
	 * @covers ::parse_user_response
	 */
	public function testParseUserResponse() {
		$response              = new stdClass();
		$response->email       = 'test@example.com';
		$response->name        = 'Test User';
		$response->given_name  = 'Test';
		$response->family_name = 'User';
		$response->picture     = 'https://example.com/photo.jpg';
		$response->locale      = 'en';

		$user = $this->testee->parse_user_response( $response );

		$this->assertSame( 'test@example.com', $user->email );
		$this->assertSame( 'Test User', $user->name );
		$this->assertSame( 'Test', $user->first_name );
		$this->assertSame( 'User', $user->last_name );
		$this->assertSame( 'https://example.com/photo.jpg', $user->picture );
		$this->assertSame( 'en', $user->locale );
		$this->assertSame( 'google', $user->provider );
	}

	/**
	 * @covers ::parse_user_response
	 */
	public function testParseUserResponseWithMissingFields() {
		$response        = new stdClass();
		$response->email = 'test@example.com';

		$user = $this->testee->parse_user_response( $response );

		$this->assertSame( 'test@example.com', $user->email );
		$this->assertSame( '', $user->name );
		$this->assertSame( '', $user->first_name );
		$this->assertSame( '', $user->last_name );
		$this->assertSame( '', $user->picture );
		$this->assertSame( '', $user->locale );
		$this->assertSame( 'google', $user->provider );
	}
}

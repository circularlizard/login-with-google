<?php
/**
 * Test ProviderRegistry class.
 */

declare(strict_types=1);

namespace Circularlizard\OAuthLogin\Tests\Unit\Utils;

use Circularlizard\OAuthLogin\Tests\TestCase;
use Circularlizard\OAuthLogin\Utils\ProviderRegistry;
use Circularlizard\OAuthLogin\Interfaces\OAuthProvider;

/**
 * Class ProviderRegistryTest
 *
 * @coversDefaultClass \Circularlizard\OAuthLogin\Utils\ProviderRegistry
 *
 * @package Circularlizard\OAuthLogin\Tests\Unit\Utils
 */
class ProviderRegistryTest extends TestCase {

	/**
	 * @var ProviderRegistry
	 */
	private $testee;

	/**
	 * Run before each test.
	 *
	 * @return void
	 */
	public function setUp(): void {
		$this->testee = new ProviderRegistry();
	}

	/**
	 * @covers ::register
	 * @covers ::get
	 */
	public function testRegisterAndGet() {
		$provider = $this->createMock( OAuthProvider::class );
		$provider->method( 'get_provider_id' )->willReturn( 'test-provider' );

		$this->testee->register( $provider );

		$this->assertSame( $provider, $this->testee->get( 'test-provider' ) );
	}

	/**
	 * @covers ::get
	 */
	public function testGetReturnsNullForUnregisteredProvider() {
		$this->assertNull( $this->testee->get( 'nonexistent' ) );
	}

	/**
	 * @covers ::get_all
	 */
	public function testGetAll() {
		$provider1 = $this->createMock( OAuthProvider::class );
		$provider1->method( 'get_provider_id' )->willReturn( 'provider1' );

		$provider2 = $this->createMock( OAuthProvider::class );
		$provider2->method( 'get_provider_id' )->willReturn( 'provider2' );

		$this->testee->register( $provider1 );
		$this->testee->register( $provider2 );

		$all = $this->testee->get_all();

		$this->assertCount( 2, $all );
		$this->assertArrayHasKey( 'provider1', $all );
		$this->assertArrayHasKey( 'provider2', $all );
	}

	/**
	 * @covers ::has
	 */
	public function testHas() {
		$provider = $this->createMock( OAuthProvider::class );
		$provider->method( 'get_provider_id' )->willReturn( 'test-provider' );

		$this->assertFalse( $this->testee->has( 'test-provider' ) );

		$this->testee->register( $provider );

		$this->assertTrue( $this->testee->has( 'test-provider' ) );
	}

	/**
	 * @covers ::get_enabled
	 */
	public function testGetEnabled() {
		$enabledProvider = $this->createMock( OAuthProvider::class );
		$enabledProvider->method( 'get_provider_id' )->willReturn( 'enabled' );
		$enabledProvider->method( 'get_client_id' )->willReturn( 'client-id' );
		$enabledProvider->method( 'get_client_secret' )->willReturn( 'client-secret' );

		$disabledProvider = $this->createMock( OAuthProvider::class );
		$disabledProvider->method( 'get_provider_id' )->willReturn( 'disabled' );
		$disabledProvider->method( 'get_client_id' )->willReturn( '' );
		$disabledProvider->method( 'get_client_secret' )->willReturn( '' );

		$this->testee->register( $enabledProvider );
		$this->testee->register( $disabledProvider );

		$enabled = $this->testee->get_enabled();

		$this->assertCount( 1, $enabled );
		$this->assertArrayHasKey( 'enabled', $enabled );
		$this->assertArrayNotHasKey( 'disabled', $enabled );
	}

	/**
	 * @covers ::get_provider_ids
	 */
	public function testGetProviderIds() {
		$provider1 = $this->createMock( OAuthProvider::class );
		$provider1->method( 'get_provider_id' )->willReturn( 'google' );

		$provider2 = $this->createMock( OAuthProvider::class );
		$provider2->method( 'get_provider_id' )->willReturn( 'github' );

		$this->testee->register( $provider1 );
		$this->testee->register( $provider2 );

		$ids = $this->testee->get_provider_ids();

		$this->assertContains( 'google', $ids );
		$this->assertContains( 'github', $ids );
	}
}

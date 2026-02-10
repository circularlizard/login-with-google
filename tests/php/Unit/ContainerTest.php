<?php
/**
 * Test settings module class.
 */

declare( strict_types=1 );

namespace Circularlizard\OAuthLogin\Tests\Unit;

use Mockery;
use InvalidArgumentException;
use Pimple\Container as PimpleContainer;
use Circularlizard\OAuthLogin\Container;
use Circularlizard\OAuthLogin\Container as Testee;
use Circularlizard\OAuthLogin\Tests\TestCase;
use Circularlizard\OAuthLogin\Interfaces\Container as ContainerInterface;

/**
 * Class ContainerTest
 *
 * @coversDefaultClass \Circularlizard\OAuthLogin\Container
 *
 * @package Circularlizard\OAuthLogin\Tests\Unit
 */
class ContainerTest extends  TestCase {

	/**
	 * @var PimpleContainer
	 */
	private $pimpleMock;

	/**
	 * Object under test.
	 *
	 * @var Container
	 */
	private $testee;

	/**
	 * @return void
	 */
	public function setUp(): void {
		$this->pimpleMock = $this->createMock( PimpleContainer::class );
		$this->testee     = new Testee( $this->pimpleMock );
	}

	public function testContainerImplementsInterface() {
		$this->assertInstanceOf( ContainerInterface::class, $this->testee );
	}

	/**
	 * @covers ::get
	 */
	public function testGetThrowsExceptionForNonExistentService() {
		$this->pimpleMock->expects( $this->once() )
		                        ->method( 'keys' )
		                        ->willReturn( [ 'example_service' ] );

		$this->expectException( InvalidArgumentException::class );
		$this->testee->get( 'non_existent_service' );
	}

	/**
	 * @covers ::get
	 */
	public function testGetReturnsServiceObject() {
		$dummyService = (object) [
			'some_key'       => 'some_value',
			'some_other_key' => 'some_other_value',
		];

		$this->testee->container['test_service'] = $dummyService;

		$this->pimpleMock->expects( $this->once() )
		                        ->method( 'keys' )
		                        ->willReturn( [ 'test_service' ] );

		$this->pimpleMock->expects( $this->once() )
		                        ->method( 'offsetGet' )
		                        ->with( 'test_service' )
		                        ->willReturn( $dummyService );

		$this->testee->get( 'test_service' );
	}

	/**
	 * @covers ::get
	 */
	public function testGetProviderRegistryService() {
		$dummyRegistry = (object) [
			'providers' => [],
		];

		$this->pimpleMock->expects( $this->once() )
		                        ->method( 'keys' )
		                        ->willReturn( [ 'provider_registry' ] );

		$this->pimpleMock->expects( $this->once() )
		                        ->method( 'offsetGet' )
		                        ->with( 'provider_registry' )
		                        ->willReturn( $dummyRegistry );

		$result = $this->testee->get( 'provider_registry' );
		$this->assertSame( $dummyRegistry, $result );
	}

	/**
	 * @covers ::get
	 */
	public function testGetGoogleOneTapLoginService() {
		$dummyOneTap = (object) [
			'name' => 'google_one_tap_login',
		];

		$this->pimpleMock->expects( $this->once() )
		                        ->method( 'keys' )
		                        ->willReturn( [ 'google_one_tap_login' ] );

		$this->pimpleMock->expects( $this->once() )
		                        ->method( 'offsetGet' )
		                        ->with( 'google_one_tap_login' )
		                        ->willReturn( $dummyOneTap );

		$result = $this->testee->get( 'google_one_tap_login' );
		$this->assertSame( $dummyOneTap, $result );
	}

	/**
	 * @covers ::get
	 */
	public function testGetGoogleTokenVerifierService() {
		$dummyVerifier = (object) [
			'name' => 'google_token_verifier',
		];

		$this->pimpleMock->expects( $this->once() )
		                        ->method( 'keys' )
		                        ->willReturn( [ 'google_token_verifier' ] );

		$this->pimpleMock->expects( $this->once() )
		                        ->method( 'offsetGet' )
		                        ->with( 'google_token_verifier' )
		                        ->willReturn( $dummyVerifier );

		$result = $this->testee->get( 'google_token_verifier' );
		$this->assertSame( $dummyVerifier, $result );
	}
}

# Testing Conventions

## Unit Tests
- Use WP_Mock for mocking WordPress functions
- Test file naming: `{ClassName}Test.php`
- One test class per source class
- Namespace: `Circularlizard\OAuthLogin\Tests\Unit\{SubNamespace}`

## Test Structure
```php
namespace Circularlizard\OAuthLogin\Tests\Unit\Providers;

use Circularlizard\OAuthLogin\Tests\TestCase;
use Circularlizard\OAuthLogin\Providers\GoogleProvider;

class GoogleProviderTest extends TestCase {
    private $testee;

    public function setUp(): void {
        $this->testee = new GoogleProvider();
    }

    public function testMethodName() {
        // Arrange
        // Act
        // Assert
    }
}
```

## Running Tests
- Unit tests: `composer tests:unit`
- PHPCS: `composer cs`
- Both: `composer qa`

## Mocking Guidelines
- Mock WordPress functions with `WP_Mock::userFunction()`
- Mock class dependencies with PHPUnit mocks or Mockery
- Always set return values to avoid PHP 8.5 strict type issues

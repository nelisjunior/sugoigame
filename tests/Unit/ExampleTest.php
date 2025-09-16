<?php
declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Exemplo de teste unitário para demonstrar a estrutura
 */
class ExampleTest extends TestCase
{
    public function testBasicAssertion(): void
    {
        $this->assertTrue(true);
        $this->assertSame(1, 1);
        $this->assertNotEmpty('Sugoi Game');
    }

    public function testPhpVersion(): void
    {
        $this->assertGreaterThanOrEqual('8.1', PHP_VERSION);
    }

    public function testRequiredExtensions(): void
    {
        $this->assertTrue(extension_loaded('mysqli'));
        $this->assertTrue(extension_loaded('curl'));
        $this->assertTrue(extension_loaded('json'));
        $this->assertTrue(extension_loaded('mbstring'));
    }
}
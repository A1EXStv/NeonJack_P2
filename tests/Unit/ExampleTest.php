<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_that_true_is_true(): void
    {
        $this->assertTrue(true);
    }

    /**
     * A basic math test example.
     */
    public function test_basic_math(): void
    {
        $a = 1;
        $b = 2;
        $this->assertEquals(3, $a + $b);
    }
}

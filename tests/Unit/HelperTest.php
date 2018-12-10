<?php

namespace Tests\Unit;

use Tests\TestCase;

require_once dirname(dirname( __DIR__)).'/src/helpers.php';

class HelperTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
    }

    public function test_is_callback_function()
    {
        $this->assertFalse(
            is_callback_function('invalid')
        );

        $this->assertTrue(
            is_callback_function(function () {
                return true;
            })
        );
    }
}

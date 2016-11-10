<?php

class DummyTest extends TestCase
{
    /** @test */
    public function it_is_dummy()
    {
        Artisan::call('icl:generic');
    }
}

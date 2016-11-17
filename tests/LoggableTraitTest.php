<?php

class LoggableTraitTest extends TestCase
{
    /** @test */
    public function it_supports_psr3_methods_for_logging()
    {
        Artisan::call('generic');

        $this->assertLogFileContains("generic/{$this->date}.log", [
            '[%datetime%]: [DEBUG]: Debug!',
            '[%datetime%]: [INFO]: Info!',
            '[%datetime%]: [NOTICE]: Notice!',
            '[%datetime%]: [WARNING]: Warning!',
            '[%datetime%]: [ERROR]: Error!',
            '[%datetime%]: [CRITICAL]: Critical!',
            '[%datetime%]: [ALERT]: Alert!',
            '[%datetime%]: [EMERGENCY]: Emergency!',
        ]);
    }

    /** @test */
    public function it_supports_context_for_psr3_methods_which_is_transformed_to_readable_dump()
    {
        Artisan::call('command-with-context-logging');

        $this->assertLogFileContains("command-with-context-logging/{$this->date}.log", [
            'Testing context!',
            'Some log with data.',
            get_dump([
                'foo' => 'bar',
                'baz' => 111,
                'faz' => true,
                3 => null,
            ]),
        ]);
    }
}

<?php

namespace Kitpages\WorkflowBundle\Tests\Console;

use Kitpages\WorkflowBundle\Tests\CommandTestCase;

class ConsoleTest
    extends CommandTestCase
{
    /**
     * used to know if services are good initialized.
     */
    public function testBasicConsole()
    {
        $client = self::createClient();
        $output = $this->runCommand($client, '');
        $this->assertContains('cache:clear', $output);
    }
}

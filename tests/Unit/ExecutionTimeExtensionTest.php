<?php

/**
 * Copyright (c) Florian Krämer (https://florian-kraemer.net)
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE file
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Florian Krämer (https://florian-kraemer.net)
 * @author    Florian Krämer
 * @link      https://github.com/Phauthentic
 * @license   https://opensource.org/licenses/MIT MIT License
 */

declare(strict_types=1);

namespace Phauthentic\PHPUnit\ExecutionTiming\Tests\Unit;

use Phauthentic\PHPUnit\ExecutionTiming\ExecutionTimeExtension;
use PHPUnit\Framework\TestCase;

final class ExecutionTimeExtensionTest extends TestCase
{
    private ExecutionTimeExtension $extension;

    protected function setUp(): void
    {
        $this->extension = new ExecutionTimeExtension();
    }

    public function testExtensionCanBeInstantiated(): void
    {
        $this->assertInstanceOf(ExecutionTimeExtension::class, $this->extension);
    }

    public function testDefaultTopNIsTen(): void
    {
        $reflection = new \ReflectionClass($this->extension);
        $property = $reflection->getProperty('topN');
        $property->setAccessible(true);

        $this->assertEquals(10, $property->getValue($this->extension));
    }

    public function testDefaultShowIndividualTimingsIsFalse(): void
    {
        $reflection = new \ReflectionClass($this->extension);
        $property = $reflection->getProperty('showIndividualTimings');
        $property->setAccessible(true);

        $this->assertFalse($property->getValue($this->extension));
    }

    public function testOnExecutionFinishedWithNoTests(): void
    {
        ob_start();
        $this->extension->onExecutionFinished();
        $output = ob_get_clean();

        $this->assertEmpty($output);
    }
}

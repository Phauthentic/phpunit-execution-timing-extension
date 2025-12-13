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
use PHPUnit\Runner\Extension\ParameterCollection;

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

    public function testDefaultWarningThresholdIsOneSecond(): void
    {
        $reflection = new \ReflectionClass($this->extension);
        $property = $reflection->getProperty('warningThreshold');
        $property->setAccessible(true);

        $this->assertEquals(1.0, $property->getValue($this->extension));
    }

    public function testDefaultDangerThresholdIsFiveSeconds(): void
    {
        $reflection = new \ReflectionClass($this->extension);
        $property = $reflection->getProperty('dangerThreshold');
        $property->setAccessible(true);

        $this->assertEquals(5.0, $property->getValue($this->extension));
    }

    public function testExtractConfigurationFromParametersWithWarningThreshold(): void
    {
        $parameters = ParameterCollection::fromArray([
            'warningThreshold' => '2.5',
        ]);

        $reflection = new \ReflectionClass($this->extension);
        $method = $reflection->getMethod('extractConfigurationFromParameters');
        $method->setAccessible(true);
        $method->invoke($this->extension, $parameters);

        $property = $reflection->getProperty('warningThreshold');
        $property->setAccessible(true);

        $this->assertEquals(2.5, $property->getValue($this->extension));
    }

    public function testExtractConfigurationFromParametersWithDangerThreshold(): void
    {
        $parameters = ParameterCollection::fromArray([
            'dangerThreshold' => '10.0',
        ]);

        $reflection = new \ReflectionClass($this->extension);
        $method = $reflection->getMethod('extractConfigurationFromParameters');
        $method->setAccessible(true);
        $method->invoke($this->extension, $parameters);

        $property = $reflection->getProperty('dangerThreshold');
        $property->setAccessible(true);

        $this->assertEquals(10.0, $property->getValue($this->extension));
    }

    public function testExtractConfigurationFromParametersWithBothThresholds(): void
    {
        $parameters = ParameterCollection::fromArray([
            'warningThreshold' => '1.5',
            'dangerThreshold' => '7.5',
        ]);

        $reflection = new \ReflectionClass($this->extension);
        $method = $reflection->getMethod('extractConfigurationFromParameters');
        $method->setAccessible(true);
        $method->invoke($this->extension, $parameters);

        $warningProperty = $reflection->getProperty('warningThreshold');
        $warningProperty->setAccessible(true);
        $dangerProperty = $reflection->getProperty('dangerThreshold');
        $dangerProperty->setAccessible(true);

        $this->assertEquals(1.5, $warningProperty->getValue($this->extension));
        $this->assertEquals(7.5, $dangerProperty->getValue($this->extension));
    }

    public function testOnExecutionFinishedWithNoTests(): void
    {
        ob_start();
        $this->extension->onExecutionFinished();
        $output = ob_get_clean();

        $this->assertEmpty($output);
    }
}

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

namespace Phauthentic\PHPUnit\ExecutionTiming;

use PHPUnit\Event\Test\Finished;
use PHPUnit\Event\Test\PreparationStarted;
use PHPUnit\Runner\Extension\Extension;
use PHPUnit\Runner\Extension\Facade;
use PHPUnit\Runner\Extension\ParameterCollection;
use PHPUnit\TextUI\Configuration\Configuration;

final class ExecutionTimeExtension implements Extension
{
    /** @var array<int, array{name: string, time: float}> */
    private array $testTimes = [];
    private float $testStartTime = 0.0;
    private int $topN = 10;
    private bool $showIndividualTimings = false;

    public function bootstrap(
        Configuration $configuration,
        Facade $facade,
        ParameterCollection $parameters
    ): void {
        $this->extractConfigurationFromParameters($parameters);
        $this->registerSubscribers($facade);
    }

    public function onTestStarted(PreparationStarted $event): void
    {
        $this->testStartTime = microtime(true);
    }

    public function onTestFinished(Finished $event): void
    {
        $duration = microtime(true) - $this->testStartTime;
        $testName = $event->test()->id();
        $this->testTimes[] = [
            'name' => $testName,
            'time' => $duration,
        ];

        if ($this->showIndividualTimings) {
            $timeMs = round($duration * 1000, 2);
            printf("  ⏱  %s: %.2f ms\n", $testName, $timeMs);
        }
    }

    public function onExecutionFinished(): void
    {
        $printer = new ExecutionTimeReportPrinter(
            $this->testTimes,
            $this->topN
        );

        $printer->print();
    }

    public function registerSubscribers(Facade $facade): void
    {
        $facade->registerSubscriber(
            new TestStartedSubscriber($this)
        );

        $facade->registerSubscriber(
            new TestFinishedSubscriber($this)
        );

        $facade->registerSubscriber(
            new TestExecutionFinishedSubscriber($this)
        );
    }

    public function extractConfigurationFromParameters(ParameterCollection $parameters): void
    {
        if ($parameters->has('topN')) {
            $this->topN = (int)$parameters->get('topN');
        }

        if ($parameters->has('showIndividualTimings')) {
            $this->showIndividualTimings = filter_var(
                $parameters->get('showIndividualTimings'),
                FILTER_VALIDATE_BOOLEAN
            );
        }
    }
}

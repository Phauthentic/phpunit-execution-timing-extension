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

use PHPUnit\Util\Color;

final class ExecutionTimeReportPrinter implements ExecutionTimeReportPrinterInterface
{
    /**
     * @param array<int, array{name: string, time: float}> $testTimes
     */
    public function __construct(
        private readonly array $testTimes,
        private readonly int $topN,
        private readonly float $warningThreshold = 1.0,
        private readonly float $dangerThreshold = 5.0
    ) {
    }

    public function print(): void
    {
        if (!$this->shouldPrint()) {
            return;
        }

        $topTests = $this->getTopSlowestTests();

        if (empty($topTests)) {
            return;
        }

        $this->printHeader(count($topTests));
        $this->printTestLines($topTests);

        echo PHP_EOL;
    }

    private function shouldPrint(): bool
    {
        return !empty($this->testTimes) && $this->topN > 0;
    }

    /**
     * @return array<int, array{name: string, time: float}>
     */
    private function getTopSlowestTests(): array
    {
        $sortedTests = $this->testTimes;
        usort(
            $sortedTests,
            static fn(array $a, array $b): int => $b['time'] <=> $a['time']
        );

        return array_slice($sortedTests, 0, $this->topN);
    }

    private function printHeader(int $count): void
    {
        echo PHP_EOL . PHP_EOL . "Top {$count} slowest tests:" . PHP_EOL . PHP_EOL;
    }

    /**
     * @param array<int, array{name: string, time: float}> $topTests
     */
    private function printTestLines(array $topTests): void
    {
        $columnWidths = $this->calculateColumnWidths($topTests);

        foreach ($topTests as $index => $test) {
            $this->printTestLine($test, $index + 1, $columnWidths);
        }
    }

    /**
     * @param array<int, array{name: string, time: float}> $topTests
     * @return array{rank: int, name: int}
     */
    private function calculateColumnWidths(array $topTests): array
    {
        $count = count($topTests);
        $maxRankWidth = strlen((string) $count);
        $maxNameWidth = max(
            array_map(
                static fn(array $test): int => strlen($test['name']),
                $topTests
            )
        );

        return [
            'rank' => $maxRankWidth,
            'name' => $maxNameWidth,
        ];
    }

    /**
     * @param array{name: string, time: float} $test
     * @param array{rank: int, name: int} $columnWidths
     */
    private function printTestLine(array $test, int $rank, array $columnWidths): void
    {
        $timeMs = round($test['time'] * 1000, 2);
        $timeSec = round($test['time'], 3);
        $rankFormatted = $this->formatRank($rank, $columnWidths['rank']);
        $nameFormatted = $this->formatTestName($test['name'], $columnWidths['name']);

        $color = $this->determineColor($test['time']);

        $nameDisplay = $color !== '' ? Color::colorize($color, $nameFormatted) : $nameFormatted;
        $timeMsDisplay = $color !== '' ? Color::colorize($color, sprintf('%.2f ms', $timeMs)) : sprintf('%.2f ms', $timeMs);
        $timeSecDisplay = $color !== '' ? Color::colorize($color, sprintf('(%.3f s)', $timeSec)) : sprintf('(%.3f s)', $timeSec);

        printf(
            "  %s. %s : %s %s" . PHP_EOL,
            $rankFormatted,
            $nameDisplay,
            $timeMsDisplay,
            $timeSecDisplay
        );
    }

    private function determineColor(float $time): string
    {
        if ($time >= $this->dangerThreshold) {
            return 'fg-red';
        }

        if ($time >= $this->warningThreshold) {
            return 'fg-yellow';
        }

        return '';
    }

    private function formatRank(int $rank, int $width): string
    {
        return str_pad(
            (string) $rank,
            $width,
            ' ',
            STR_PAD_LEFT
        );
    }

    private function formatTestName(string $name, int $width): string
    {
        return str_pad($name, $width, ' ');
    }
}

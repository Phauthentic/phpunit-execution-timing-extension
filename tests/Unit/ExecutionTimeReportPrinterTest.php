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

use Phauthentic\PHPUnit\ExecutionTiming\ExecutionTimeReportPrinter;
use PHPUnit\Framework\TestCase;

final class ExecutionTimeReportPrinterTest extends TestCase
{
    public function testPrintWithEmptyTestTimes(): void
    {
        $printer = new ExecutionTimeReportPrinter([], 10);

        ob_start();
        $printer->print();
        $output = ob_get_clean() ?: '';

        $this->assertEmpty($output);
    }

    public function testPrintWithZeroTopN(): void
    {
        $testTimes = [
            ['name' => 'Test1', 'time' => 1.5],
        ];
        $printer = new ExecutionTimeReportPrinter($testTimes, 0);

        ob_start();
        $printer->print();
        $output = ob_get_clean() ?: '';

        $this->assertEmpty($output);
    }

    public function testPrintWithSingleTest(): void
    {
        $testTimes = [
            ['name' => 'Test1', 'time' => 1.5],
        ];
        $printer = new ExecutionTimeReportPrinter($testTimes, 10);

        ob_start();
        $printer->print();
        $output = ob_get_clean() ?: '';

        $this->assertStringContainsString('Top 1 slowest tests:', $output);
        $this->assertStringContainsString('Test1', $output);
        $this->assertStringContainsString('1500.00 ms', $output);
        $this->assertStringContainsString('1.500 s', $output);
    }

    public function testPrintSortsTestsByTimeDescending(): void
    {
        $testTimes = [
            ['name' => 'FastTest', 'time' => 0.1],
            ['name' => 'SlowTest', 'time' => 2.5],
            ['name' => 'MediumTest', 'time' => 1.0],
        ];
        $printer = new ExecutionTimeReportPrinter($testTimes, 10);

        ob_start();
        $printer->print();
        $output = ob_get_clean() ?: '';

        $slowPos = strpos($output, 'SlowTest');
        $mediumPos = strpos($output, 'MediumTest');
        $fastPos = strpos($output, 'FastTest');

        $this->assertNotFalse($slowPos);
        $this->assertNotFalse($mediumPos);
        $this->assertNotFalse($fastPos);
        $this->assertLessThan($mediumPos, $slowPos);
        $this->assertLessThan($fastPos, $mediumPos);
    }

    public function testPrintRespectsTopNParameter(): void
    {
        $testTimes = [
            ['name' => 'Test1', 'time' => 3.0],
            ['name' => 'Test2', 'time' => 2.0],
            ['name' => 'Test3', 'time' => 1.0],
        ];
        $printer = new ExecutionTimeReportPrinter($testTimes, 2);

        ob_start();
        $printer->print();
        $output = ob_get_clean() ?: '';

        $this->assertStringContainsString('Top 2 slowest tests:', $output);
        $this->assertStringContainsString('Test1', $output);
        $this->assertStringContainsString('Test2', $output);
        $this->assertStringNotContainsString('Test3', $output);
    }

    public function testPrintAlignsColumns(): void
    {
        $testTimes = [
            ['name' => 'Short', 'time' => 1.0],
            ['name' => 'VeryLongTestName', 'time' => 2.0],
        ];
        $printer = new ExecutionTimeReportPrinter($testTimes, 10);

        ob_start();
        $printer->print();
        $output = ob_get_clean() ?: '';

        $lines = explode("\n", $output);
        $testLines = array_filter($lines, static fn(string $line): bool => str_contains($line, '.'));

        if (count($testLines) >= 2) {
            $testLines = array_values($testLines);
            $firstLine = $testLines[0];
            $secondLine = $testLines[1];

            $firstColonPos = strpos($firstLine, ':');
            $secondColonPos = strpos($secondLine, ':');

            $this->assertNotFalse($firstColonPos);
            $this->assertNotFalse($secondColonPos);
            $this->assertEquals($firstColonPos, $secondColonPos, 'Columns should be aligned');
        }
    }

    public function testPrintFormatsTimeCorrectly(): void
    {
        $testTimes = [
            ['name' => 'Test1', 'time' => 0.123],
            ['name' => 'Test2', 'time' => 1.234],
            ['name' => 'Test3', 'time' => 12.345],
        ];
        $printer = new ExecutionTimeReportPrinter($testTimes, 10);

        ob_start();
        $printer->print();
        $output = ob_get_clean() ?: '';

        $this->assertStringContainsString('123.00 ms', $output);
        $this->assertStringContainsString('0.123 s', $output);
        $this->assertStringContainsString('1234.00 ms', $output);
        $this->assertStringContainsString('1.234 s', $output);
        $this->assertStringContainsString('12345.00 ms', $output);
        $this->assertStringContainsString('12.345 s', $output);
    }

    public function testPrintDoesNotColorTestBelowThreshold(): void
    {
        $testTimes = [
            ['name' => 'FastTest', 'time' => 0.5],
        ];
        $printer = new ExecutionTimeReportPrinter($testTimes, 10, 1.0, 5.0);

        ob_start();
        $printer->print();
        $output = ob_get_clean() ?: '';

        $this->assertStringContainsString('FastTest', $output);
        // Should not contain ANSI color codes
        $this->assertStringNotContainsString("\x1b[", $output);
    }

    public function testPrintColorsTestWithWarningThreshold(): void
    {
        $testTimes = [
            ['name' => 'WarningTest', 'time' => 1.5],
        ];
        $printer = new ExecutionTimeReportPrinter($testTimes, 10, 1.0, 5.0);

        ob_start();
        $printer->print();
        $output = ob_get_clean() ?: '';

        $this->assertStringContainsString('WarningTest', $output);
        // Should contain yellow ANSI color code (fg-yellow = 33)
        $this->assertStringContainsString("\x1b[33m", $output);
        // Should not contain red color code
        $this->assertStringNotContainsString("\x1b[31m", $output);
    }

    public function testPrintColorsTestWithDangerThreshold(): void
    {
        $testTimes = [
            ['name' => 'DangerTest', 'time' => 6.0],
        ];
        $printer = new ExecutionTimeReportPrinter($testTimes, 10, 1.0, 5.0);

        ob_start();
        $printer->print();
        $output = ob_get_clean() ?: '';

        $this->assertStringContainsString('DangerTest', $output);
        // Should contain red ANSI color code (fg-red = 31)
        $this->assertStringContainsString("\x1b[31m", $output);
    }

    public function testPrintColorsCorrectlyWithMultipleThresholds(): void
    {
        $testTimes = [
            ['name' => 'FastTest', 'time' => 0.5],
            ['name' => 'WarningTest', 'time' => 2.0],
            ['name' => 'DangerTest', 'time' => 6.0],
        ];
        $printer = new ExecutionTimeReportPrinter($testTimes, 10, 1.0, 5.0);

        ob_start();
        $printer->print();
        $output = ob_get_clean() ?: '';

        // FastTest should not be colored
        $fastTestPos = strpos($output, 'FastTest');
        $this->assertNotFalse($fastTestPos);
        $fastTestLine = substr($output, $fastTestPos, 100);
        $this->assertStringNotContainsString("\x1b[", $fastTestLine);

        // WarningTest should be yellow
        $warningTestPos = strpos($output, 'WarningTest');
        $this->assertNotFalse($warningTestPos);
        $warningTestLine = substr($output, $warningTestPos, 200);
        $this->assertStringContainsString("\x1b[33m", $warningTestLine);
        $this->assertStringNotContainsString("\x1b[31m", $warningTestLine);

        // DangerTest should be red
        $dangerTestPos = strpos($output, 'DangerTest');
        $this->assertNotFalse($dangerTestPos);
        $dangerTestLine = substr($output, $dangerTestPos, 200);
        $this->assertStringContainsString("\x1b[31m", $dangerTestLine);
    }

    public function testPrintRespectsThresholdConfiguration(): void
    {
        $testTimes = [
            ['name' => 'Test1', 'time' => 0.8],
            ['name' => 'Test2', 'time' => 1.2],
            ['name' => 'Test3', 'time' => 3.0],
        ];
        // Custom thresholds: warning at 1.0, danger at 2.0
        $printer = new ExecutionTimeReportPrinter($testTimes, 10, 1.0, 2.0);

        ob_start();
        $printer->print();
        $output = ob_get_clean() ?: '';

        // Test1 (0.8s) should not be colored
        $test1Pos = strpos($output, 'Test1');
        $this->assertNotFalse($test1Pos);
        $test1Line = substr($output, $test1Pos, 100);
        $this->assertStringNotContainsString("\x1b[", $test1Line);

        // Test2 (1.2s) should be yellow (>= 1.0 but < 2.0)
        $test2Pos = strpos($output, 'Test2');
        $this->assertNotFalse($test2Pos);
        $test2Line = substr($output, $test2Pos, 200);
        $this->assertStringContainsString("\x1b[33m", $test2Line);

        // Test3 (3.0s) should be red (>= 2.0)
        $test3Pos = strpos($output, 'Test3');
        $this->assertNotFalse($test3Pos);
        $test3Line = substr($output, $test3Pos, 200);
        $this->assertStringContainsString("\x1b[31m", $test3Line);
    }
}

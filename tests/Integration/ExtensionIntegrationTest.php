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

namespace Phauthentic\PHPUnit\ExecutionTiming\Tests\Integration;

use PHPUnit\Framework\TestCase;

final class ExtensionIntegrationTest extends TestCase
{
    public function testFastTest(): void
    {
        usleep(10000);
        $this->assertTrue(true);
    }

    public function testMediumTest(): void
    {
        usleep(50000);
        $this->assertTrue(true);
    }

    public function testSlowTest(): void
    {
        usleep(100000);
        $this->assertTrue(true);
    }

    public function testVerySlowTest(): void
    {
        usleep(200000);
        $this->assertTrue(true);
    }

    public function testAnotherFastTest(): void
    {
        usleep(15000);
        $this->assertTrue(true);
    }
}

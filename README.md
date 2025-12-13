# PHPUnit Execution Timing Extension

A PHPUnit extension that tracks and reports test execution times, helping you identify slow tests in your test suite.

## Features

- Tracks execution time for each test
- Displays a summary of the slowest tests after test execution
- Configurable number of slowest tests to display
- Optional per-test timing output
- Aligned column formatting for easy reading
- Compatible with PHPUnit 10, 11, and 12

## Requirements

- PHP 8.1 or higher
- PHPUnit 10, 11, or 12

## Installation

Install via Composer:

```bash
composer require --dev phauthentic/phpunit-execution-timing-extension
```

## Configuration

Add the extension to your `phpunit.xml.dist` or `phpunit.xml` file:

```xml
<phpunit>
    <extensions>
        <bootstrap class="Phauthentic\PHPUnit\ExecutionTiming\ExecutionTimeExtension">
            <parameter name="topN" value="10"/>
            <parameter name="showIndividualTimings" value="false"/>
        </bootstrap>
    </extensions>
</phpunit>
```

### Configuration Parameters

- **`topN`** (default: `10`): Number of slowest tests to display in the summary report
- **`showIndividualTimings`** (default: `false`): Whether to display timing for each test as it runs

## Usage

After running your tests, you'll see a summary report at the end showing the slowest tests:

```
Top 10 slowest tests:

  1. MyTest::testSlowOperation                    : 1234.56 ms (1.235 s)
  2. AnotherTest::testComplexCalculation          :  987.65 ms (0.988 s)
  3. DatabaseTest::testLargeQuery                  :  654.32 ms (0.654 s)
  ...
```

### Example Output

With `showIndividualTimings` set to `true`, you'll also see timing for each test as it executes:

```
  ⏱  MyTest::testSomething: 123.45 ms
  ⏱  AnotherTest::testOther: 45.67 ms
```

## Example Configuration

### Basic Configuration

```xml
<phpunit>
    <extensions>
        <bootstrap class="Phauthentic\PHPUnit\ExecutionTiming\ExecutionTimeExtension">
            <parameter name="topN" value="5"/>
        </bootstrap>
    </extensions>
</phpunit>
```

### With Individual Timings Enabled

```xml
<phpunit>
    <extensions>
        <bootstrap class="Phauthentic\PHPUnit\ExecutionTiming\ExecutionTimeExtension">
            <parameter name="topN" value="20"/>
            <parameter name="showIndividualTimings" value="true"/>
        </bootstrap>
    </extensions>
</phpunit>
```

## How It Works

The extension subscribes to PHPUnit events:
- **Test Preparation Started**: Records the start time for each test
- **Test Finished**: Calculates duration and optionally displays it
- **Execution Finished**: Generates and displays the summary report

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Copyright

Copyright (c) Florian Krämer (https://florian-kraemer.net)

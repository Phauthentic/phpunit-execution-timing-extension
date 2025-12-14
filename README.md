# PHPUnit Execution Timing Extension

A PHPUnit extension that tracks and reports test execution times, helping you identify slow tests in your test suite.

## Features

- Tracks execution time for each test
- Displays a summary of the slowest tests after test execution
- Configurable number of slowest tests to display
- Optional per-test timing output
- Color-coded output based on configurable thresholds (yellow for warnings, red for danger)
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
            <parameter name="warningThreshold" value="1.0"/>
            <parameter name="dangerThreshold" value="5.0"/>
        </bootstrap>
    </extensions>
</phpunit>
```

### Configuration Parameters

- **`topN`** (default: `10`): Number of slowest tests to display in the summary report
- **`showIndividualTimings`** (default: `false`): Whether to display timing for each test as it runs
- **`warningThreshold`** (default: `1.0`): Time in seconds at which tests will be colored yellow (warning). Tests with execution time >= this value will be highlighted.
- **`dangerThreshold`** (default: `5.0`): Time in seconds at which tests will be colored red (danger). Tests with execution time >= this value will be highlighted in red. Tests between `warningThreshold` and `dangerThreshold` will be colored yellow.

## Usage

After running your tests, you'll see a summary report at the end showing the slowest tests. Tests are color-coded based on their execution time:

- **Yellow**: Tests that exceed the warning threshold (default: 1 second)
- **Red**: Tests that exceed the danger threshold (default: 5 seconds)
- **Normal**: Tests below the warning threshold

```text
Top 10 slowest tests:

  1. ⏱ 1234.56 ms (1.235 s) MyTest::testSlowOperation                    [colored red]
  2. ⏱  987.65 ms (0.988 s) AnotherTest::testComplexCalculation          [colored yellow]
  3. ⏱  654.32 ms (0.654 s) DatabaseTest::testLargeQuery                 [colored yellow]
  ...
```

Note: The actual output will show ANSI color codes when viewed in a terminal that supports colors. The colors help quickly identify tests that may need optimization.

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

### With Custom Thresholds

```xml
<phpunit>
    <extensions>
        <bootstrap class="Phauthentic\PHPUnit\ExecutionTiming\ExecutionTimeExtension">
            <parameter name="topN" value="10"/>
            <parameter name="warningThreshold" value="0.5"/>
            <parameter name="dangerThreshold" value="2.0"/>
        </bootstrap>
    </extensions>
</phpunit>
```

This configuration will:
- Show yellow for tests taking 0.5 seconds or more
- Show red for tests taking 2.0 seconds or more

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

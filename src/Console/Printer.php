<?php

declare(strict_types=1);

namespace Brain\Console;

use Brain\Facades\Terminal;
use Exception;
use Illuminate\Console\Concerns\InteractsWithIO;

class Printer
{
    use InteractsWithIO;

    /**
     * Colors that represent the different elements.
     */
    private array $elemColors = [
        'DOMAIN' => '#6C7280',
        'PROC' => 'blue',
        'TASK' => 'yellow',
        'QERY' => 'green',
    ];

    /**
     * @var int The width of the terminal in characters.
     */
    private int $terminalWidth;

    /**
     * @var int The length of the longest domain in the brain's map.
     */
    private int $lengthLongestDomain = 0;

    /**
     * @var array The lines to be printed to the terminal.
     */
    private array $lines = [];

    public function __construct(
        private readonly BrainMap $brain
    ) {
        $this->checkIfBrainMapIsEmpty();
        $this->getTerminalWidth();
        $this->getLengthOfTheLongestDomain();
    }

    /**
     * Prints the collected lines to the output.
     *
     * This method uses the `collect` helper to create a collection
     * from the `$lines` property, flattens the collection, and writes
     * the resulting output using the `$output`'s `writeln` method.
     */
    public function print(): void
    {
        $flattenedLines = [];
        array_walk_recursive($this->lines, function ($line) use (&$flattenedLines) {
            $flattenedLines[] = $line;
        });

        $this->output->writeln($flattenedLines);
    }

    private function checkIfBrainMapIsEmpty(): void
    {
        if (empty($this->brain->map)) {
            throw new Exception('The brain map is empty.');
        }
    }

    private function createLines(): void
    {
        $this->brain->map->each(function ($domainData) {
            $domain = data_get($domainData, 'domain');

            $this->addNewLine();
        });
    }

    /**
     * Adds a new empty line to the lines array if the last line is not already empty.
     * This ensures that there is a separation or spacing between lines when needed.
     */
    private function addNewLine(): void
    {
        if (end($this->lines) === ['']) {
            return;
        }

        $this->lines[] = [''];
    }

    /**
     * Calculates the length of the longest domain in the brain's map.
     *
     * This method iterates through the brain's map, sorts the entries
     * in descending order based on the length of the 'domain' value,
     * and retrieves the length of the longest domain.
     */
    private function getLengthOfTheLongestDomain(): void
    {
        $this->lengthLongestDomain = mb_strlen(
            (string) data_get($this->brain->map
                ->sortByDesc(fn ($value): int => mb_strlen((string) $value['domain']))
                ->first(), 'domain')
        );
    }

    /**
     * Retrieves and sets the terminal's current width by using the Terminal utility.
     *
     * This method assigns the number of columns (width) of the terminal
     * to the `$terminalWidth` property.
     */
    private function getTerminalWidth(): void
    {
        $this->terminalWidth = Terminal::cols();
    }
}

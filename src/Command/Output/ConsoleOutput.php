<?php

declare(strict_types = 1);

namespace Pekral\GoogleConsole\Command\Output;

use Symfony\Component\Console\Output\OutputInterface;

use function Termwind\render;
use function Termwind\renderUsing;

/**
 * Provides styled pekral-google output using Termwind.
 */
final readonly class ConsoleOutput
{

    public function __construct(private OutputInterface $output)
    {
        renderUsing($this->output);
    }

    public function header(string $title): void
    {
        render(<<<HTML
            <div class="mx-2 my-1">
                <span class="px-1 bg-yellow-500 text-black font-bold"> ⚡ </span>
                <span class="ml-1 text-yellow-500 font-bold">{$title}</span>
            </div>
        HTML);
    }

    public function section(string $title): void
    {
        render(<<<HTML
            <div class="mx-2 mt-1">
                <span class="text-cyan-400 font-bold">{$title}</span>
            </div>
            <div class="mx-2 text-gray-500">╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌</div>
        HTML);
    }

    public function success(string $message): void
    {
        render(<<<HTML
            <div class="mx-2 my-1">
                <span class="text-green-500 font-bold"> ✓ </span>
                <span class="text-green-400">{$message}</span>
            </div>
        HTML);
    }

    public function warning(string $message): void
    {
        render(<<<HTML
            <div class="mx-2 my-1">
                <span class="text-yellow-500 font-bold"> ⚠ </span>
                <span class="text-yellow-400">{$message}</span>
            </div>
        HTML);
    }

    public function info(string $message): void
    {
        render(<<<HTML
            <div class="mx-2">
                <span class="text-blue-400"> ℹ </span>
                <span class="text-gray-300">{$message}</span>
            </div>
        HTML);
    }

    public function error(string $message): void
    {
        render(<<<HTML
            <div class="mx-2 my-1">
                <span class="px-1 bg-red-500 text-white font-bold"> ✗ </span>
                <span class="text-red-400 ml-1">{$message}</span>
            </div>
        HTML);
    }

    /**
     * @param array<string> $headers
     * @param array<array<string>> $rows
     */
    public function table(array $headers, array $rows): void
    {
        $headerHtml = '';

        foreach ($headers as $header) {
            $headerHtml .= sprintf('<th class="px-2 text-cyan-400 font-bold">%s</th>', $header);
        }

        $rowsHtml = '';

        foreach ($rows as $row) {
            $rowsHtml .= '<tr>';

            foreach ($row as $index => $cell) {
                $class = $index === 0 ? 'text-white' : 'text-gray-300';
                $rowsHtml .= sprintf('<td class="px-2 %s">', $class) . htmlspecialchars($cell) . '</td>';
            }

            $rowsHtml .= '</tr>';
        }

        render(<<<HTML
            <div class="mx-2 my-1">
                <table>
                    <thead>
                        <tr>{$headerHtml}</tr>
                    </thead>
                    <tbody>
                        {$rowsHtml}
                    </tbody>
                </table>
            </div>
        HTML);
    }

    /**
     * @param array<array{label: string, value: string}> $items
     */
    public function definitionList(array $items): void
    {
        $html = '<div class="mx-2 my-1">';

        foreach ($items as $item) {
            $label = htmlspecialchars($item['label']);
            $value = htmlspecialchars($item['value']);
            $html .= <<<HTML
                <div class="flex">
                    <span class="text-gray-400 w-20">{$label}</span>
                    <span class="text-white ml-2">{$value}</span>
                </div>
            HTML;
        }

        $html .= '</div>';

        render($html);
    }

    public function keyValue(string $label, string $value, string $valueColor = 'white'): void
    {
        $label = htmlspecialchars($label);
        $value = htmlspecialchars($value);
        render(<<<HTML
            <div class="mx-4 flex">
                <span class="text-gray-400">{$label}</span>
                <span class="text-{$valueColor} ml-2">{$value}</span>
            </div>
        HTML);
    }

    public function keyValueBool(string $label, bool $value, string $trueColor = 'green-400', string $falseColor = 'red-400'): void
    {
        $this->keyValue($label, $this->boolToString($value), $value ? $trueColor : $falseColor);
    }

    public function boolToString(bool $value): string
    {
        return $value ? 'Yes' : 'No';
    }

    public function newLine(): void
    {
        render('<div></div>');
    }

}

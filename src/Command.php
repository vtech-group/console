<?php

namespace Vtech\Console;

use Exception;
use Illuminate\Console\Command as IlluminateCommand;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableCell;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Helper\TableStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;

class Command extends IlluminateCommand
{
    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Alias of the fire() method.
     *
     * @return void
     */
    public function handle()
    {
        $this->fire();
    }

    /**
     * Prompt the user for input.
     *
     * @param string          $question
     * @param string|null     $default
     * @param string|callable $validator
     * @param mixed|null      $attempts
     *
     * @return mixed
     */
    public function ask($question, $default = null, $validator = null, $attempts = null)
    {
        $question = new Question($question, $default);

        $question->setValidator($validator)->setMaxAttempts($attempts);

        try {
            return $this->output->askQuestion($question);
        } catch (Exception $exception) {
            $this->errorBlock('Entered incorrect information multiple times. Cancel the action.', 'ERROR');
            exit(1);
        }
    }

    /**
     * Prompt the user for input but hide the answer from the console.
     *
     * @param string        $question
     * @param bool          $fallback
     * @param callable|null $validator
     * @param mixed|null    $attempts
     *
     * @return mixed
     */
    public function secret($question, $fallback = true, $validator = null, $attempts = null)
    {
        $question = new Question($question);

        $question->setHidden(true)->setHiddenFallback($fallback)->setValidator($validator)->setMaxAttempts($attempts);

        try {
            return $this->output->askQuestion($question);
        } catch (Exception $exception) {
            $this->errorBlock('Entered incorrect information multiple times. Cancel the action.', 'ERROR');
            exit(1);
        }
    }

    /**
     * Give the user a single choice from an array of answers.
     *
     * @param string        $question
     * @param string|null   $default
     * @param mixed|null    $attempts
     * @param bool          $multiple
     * @param callable|null $normalizer
     *
     * @return string|array
     */
    public function choice($question, array $choices, $default = null, $attempts = null, $multiple = false, $normalizer = null)
    {
        $question = new ChoiceQuestion($question, $choices, $default);

        $question->setMaxAttempts($attempts)->setMultiselect($multiple)->setAutocompleterValues(null);

        if (is_callable($normalizer)) {
            $question->setNormalizer($normalizer);
        }

        $answer = $this->output->askQuestion($question);

        if (is_array($answer)) {
            $this->output->block('Selected: ' . implode(', ', $answer));
        } else {
            $this->output->block('Selected: ' . $answer);
        }

        return $answer;
    }

    /**
     * Write a message as standard output without new line.
     *
     * @param string          $message
     * @param string          $style
     * @param int|string|null $verbosity
     *
     * @return void
     */
    public function write($message, $style = null, $verbosity = null)
    {
        $styled = $style ? "<$style>$message</$style>" : $message;

        if (method_exists($this, 'parseVerbosity')) {
            $this->output->write($styled, false, $this->parseVerbosity($verbosity));
        } else {
            $this->output->write($styled, false);
        }
    }

    /**
     * Add newline(s).
     *
     * @param int $count The quantity of lines
     *
     * @return void
     */
    public function newLine($count = 1)
    {
        $this->output->newLine($count);
    }

    /**
     * Formats a command title.
     *
     * @param string $message
     *
     * @return void
     */
    public function title($message)
    {
        $this->output->title($message);
    }

    /**
     * Formats a heading title.
     *
     * @param string      $message The heading message
     * @param int         $level   The heading level
     * @param string|null $label   The symbol of heading
     * @param string      $style   The color style
     *
     * @return void
     */
    public function section($message, $level = 1, $label = '#', $style = 'comment')
    {
        $level   = max(1, (int) $level);
        $message = trim($message);

        if (preg_match('/(\w|\d)$/', $message)) {
            $message .= '.';
        }

        $this->block($message, $label ? str_repeat($label, $level) : null, $style, '');
    }

    /**
     * Formats a message as a block of text.
     *
     * @param string|array $messages The message to write in the block
     * @param string       $label    The content will be display in front of message
     * @param string       $style    The output formatter style
     * @param string       $prefix   The prefix content will be display in front of label
     * @param bool         $padding
     * @param bool         $escape
     *
     * @return void
     */
    public function block($messages, $label = null, $style = null, $prefix = ' ', $padding = false, $escape = true)
    {
        $this->output->block($messages, $label, $style, $prefix, $padding, $escape);
    }

    /**
     * Format a message as a block with highlight style.
     *
     * @param string|array $messages The message to write in the block
     * @param string       $label    The content will be display in front of message
     * @param string       $prefix   The prefix content will be display in front of label
     * @param bool         $padding
     * @param bool         $escape
     *
     * @return void
     */
    public function highlightBlock($message, $label = null, $prefix = ' ', $padding = true, $escape = true)
    {
        $this->block($message, $label, 'highlight', $prefix, $padding, $escape);
    }

    /**
     * Format a message as a block with the success style.
     *
     * @param string|array $messages The message to write in the block
     * @param string       $label    The content will be display in front of message
     * @param string       $prefix   The prefix content will be display in front of label
     * @param bool         $padding
     * @param bool         $escape
     *
     * @return void
     */
    public function successBlock($message, $label = null, $prefix = ' ', $padding = true, $escape = true)
    {
        $this->block($message, $label, 'success', $prefix, $padding, $escape);
    }

    /**
     * Format a message as a block with the warning style.
     *
     * @param string|array $messages The message to write in the block
     * @param string       $label    The content will be display in front of message
     * @param string       $prefix   The prefix content will be display in front of label
     * @param bool         $padding
     * @param bool         $escape
     *
     * @return void
     */
    public function warningBlock($message, $label = null, $prefix = ' ', $padding = true, $escape = true)
    {
        $this->block($message, $label, 'warning', $prefix, $padding, $escape);
    }

    /**
     * Format a message as a block with the error style.
     *
     * @param string|array $messages The message to write in the block
     * @param string       $label    The content will be display in front of message
     * @param string       $prefix   The prefix content will be display in front of label
     * @param bool         $padding
     * @param bool         $escape
     *
     * @return void
     */
    public function errorBlock($message, $label = null, $prefix = ' ', $padding = true, $escape = true)
    {
        $this->block($message, $label, 'error', $prefix, $padding, $escape);
    }

    /**
     * Formats a list of key/value horizontally.
     *
     * @param array      $list   The list want to render
     * @param string|int $symbol The index symbol
     * @param string     $style  The color styles will apply to the header cell
     * @param bool       $border Draw top and bottom borders
     *
     * @return void
     */
    public function writeList(array $list = [], $symbol = 'circle', $style = 'info', $border = false)
    {
        $tableStyle = new TableStyle();
        $tableStyle
            ->setHorizontalBorderChars($border ? '-' : '')
            ->setVerticalBorderChars('')
            ->setDefaultCrossingChar('')
            ->setCellHeaderFormat('%s');

        if ($style) {
            $tableStyle->setCellHeaderFormat("<$style>%s</$style>");
        }

        $table   = new Table($this->output);
        $headers = [];
        $rows    = [];

        if ($this->isAssoc($list)) {
            foreach ($list as $key => $value) {
                $headers[] = (is_numeric($symbol) ? ((int) $symbol++ . '. ') : ($this->bullet($symbol) . ' ')) . $key;
                $rows[]    = $value;
            }

            $colons = array_fill(0, count($rows), ($style ? "<$style>:</$style>" : ':'));

            $table->setRows([$colons, $rows]);
        } else {
            foreach ($list as $key => $value) {
                $headers[] = (is_numeric($symbol) ? ((int) $symbol++ . '.') : $this->bullet($symbol));
                $rows[]    = $value;
            }

            $table->setRows([$rows]);
        }

        $table->setHeaders($headers);
        $table->setHorizontal();
        $table->setStyle($tableStyle);

        $table->render();
        $this->newLine();
    }

    /**
     * Return a formatted bullet with a style name.
     *
     * @param string $style The style name
     *
     * @return string
     */
    public function bullet($style)
    {
        if ('disc' == $style) {
            return '•';
        }

        if ('circle' == $style) {
            return '○';
        }

        if ('square' == $style) {
            return '■';
        }

        if ('double-left-arrow' == $style) {
            return '«';
        }

        if ('double-right-arrow' == $style) {
            return '»';
        }

        return $style;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->setFormatStyles();

        return parent::execute($input, $output);
    }

    /**
     * Initialize some styles for the formatter.
     *
     * @return void
     */
    protected function setFormatStyles()
    {
        $styles = [
            'highlight' => [
                'foreground' => 'black',
                'background' => 'white',
            ],
            'success' => [
                'foreground' => 'black',
                'background' => 'green',
            ],
            'warning' => [
                'foreground' => 'black',
                'background' => 'yellow',
            ],
            'error' => [
                'foreground' => 'white',
                'background' => 'red',
            ],
        ];

        if (property_exists($this, 'formatStyles')) {
            $styles = array_merge($styles, $this->formatStyles);
        }

        foreach ($styles as $name => $settings) {
            $foreground = Arr::get($settings, 'foreground', 'default');
            $background = Arr::get($settings, 'background', 'default');
            $style      = new OutputFormatterStyle($foreground, $background);

            $this->output->getFormatter()->setStyle(Str::snake($name), $style);
        }

        return $this;
    }

    /**
     * Determine if array is associative.
     *
     * @param array $array The array want to check
     *
     * @return bool
     */
    protected function isAssoc(array $array)
    {
        return (bool) count(array_filter(array_keys($array), 'is_string'));
    }
}

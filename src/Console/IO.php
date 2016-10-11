<?php declare(strict_types = 1);

namespace Mihaeu\MovieManager\Console;

use Composer\IO\ConsoleIO;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Question\Question;

class IO extends ConsoleIO
{
    /**
     * @param Question $question
     *
     * @return string
     */
    public function askQuestion(Question $question)
    {
        /** @var QuestionHelper $questionHelper */
        $questionHelper = $this->helperSet->get('question');
        return $questionHelper->ask($this->input, $this->output, $question);
    }

    /**
     * @param string $option
     *
     * @return mixed
     */
    public function getOption($option)
    {
        return $this->input->getOption($option);
    }

    /**
     * @param array $headers
     * @param array $rows
     */
    public function table(array $headers, array $rows)
    {
        /** @var Table $table */
        $table = $this->helperSet->get('table');
        $table
            ->setHeaders($headers)
            ->setRows($rows)
        ;
        $table->render($this->output);
    }
}

<?php namespace Magazine\Command;

use Magazine\Magazine\Magazine;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Params extends Command
{

    protected function configure()
    {
        $this
            ->setName('package')
            ->setDescription('Builds the Magento tar.gz connect package')
            ->addArgument(
                'path',
                InputArgument::REQUIRED,
                'Path to the package JSON'
            );
    }

    /**
     * Main CLI method that validates that the specified path exist, is a file
     * and a well-formed JSON file else exits with status code 0.
     *
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $path = $input->getArgument('path');
        if (!file_exists($path) && !is_file($input->getArgument('path'))) {
            self::error($output, "The specified path is missing or a directory");
        } else {
            if (json_decode(file_get_contents($path)) == null) {
                self::error($output, "The specified file is not a valid JSON file");
            } else {
                $packager = new Magazine($path, $output);
                $packager->package();
            }
        }
    }

    /**
     * Prints an info log message to the console with the warning message colour
     *
     * @param $output
     * @param $message string the log message
     * @param array $args the array of format parameters
     */
    private function error(OutputInterface $output, $message, $args = array()) {
        $output->writeln('<error>'.sprintf($message, $args).'</error>');
        exit(0);
    }
}
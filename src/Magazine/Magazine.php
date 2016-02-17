<?php namespace Magazine\Magazine;
/**
 * Copyright (c) 2016 [Mridang Agarwalla]
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 */
use Symfony\Component\Console\Output\OutputInterface;

class Magazine
{

    private $base_dir;
    private $temp_dir;
    private $targets;
    private $output;

    /**
     * Constructor for initializing the Magazine packager that initializes the
     * base and temporary directories and validates that a file exists.
     *
     * @param $path string the absolute path to the package.json file
     * @param OutputInterface $output The output interface for logging messages
     * @throws \Exception When the package.json file was not found or malformed
     */
    public function __construct($path, OutputInterface $output)
    {

        $this->output = $output;
        if (!file_exists(realpath($path)) && !is_file(realpath($path))) {
            throw new \Exception("Missing file or was a directory");
        } else {
            $this->pkg_json = $path;
            $this->base_dir = dirname(realpath($path));
            $this->temp_dir = self::getTempDir();
            $this->targets = new \Mage_Connect_Package_Target();
        }
    }

    public function package() {
        $this->debug("Using %s as the temporary directory", $this->temp_dir);

        $package = new \Mage_Connect_Package();
        $string = file_get_contents($this->pkg_json);
        $json = json_decode($string, true);
        $package->importDataV1x($json);
        $this->debug("Loading packaging metadata from %s", $this->pkg_json);

        // Building the list of files and directories that should be excluded
        $excludes = array();
        foreach ($json['exclude'] as $entry) {
            $this->info("Building exclusion files matching pattern %s", $entry);
            $excludes = array_merge($excludes, $this->rglob($entry));
        }

        // Building the list of files and directories that should be included
        $contents = array();
        foreach ($json['include'] as $target=>$patterns) {
            if (!empty($patterns)) {
                $this->info("Building file list for target directory %s", $target);
            }
            foreach ($patterns as $pattern) {
                $this->info("Globbing files matching pattern %s", $pattern);
                $contents = array_merge($contents, $this->rglob($pattern, $excludes));
            }
        }

        // At this point we have a list of files that should be packaged into the
        // extension so we move each of the files and directories to the temporary
        // directory. If the directory doesn't exist, it will be created
        chdir($this->temp_dir); // This is necessary for the Magento packager's addContent
        // method to work as it needs to put relative paths into the package XML.
        foreach ($contents as $item) {
            if (is_dir($item)) {
                if (!file_exists($item)) {
                    $this->debug("Creating directory %s in the temporary directory", $item);
                    mkdir($item, 0755, true);
                }
            } else {
                $target = $this->get_target_uri($item);
                $name = $this->get_target_name($item);
                $this->debug("Copying file %s to the temporary directory", $item);
                if (!file_exists(dirname($item))) {
                    $this->debug("Creating directory %s in the temporary directory", dirname($item));
                    mkdir(dirname($item), 0755, true);
                }
                copy($this->base_dir.'/'.$item, $item);
                $this->debug("Adding %s to the package", $item);
                $package->addContent(substr($item, strlen($target) - 1), $name);
            }
        }

        $xml = new \DOMDocument();
        $xml->preserveWhiteSpace = FALSE;
        $xml->loadXML($package->getPackageXml());
        $xml->formatOutput = TRUE;
        $this->trace(PHP_EOL);
        file_put_contents($this->temp_dir.'/package.xml', $xml->saveXml());
        if ($this->output->getVerbosity() >= OutputInterface::VERBOSITY_VERY_VERBOSE) {
            $this->trace($xml->saveXml());
        }

        $tgz = $json['name']."-".$json['version']['release'].'.tgz';
        $this->info("Packaging all the contents into archive %s", $tgz);
        $archiver = new \Mage_Archive_Tar();
        $archiver->pack($this->temp_dir, $this->base_dir."/".$tgz);
    }

    private function get_target_name($path)
    {
        foreach ($this->targets->getTargets() as $target => $uri) {
            if (strpos('./'.$path, $uri) === 0) {
                return $target;
            }
        }
        return null;
    }

    private function get_target_uri($path)
    {
        foreach ($this->targets->getTargets() as $target => $uri) {
            if (strpos('./'.$path, $uri) === 0) {
                return $uri;
            }
        }
        return null;
    }

    /**
     * Returns a newly created temporary directory in the OS's temporary location.
     * All the files and folders of the package are moved to this directory and
     * packaged.
     *
     * @return string the path the newly created temporary directory
     */
    protected static function getTempDir()
    {
        $name = tempnam(sys_get_temp_dir(), 'tmp');
        unlink($name);
        mkdir($name,0777,true);
        return $name;
    }

    /**
 * Recursively globs all the files and directories using the given pattern.
 * If any file or directory exists in the list of excluded items, it is
 * skipped
 *
 * @param $pattern string the glob pattern for selecting files and directories
 * @param array $excludes the array of files and directories to exclude
 * @return array the array of files and directories matching the glob pattern
 */
    protected function rglob($pattern, $excludes = array()) {
        $files = array();
        foreach (glob($pattern, 0) as $item) {
            if (!empty($excludes) && in_array(ltrim($item, '\.\/'), $excludes)) {
                $this->trace("Skipping excluded item ./%s", ltrim($item, '\.\/'));
                continue;
            }
            if (is_dir($item)) {
                $this->debug("Globbing files in directory %s", $item);
                $files = array_merge($files, $this->rglob($item.'/*', $excludes));
            } else {
                array_push($files, ltrim($item, '\.\/'));
            }
        }
        return $files;
    }

    /**
     * Prints a debug log message to the console with the trace message colour
     *
     * @param $message string the log message
     * @param array $args the array of format parameters
     */
    protected function trace($message, $args = array()) {
        if ($this->output->getVerbosity() >= OutputInterface::VERBOSITY_VERY_VERBOSE) {
            /** @noinspection HtmlUnknownTag */
            $this->output->writeln("<fg=cyan>".sprintf($message, $args));
        }
    }

    /**
     * Prints a debug log message to the console with the debug message colour
     *
     * @param $message string the log message
     * @param array $args the array of format parameters
     */
    protected function debug($message, $args = array()) {
    if ($this->output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
        /** @noinspection HtmlUnknownTag */
        $this->output->writeln('<fg=green>'.sprintf($message, $args));
        }
    }

    /**
     * Prints an info log message to the console with the info message colour
     *
     * @param $message string the log message
     * @param array $args the array of format parameters
     */
    protected function info($message, $args = array()) {
        /** @noinspection HtmlUnknownTag */
        $this->output->writeln('<fg=white>'.sprintf($message, $args));
    }
}
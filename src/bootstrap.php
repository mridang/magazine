<?php
function includeIfExists($file)
{
    /** @noinspection PhpIncludeInspection */
    return file_exists($file) ? include $file : false;
}

if ((!$loader = includeIfExists(__DIR__.'/../vendor/autoload.php'))
    &&
    (!$loader = includeIfExists(__DIR__.'/../../../autoload.php')))
{
    echo 'You must set up the project dependencies using `composer install`'.PHP_EOL.
        'See https://getcomposer.org/download/ for instructions on installing Composer'.PHP_EOL;
    exit(1);
}

if ((!$loader = includeIfExists(__DIR__.'/../lib/autoload.php')))
{
    echo 'You must set up the project dependencies using `composer install`'.PHP_EOL.
        'See https://getcomposer.org/download/ for instructions on installing Composer'.PHP_EOL;
    exit(1);
}

require_once dirname(__FILE__).'/Magazine/'.'Magazine.php';
require_once dirname(__FILE__).'/Magazine/'.'Command.php';
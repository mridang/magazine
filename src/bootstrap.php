<?php
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
/** @noinspection PhpIncludeInspection */
function includeIfExists(/** @noinspection PhpDocSignatureInspection */ $file)
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
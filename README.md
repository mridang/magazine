# Magazine - Magento Packaging for the sane

Magazine helps you automatically package extensions for Magento 1.x in a sane manner. Magazine uses the cannibalised Magento core classes to build you a fully-compliant Magento package in seconds.

Requirements
------------

Magazine requires PHP version 5.1.2 or greater.

Installation
------------

The easiest way to get started with Magazine is use [Composer](http://getcomposer.org/). You can easily install Magazine system-wide with the following command:

    composer global require "mridang/magazine=*"

Make sure you have `~/.composer/vendor/bin/` in your PATH.

Or alternatively, include a dependency for `mridang/magazine` in your `composer.json` file. For example:

```json
{
    "require-dev": {
        "mridang/magazine": "0.*"
    }
}
```

You will then be able to run Magazine from the vendor bin directory:

    ./vendor/bin/magazine -h

You can also download the Magazine source and run the `magazine` command directly from the Git checkout:

    git clone git://github.com/mridang/magazine.git
    cd magazine
    php bin/magazine -h

Authors
-------

Mridang Agarwalla

License
-------

Magazine is licensed under the MIT License - see the LICENSE file for details

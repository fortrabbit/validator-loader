[![Build Status](https://travis-ci.org/fortrabbit/validator-loader.png?branch=master)](https://travis-ci.org/fortrabbit/validator-loader)

# Laravel Validation Loader

Load [Laravel validator](http://laravel.com/docs/validation) definitions from files or directories (or wherever you want to store them).

The general idea is to centralize validation (rules) to simplify maintenance and maximize re-usability.

For the service provider, integrating the loader neatly into Laravel, [look here](https://github.com/fortrabbit/validator-loader-laravel).

## Features

* Provides simple inheritance, for re-usable validation rules
* Allows variable usage, to keep the validations clean and readable
* Supports out of the box: JSON, YAML or native PHP files. Or directories containing such files.
* Flexible, extendable interface
* Can be used outside of Laravel

## Installation

``` bash
$ php composer.phar require "frbit/validator-loader:*"
```

## Example

That's how you use it:

``` php
<?php

use \Frbit\ValidatorLoader\Factory;

$loader = Factory::fromFile("my-rules.yml"); # or "my-rules.json" or "my-rules.php"

$inputData = array('email' => 'foo@bar.tld');
$validator = $loader->get('my-form', $inputData);

if ($validator->fails()) {
    # ..
}
```

And this is how the file (structure) looks like:

``` yaml
---

variables:
    FOO: /foo/
    bar: 5

validators:

    my-form:
        rules:
            email:
                - min:10

                # use the bar variable
                - max:<<bar>>

                # use the FOO variable
                - regex:<<FOO>>

        messages:
            email.min: EMail too short
            email.max: EMail too long
            email.regex: EMail not foo enough

    other-form:

        # extend from above
        extends: my-form

        rules:
            email:
                # just differ in the min rule
                - min:15

        messages:
            # just differ in the regex error message
            email.regex: You are not foo

```

### Validator files in directories

The more complex your application becomes and the more validation rules you need, the more it makes sense split
the rules into multiple files.

Each named validator (and variable) name must be unique across all files.

``` php
<?php

use \Frbit\ValidatorLoader\Factory;

$loader = Factory::fromDirectory("my-directory");
```

### Validator definition from array

If you need the flexibility.

``` php
<?php

use \Frbit\ValidatorLoader\Factory;

$loader = $loader = Factory::fromArray(array(
    'variables' => array(
        'FOO' => '/foo/',
        'bar' => 5
    ),
    'validators' => array(
        'my-form' => array(
            'rules' => array(
                'email' => array(
                    'min:10',
                    'max:<<bar>>'
                )
            ),
            'messages' => array(
                'email.min' => 'Too short',
                'email.max' => 'Too long'
            )
        )
    )
));
```

# #Using custom validation methods

[Custom validation rules](http://laravel.com/docs/validation#custom-validation-rules), with the
same signature as the [Validator::extend](https://github.com/illuminate/validation/blob/master/Validator.php)
method, can be added either in the definition (file) or programmatically. Once added, they are automatically
available in all named validators.

### Definition file

``` yaml
---

methods:
    foo: FooValidator@validate
variables:
    FOO: /foo/
    bar: 5
validators:
    my-form:
        rules:
            email:
                - foo
        messages:
            email.foo: EMail is not foo
```

### Programmatically

``` php
<?php
// ..
$loader->setMethod('foo', 'FooValidator@validate');
```

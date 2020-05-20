<p align="center">
    <img src="https://raw.githubusercontent.com/spacetab-io/obelix-php/master/obelix.jpg" alt="Docker" height=300>
</p>

Obelix 
======

[![CircleCI](https://circleci.com/gh/spacetab-io/obelix-php/tree/master.svg?style=svg)](https://circleci.com/gh/spacetab-io/obelix-php/tree/master)
[![codecov](https://codecov.io/gh/spacetab-io/obelix-php/branch/master/graph/badge.svg)](https://codecov.io/gh/spacetab-io/obelix-php)

Reads an array with dot-notation and asterix (wildcard).

## Installation

```bash
composer require spacetab-io/obelix
```

## Usage

```php
<?php
use Spacetab\Obelix;

$array = [
    'foo' => [
        'bar' => [
            'key1' => 1,
            'key2' => 2,
        ],
    ]
];

$dot = new Obelix\Dot($array);
$dot->get('foo.*.*')->getValue();
// [1, 2]

$dot->get('foo.*.*')->getMap();
// [
//   'foo.bar.key1' => 1,
//   'foo.bar.key2' => 2,
// ]
```

More examples you can found at [tests directory](./tests).

## Depends

* \>= PHP 7.4
* Composer for install package

## License

The MIT License

Copyright © 2020 spacetab.io, Inc. https://spacetab.io

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.


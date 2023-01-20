<?php

declare(strict_types=1);

namespace Spacetab\Tests\Obelix;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Spacetab\Obelix;
use stdClass;

class DotTest extends TestCase
{
    public function testWhenDeveloperPassInvalidValueToConstructor(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The items argument must be an array or Dot instance.');

        new Obelix\Dot(new stdClass());
    }

    public function testWhenDeveloperPassInvalidValueToDelimiterSetter(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The delimiter must not be an empty string.');

        $dot = new Obelix\Dot([]);
        $dot->setDelimiter('');
    }

    public function testWhenDeveloperPassInvalidValueToWildcardSetter(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The wildcard must not be an empty string.');

        $dot = new Obelix\Dot([]);
        $dot->setWildcard('');
    }

    public function testGetItemsFromSimpleArrayAndSingleValue(): void
    {
        $array = [
            'foo' => [
                'bar' => [
                    'baz' => 1
                ]
            ]
        ];

        $dot = new Obelix\Dot($array);
        $result = $dot->get('foo.bar.baz');

        $this->assertSame(1, $result->getValue());
        $this->assertSame([
            'foo.bar.baz' => 1
        ], $result->getMap());
    }

    public function testGetDefaultValueWhenValueIsEqualsSingleItemSlice(): void
    {
        $array = [
            'foo' => [
                'bar' => [
                    'baz' => 1
                ]
            ]
        ];

        $dot = new Obelix\Dot($array);
        $result = $dot->get('test.path', ['defaultValue']);

        $this->assertSame(['defaultValue'], $result->getValue());
    }

    public function testGetItemsFromSimpleArrayAndItReturnsCorrectAssociativeArray(): void
    {
        $array = [
            'foo' => [
                'bar' => [
                    'baz' => 1,
                    'taz' => 2,
                    'gaz' => 3,
                ]
            ]
        ];

        $dot = new Obelix\Dot($array);
        $result = $dot->get('foo.bar');

        $this->assertSame([
            'baz' => 1,
            'taz' => 2,
            'gaz' => 3,
        ], $result->getValue());

        $this->assertSame([
            'foo.bar' => [
                'baz' => 1,
                'taz' => 2,
                'gaz' => 3,
            ],
        ], $result->getMap());
    }

    public function testGetItemsFromSimpleArrayAndItReturnsCorrectIndexedArray(): void
    {
        $array = [
            'foo' => [
                'bar' => [
                    10 => 1,
                    20 => 2,
                    30 => 3,
                ]
            ]
        ];

        $dot = new Obelix\Dot($array);
        $result = $dot->get('foo.bar');

        $this->assertSame([
            10 => 1,
            20 => 2,
            30 => 3,
        ], $result->getValue());

        $this->assertSame([
            'foo.bar' => [
                10 => 1,
                20 => 2,
                30 => 3,
            ]
        ], $result->getMap());
    }

    public function testGetNotExistingItemsFromArray(): void
    {
        $array = [
            'foo' => [
                'bar' => [
                    10 => 1,
                    20 => 2,
                    30 => 3,
                ]
            ]
        ];

        $dot = new Obelix\Dot($array);
        $result = $dot->get('foo.bar.40', 'default');

        $this->assertSame('default', $result->getValue());
        $this->assertSame(['foo.bar.40' => 'default'], $result->getMap());
    }

    public function testGetItemByIndexFromArray(): void
    {
        $array = [
            'foo' => [
                'bar' => [1]
            ]
        ];

        $dot = new Obelix\Dot($array);
        $result = $dot->get('foo.bar.0');

        $this->assertSame(1, $result->getValue());
        $this->assertSame([
            'foo.bar.0' => 1
        ], $result->getMap());
    }

    public function testGetItemsSelectedByWildcardSimpleCase(): void
    {
        $array = [
            'foo' => [
                'bar' => [
                    ['key' => 1],
                    ['key' => 2],
                    ['key' => 3],
                    ['key' => 4],
                ],
            ]
        ];

        $dot = new Obelix\Dot($array);
        $result = $dot->get('foo.bar.*.key');

        $this->assertSame([
            1, 2, 3, 4
        ], $result->getValue());

        $this->assertSame([
            'foo.bar.0.key' => 1,
            'foo.bar.1.key' => 2,
            'foo.bar.2.key' => 3,
            'foo.bar.3.key' => 4,
        ], $result->getMap());
    }

    public function testGetItemsSelectedByWildcardAtEndPathString(): void
    {
        $k = [
            ['key' => 1],
            ['key' => 2],
            ['key' => 3],
            ['key' => 4],
        ];

        $array = [
            'foo' => [
                'bar' => $k,
            ]
        ];

        $dot = new Obelix\Dot($array);
        $result = $dot->get('foo.bar.*');

        $this->assertSame($k, $result->getValue());

        $this->assertSame([
            'foo.bar.0' => ['key' => 1],
            'foo.bar.1' => ['key' => 2],
            'foo.bar.2' => ['key' => 3],
            'foo.bar.3' => ['key' => 4],
        ], $result->getMap());
    }

    public function testGetItemsSelectedByWildcardHardCase(): void
    {
        $array = [
            'test' => [
                [
                    'nested' => [
                        [
                            'foo' => [
                                10 => ['key' => 100],
                                20 => ['key' => 200],
                                30 => ['key' => 300],
                            ]
                        ],
                        [
                            'foo' => [
                                40 => ['key' => 400],
                                ['key' => 400],
                                ['key' => 500],
                            ]
                        ]
                    ],
                ],
                [
                    'nested' => [
                        [
                            'foo' => [
                                10 => ['key' => 1000],
                                20 => ['key' => 2000],
                                30 => ['key' => 3000],
                            ]
                        ],
                        [
                            'foo' => [
                                40 => ['key' => 4000],
                                ['key' => 4000],
                                ['key' => 5000],
                            ]
                        ]
                    ],
                ]
            ],
        ];

        $dot = new Obelix\Dot($array);
        $result = $dot->get('test.*.nested.*.foo.*.key');

        $this->assertSame([
            100, 200, 300, 400, 400, 500, 1000, 2000, 3000, 4000, 4000, 5000
        ], $result->getValue());

        $this->assertSame([
            'test.0.nested.0.foo.10.key' => 100,
            'test.0.nested.0.foo.20.key' => 200,
            'test.0.nested.0.foo.30.key' => 300,
            'test.0.nested.1.foo.40.key' => 400,
            'test.0.nested.1.foo.41.key' => 400,
            'test.0.nested.1.foo.42.key' => 500,
            'test.1.nested.0.foo.10.key' => 1000,
            'test.1.nested.0.foo.20.key' => 2000,
            'test.1.nested.0.foo.30.key' => 3000,
            'test.1.nested.1.foo.40.key' => 4000,
            'test.1.nested.1.foo.41.key' => 4000,
            'test.1.nested.1.foo.42.key' => 5000,
        ], $result->getMap());
    }

    public function testDotReturnsCorrectCacheKeyValues(): void
    {
        $array = [
            'foo' => [
                'bar' => [
                    'baz' => 1
                ]
            ]
        ];

        $dot = new Obelix\Dot($array);
        $dot->get('foo.bar.baz');

        $this->assertSame([
            'foo.bar.baz' => [1, ['foo.bar.baz' => 1]]
        ], $dot->getCache());
    }

    public function testDotCacheHit(): void
    {
        $array = [
            'foo' => [
                'bar' => [
                    'baz' => 1
                ]
            ]
        ];

        $dot = new Obelix\Dot($array);
        $result = $dot->get('foo.bar.baz');

        $this->assertSame([
            'foo.bar.baz' => [1, ['foo.bar.baz' => 1]]
        ], $dot->getCache());
        $this->assertSame(1, $result->getValue());
        $this->assertSame(1, $dot->get('foo.bar.baz')->getValue());

        $keyHit = $dot->get('foo');

        $this->assertSame([
            'bar' => ['baz' => 1]
        ], $keyHit->getValue());
        $this->assertSame([
            'foo' => [
                'bar' => ['baz' => 1]
            ]
        ], $keyHit->getMap());
    }

    public function testCacheClearedOnDestructorCall(): void
    {
        $array = [
            'foo' => [
                'bar' => [
                    'baz' => 1
                ]
            ]
        ];

        $dot = new Obelix\Dot($array);
        $dot->get('foo.bar.baz');

        $this->assertSame([
            'foo.bar.baz' => [1, ['foo.bar.baz' => 1]]
        ], $dot->getCache());

        $dot->__destruct();

        $this->assertSame([], $dot->getCache());
        $this->assertSame([], $dot->toArray());
    }

    public function testHowToDotObjectAcceptsDotObject(): void
    {
        $array = [
            'foo' => [
                'bar' => [
                    'baz' => 1
                ]
            ]
        ];

        $dot1 = new Obelix\Dot($array);
        $dot1->get('foo.bar.baz');

        $dot2 = new Obelix\Dot($dot1);

        $this->assertSame([
            'foo.bar.baz' => [1, ['foo.bar.baz' => 1]]
        ], $dot2->getCache());

        $this->assertSame($array, $dot2->toArray());
    }

    public function testHowToDotObjectWorksWithOtherDelimiterAndWildcard(): void
    {
        $array = [
            'foo' => [
                'bar' => [
                    ['key' => 1],
                    ['key' => 2],
                ],
            ]
        ];

        $dot = new Obelix\Dot($array);
        $dot->setDelimiter(':');
        $dot->setWildcard('@');

        $this->assertSame(null, $dot->get('foo.bar.*.key')->getValue());
        $this->assertSame([1, 2], $dot->get('foo:bar:@:key')->getValue());
    }

    public function testGetItemWithWildcardAndAssociativeArray(): void
    {
        $array = [
            'foo' => [
                'bar' => [
                    'key1' => 1,
                    'key2' => 2,
                ],
            ]
        ];

        $dot = new Obelix\Dot($array);

        $this->assertSame([1, 2], $dot->get('foo.bar.*')->getValue());
        $this->assertSame([
            'foo.bar.key1' => 1,
            'foo.bar.key2' => 2,
        ], $dot->get('foo.bar.*')->getMap());

        $this->assertSame([1, 2], $dot->get('foo.*.*')->getValue());
        $this->assertSame([
            'foo.bar.key1' => 1,
            'foo.bar.key2' => 2,
        ], $dot->get('foo.*.*')->getMap());
    }

    public function testGetItemWhereWildcardPassedAsSingleSymbol(): void
    {
        $array = [
            [1, 3, 4]
        ];

        $dot = new Obelix\Dot($array);

        $result = $dot->get('*');
        $this->assertSame($array, $result->getValue());
        $this->assertSame([
            '*' => $array
        ], $result->getMap());
    }

    public function testGetItemWhenSecondKeyIsNotUniqueInSubArrays(): void
    {
        $array = [
            'server' => [
                'foo' => [
                    'headers' => [
                        'omg' => 'it'
                    ],
                ],
                'prerender' => [
                    'headers' => [],
                ],
                'logger'    => [
                    'enabled' => false,
                    'level'   => 'info',
                ],
                'headers' => [
                    'foo' => 'bar',
                ],
            ],
        ];

        $dot = new Obelix\Dot($array);

        $this->assertSame([
            'foo' => 'bar'
        ], $dot->get('server.headers')->getValue());

        $this->assertSame([
            'server.headers' => [
                'foo' => 'bar'
            ]
        ], $dot->get('server.headers')->getMap());
    }

    public function testWhenALotOfManyKeysAndValuesAreSameAkaHardestTest(): void
    {
        $array = [
            'foo' => [
                null,
                'foo' => 'foo',
                'bar' => 'bar',
                'baz' => [
                    'foo' => 'bar',
                    'bar' => 'foo',
                    'baz' => [
                        'foo',
                        'bar',
                        'baz' => [
                            'baz', 'foo', 'bar', 0
                        ]
                    ],
                    'acme' => [
                        'foo' => -10001,
                        'bar',
                        'baz' => [
                            'baz', 'acme'
                        ]
                    ],
                    [],
                    false,
                    null
                ],
                'faa' => [
                    'foo' => 'bar',
                    'bar' => 'foo',
                    'baz' => [
                        'faa',
                        'baa',
                        'baz' => [
                            'baz', 'foo', 'bar', 1
                        ]
                    ],
                    'acme' => [
                        'foo',
                        'bar',
                        'baz' => [
                            'baz', 'acme', 999
                        ]
                    ],
                    true,
                    [],
                    new stdClass()
                ]
            ],
            'bar' => 1,
            'acme' => [
                'foo',
                'bar',
                'baz' => [
                    'baz'
                ]
            ],
            1,
            0
        ];

        $dot = new Obelix\Dot($array);

        $result = $dot->get('foo.0');
        $this->assertSame(null, $result->getValue());
        $this->assertSame(['foo.0' => null], $result->getMap());

        $result = $dot->get('foo.baz.baz.0');
        $this->assertSame('foo', $result->getValue());
        $this->assertSame(['foo.baz.baz.0' => 'foo'], $result->getMap());

        $expected = [
            'foo' => -10001,
            'bar',
            'baz' => [
                'baz', 'acme'
            ]
        ];
        $result = $dot->get('foo.baz.acme');
        $this->assertSame($expected, $result->getValue());
        $this->assertSame(['foo.baz.acme' => $expected], $result->getMap());

        $result = $dot->get('foo.faa.2');
        $this->assertInstanceOf(stdClass::class, $result->getValue());
        $this->assertSame($array['foo']['faa'][2], $result->getValue());
        $this->assertSame(['foo.faa.2' => $array['foo']['faa'][2]], $result->getMap());

        $result = $dot->get('foo.faa.0');
        $this->assertSame(true, $result->getValue());
        $this->assertSame(['foo.faa.0' => true], $result->getMap());

        $expected = [
            'baz', 'acme', 999
        ];
        $result = $dot->get('foo.faa.acme.baz');
        $this->assertSame($expected, $result->getValue());
        $this->assertSame(['foo.faa.acme.baz' => $expected], $result->getMap());

        $expected = [
            'baz', 'foo', 'bar', 0
        ];
        $result = $dot->get('foo.baz.baz.baz.*');
        $this->assertSame($expected, $result->getValue());
        $this->assertSame([
            'foo.baz.baz.baz.0' => 'baz',
            'foo.baz.baz.baz.1' => 'foo',
            'foo.baz.baz.baz.2' => 'bar',
            'foo.baz.baz.baz.3' => 0,
        ], $result->getMap());
    }
}

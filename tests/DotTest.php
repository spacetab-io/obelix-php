<?php

declare(strict_types=1);

namespace Spacetab\Tests\Obelix;

use PHPUnit\Framework\TestCase;
use Spacetab\Obelix;

class DotTest extends TestCase
{
    public function testGetItemsFromSimpleArrayAndSingleValue()
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

    public function testGetItemsFromSimpleArrayAndItReturnsCorrectAssociativeArray()
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

    public function testGetItemsFromSimpleArrayAndItReturnsCorrectIndexedArray()
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

    public function testGetNotExistingItemsFromArray()
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
        $this->assertSame([], $result->getMap());
    }

    public function testGetItemByIndexFromArray()
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

    public function testGetItemsSelectedByWildcardSimpleCase()
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

    public function testGetItemsSelectedByWildcardAtEndPathString()
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

    public function testGetItemsSelectedByWildcardHardCase()
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

    public function testDotReturnsCorrectCacheKeyValues()
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

    public function testDotCacheHit()
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

    public function testCacheClearedOnDestructorCall()
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

    public function testHowToDotObjectAcceptsDotObject()
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

    public function testHowToDotObjectWorksWithOtherDelimiterAndWildcard()
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

    public function testGetItemWithWildcardAndAssociativeArray()
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
}

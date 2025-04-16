<?php

/**
 * This file is part of php-generator-chunk.
 *
 * (c) 2025 Alexandre Bordji <arezki.bordji@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license https://opensource.org/licenses/MIT
 * @link https://github.com/abordji/php-generator-chunk
 */

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;

class GeneratorChunkTest extends TestCase
{
    public function testChunkingWithPositiveLength(): void
    {
        $generator = (function () {
            yield 1;
            yield 2;
            yield 3;
            yield 4;
            yield 5;
        })();

        $chunks = $this->dump(generator_chunk($generator, 2));

        $this->assertCount(3, $chunks);
        $this->assertEquals($this->dump([1, 2]), $chunks[0]);
        $this->assertEquals($this->dump([3, 4]), $chunks[1]);
        $this->assertEquals($this->dump([5]), $chunks[2]);
    }

    public function testChunkingWithPreservedKeys(): void
    {
        $generator = (function () {
            yield 'a' => 1;
            yield 'b' => 2;
            yield 'c' => 3;
        })();

        $chunks = $this->dump(generator_chunk($generator, 2, true));

        $this->assertCount(2, $chunks);
        $this->assertEquals($this->dump(['a' => 1, 'b' => 2]), $chunks[0]);
        $this->assertEquals($this->dump(['c' => 3]), $chunks[1]);
    }

    public function testChunkingWithNonDivisibleLength(): void
    {
        $generator = (function () {
            yield 10;
            yield 20;
            yield 30;
            yield 40;
        })();

        $chunks = $this->dump(generator_chunk($generator, 3));

        $this->assertCount(2, $chunks);
        $this->assertEquals($this->dump([10, 20, 30]), $chunks[0]);
        $this->assertEquals($this->dump([40]), $chunks[1]);
    }

    public function testChunkingEmptyGenerator(): void
    {
        $generator = (function () {
            yield from [];
        })();

        $chunks = $this->dump(generator_chunk($generator, 2));

        $this->assertCount(0, $chunks);
    }

    public function testChunkingWithLengthEqualToGeneratorSize(): void
    {
        $generator = (function () {
            yield 'x' => 100;
            yield 'y' => 200;
        })();

        $chunks = $this->dump(generator_chunk($generator, 2, true));

        $this->assertCount(1, $chunks);
        $this->assertEquals($this->dump(['x' => 100, 'y' => 200]), $chunks[0]);
    }

    public function testChunkingWithLengthGreaterThanGeneratorSize(): void
    {
        $generator = (function () {
            yield 'p' => 5;
        })();

        $chunks = $this->dump(generator_chunk($generator, 3, true));

        $this->assertCount(1, $chunks);
        $this->assertEquals($this->dump(['p' => 5]), $chunks[0]);
    }

    public function testChunkingWithLengthOne(): void
    {
        $generator = (function () {
            yield 'first' => 'a';
            yield 'second' => 'b';
            yield 'third' => 'c';
        })();

        $chunks = $this->dump(generator_chunk($generator, 1, true));

        $this->assertCount(3, $chunks);
        $this->assertEquals($this->dump(['first' => 'a']), $chunks[0]);
        $this->assertEquals($this->dump(['second' => 'b']), $chunks[1]);
        $this->assertEquals($this->dump(['third' => 'c']), $chunks[2]);
    }

    public function testChunkingWithoutKeyPreservation(): void
    {
        $generator = (function () {
            yield 'alpha' => 1;
            yield 'beta' => 2;
            yield 'gamma' => 3;
        })();

        $chunks = $this->dump(generator_chunk($generator, 2, false));

        $this->assertCount(2, $chunks);
        $this->assertEquals($this->dump([1, 2]), $chunks[0]);
        $this->assertEquals($this->dump([3]), $chunks[1]);
    }

    public function testChunkingWithDuplicateKeysAndPreservedKeys(): void
    {
        $generator = (function () {
            yield 'a' => 1;
            yield 'b' => 2;
            yield 'a' => 3; // Duplicate key 'a'
            yield 'c' => 4;
            yield 'b' => 5; // Duplicate key 'b'
        })();

        $chunks = $this->dump(generator_chunk($generator, 3, true));

        $this->assertCount(2, $chunks);
        $this->assertEquals([['a' => 1],['b' => 2], ['a' => 3]], $chunks[0]);
        $this->assertEquals([['c' => 4], ['b' => 5]], $chunks[1]);
    }

    public function testChunkingWithDuplicateKeysAndNoPreservedKeys(): void
    {
        $generator = (function () {
            yield 'a' => 1;
            yield 'b' => 2;
            yield 'a' => 3; // Duplicate key 'a'
            yield 'c' => 4;
        })();

        $chunks = $this->dump(generator_chunk($generator, 3, false));

        $this->assertCount(2, $chunks);
        $this->assertEquals([[0 => 1], [1 => 2], [2 => 3]], $chunks[0]);
        $this->assertEquals([[0 => 4]], $chunks[1]);
    }

    public function testChunkingWithZeroLengthThrowsException(): void
    {
        $this->expectException(\ValueError::class);
        $generator = (function () { yield 1; })();

        generator_chunk($generator, 0)->valid();
    }

    public function testChunkingWithNegativeLengthThrowsException(): void
    {
        $this->expectException(\ValueError::class);
        $generator = (function () { yield 1; })();

        generator_chunk($generator, -1)->valid();
    }

    private function dump(iterable $iterable): array
    {
        $chunks = [];
        foreach ($iterable as $key => $value) {
            $chunks[] = is_iterable($value) ? $this->dump($value) : [$key => $value];
        }

        return $chunks;
    }
}

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

if (!function_exists('generator_chunk')) {
    /**
     * Splits a PHP Generator into chunks of a specified length.
     *
     * @param Generator $generator The Generator to be chunked.
     * @param int $length The length of each chunk. Must be greater than 0.
     * @param bool $preserve_keys Whether to preserve the original keys. Defaults to false.
     *
     * @return Generator A Generator where each element is itself a Generator.
     *
     * @throws ValueError If $length is less than or equal to 0.
     */
    function generator_chunk(Generator $generator, int $length, bool $preserve_keys = false): Generator
    {
        if ($length <= 0) {
            throw new ValueError('generator_chunk(): Argument #2 ($length) must be greater than 0');
        }

        $chunk = static function () use ($generator, $length, $preserve_keys): Generator {
            for ($i = 0; $i < $length && $generator->valid(); ++$i) {
                if ($preserve_keys) {
                    yield $generator->key() => $generator->current();
                } else {
                    yield $generator->current();
                }

                if ($i + 1 < $length) {
                    $generator->next();
                }
            }
        };

        while ($generator->valid()) {
            yield $chunk();
            $generator->next();
        }

        return $generator->getReturn();
    }
}

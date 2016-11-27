<?php

declare(strict_types = 1);

namespace MCordingley\LinearAlgebraTest;

use MCordingley\LinearAlgebra\Decomposition\LU;
use MCordingley\LinearAlgebra\Matrix;
use MCordingley\LinearAlgebra\MatrixException;
use PHPUnit_Framework_TestCase;

final class LUTest extends PHPUnit_Framework_TestCase
{
    public function testNonSquare()
    {
        $matrix = new Matrix([
            [2,  3,  1,  5],
            [6, 13,  5, 19],
            [2, 19, 10, 23],
        ]);

        static::expectException(MatrixException::class);

        new LU($matrix);
    }

    public function testLower()
    {
        $lower = $this->getDecomposition()->lower();

        static::assertEquals(1, $lower->get(0, 0));
        static::assertEquals(0, $lower->get(0, 1));
        static::assertEquals(0, $lower->get(0, 2));
        static::assertEquals(0, $lower->get(0, 3));

        static::assertEquals(3, $lower->get(1, 0));
        static::assertEquals(1, $lower->get(1, 1));
        static::assertEquals(0, $lower->get(1, 2));
        static::assertEquals(0, $lower->get(1, 3));

        static::assertEquals(1, $lower->get(2, 0));
        static::assertEquals(4, $lower->get(2, 1));
        static::assertEquals(1, $lower->get(2, 2));
        static::assertEquals(0, $lower->get(2, 3));

        static::assertEquals(2, $lower->get(3, 0));
        static::assertEquals(1, $lower->get(3, 1));
        static::assertEquals(7, $lower->get(3, 2));
        static::assertEquals(1, $lower->get(3, 3));
    }

    private function getDecomposition(): LU
    {
        $matrix = new Matrix([
            [2,  3,  1,  5],
            [6, 13,  5, 19],
            [2, 19, 10, 23],
            [4, 10, 11, 31],
        ]);

        return new LU($matrix);
    }

    public function testUpper()
    {
        $upper = $this->getDecomposition()->upper();

        static::assertEquals(2, $upper->get(0, 0));
        static::assertEquals(3, $upper->get(0, 1));
        static::assertEquals(1, $upper->get(0, 2));
        static::assertEquals(5, $upper->get(0, 3));

        static::assertEquals(0, $upper->get(1, 0));
        static::assertEquals(4, $upper->get(1, 1));
        static::assertEquals(2, $upper->get(1, 2));
        static::assertEquals(4, $upper->get(1, 3));

        static::assertEquals(0, $upper->get(2, 0));
        static::assertEquals(0, $upper->get(2, 1));
        static::assertEquals(1, $upper->get(2, 2));
        static::assertEquals(2, $upper->get(2, 3));

        static::assertEquals(0, $upper->get(3, 0));
        static::assertEquals(0, $upper->get(3, 1));
        static::assertEquals(0, $upper->get(3, 2));
        static::assertEquals(3, $upper->get(3, 3));
    }
}

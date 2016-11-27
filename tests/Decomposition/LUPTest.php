<?php

declare(strict_types = 1);

namespace MCordingley\LinearAlgebraTest;

use MCordingley\LinearAlgebra\Decomposition\LUP;
use MCordingley\LinearAlgebra\Matrix;
use MCordingley\LinearAlgebra\MatrixException;
use PHPUnit_Framework_TestCase;

final class LUPTest extends PHPUnit_Framework_TestCase
{
    public function testNonSquare()
    {
        $matrix = new Matrix([
            [2,  3,  1,  5],
            [6, 13,  5, 19],
            [2, 19, 10, 23],
        ]);

        static::expectException(MatrixException::class);

        new LUP($matrix);
    }

    public function testSingularMatrix()
    {
        $matrix = new Matrix([
            [0, 1],
            [0, 1],
        ]);

        static::expectException(MatrixException::class);

        new LUP($matrix);
    }

    public function testLower()
    {
        $lower = $this->getDecomposition()->lower();

        static::assertEquals(1, $lower->get(0, 0));
        static::assertEquals(0, $lower->get(0, 1));
        static::assertEquals(0, $lower->get(0, 2));
        static::assertEquals(0, $lower->get(0, 3));

        static::assertEquals(0.4, $lower->get(1, 0));
        static::assertEquals(1, $lower->get(1, 1));
        static::assertEquals(0, $lower->get(1, 2));
        static::assertEquals(0, $lower->get(1, 3));

        static::assertEquals(-0.2, $lower->get(2, 0));
        static::assertEquals(0.5, $lower->get(2, 1));
        static::assertEquals(1, $lower->get(2, 2));
        static::assertEquals(0, $lower->get(2, 3));

        static::assertEquals(0.6, $lower->get(3, 0));
        static::assertEquals(0, $lower->get(3, 1));
        static::assertEquals(0.4, $lower->get(3, 2));
        static::assertEquals(1, $lower->get(3, 3));
    }

    private function getDecomposition(): LUP
    {
        $matrix = new Matrix([
            [ 2,  0,    2, 0.6],
            [ 3,  3,    4,  -2],
            [ 5,  5,    4,   2],
            [-1, -2,  3.4,  -1],
        ]);

        return new LUP($matrix);
    }
    public function testUpper()
    {
        $upper = $this->getDecomposition()->upper();

        static::assertEquals(5, $upper->get(0, 0));
        static::assertEquals(5, $upper->get(0, 1));
        static::assertEquals(4, $upper->get(0, 2));
        static::assertEquals(2, $upper->get(0, 3));

        static::assertEquals(0, $upper->get(1, 0));
        static::assertEquals(-2, $upper->get(1, 1));
        static::assertEquals(0.4, $upper->get(1, 2));
        static::assertEquals(-0.2, $upper->get(1, 3));

        static::assertEquals(0, $upper->get(2, 0));
        static::assertEquals(0, $upper->get(2, 1));
        static::assertEquals(4, $upper->get(2, 2));
        static::assertEquals(-0.5, $upper->get(2, 3));

        static::assertEquals(0, $upper->get(3, 0));
        static::assertEquals(0, $upper->get(3, 1));
        static::assertEquals(0, $upper->get(3, 2));
        static::assertEquals(-3, $upper->get(3, 3));
    }

    public function testParity()
    {
        static::assertEquals(3, $this->getDecomposition()->parity());
    }

    public function testPermutationArray()
    {
        $permutation = $this->getDecomposition()->permutationArray();

        static::assertEquals(2, $permutation[0]);
        static::assertEquals(0, $permutation[1]);
        static::assertEquals(3, $permutation[2]);
        static::assertEquals(1, $permutation[3]);
    }

    public function testPermutationMatrix()
    {
        $permutation = $this->getDecomposition()->permutationMatrix();

        static::assertEquals(0, $permutation->get(0, 0));
        static::assertEquals(0, $permutation->get(0, 1));
        static::assertEquals(1, $permutation->get(0, 2));
        static::assertEquals(0, $permutation->get(0, 3));

        static::assertEquals(1, $permutation->get(1, 0));
        static::assertEquals(0, $permutation->get(1, 1));
        static::assertEquals(0, $permutation->get(1, 2));
        static::assertEquals(0, $permutation->get(1, 3));

        static::assertEquals(0, $permutation->get(2, 0));
        static::assertEquals(0, $permutation->get(2, 1));
        static::assertEquals(0, $permutation->get(2, 2));
        static::assertEquals(1, $permutation->get(2, 3));

        static::assertEquals(0, $permutation->get(3, 0));
        static::assertEquals(1, $permutation->get(3, 1));
        static::assertEquals(0, $permutation->get(3, 2));
        static::assertEquals(0, $permutation->get(3, 3));
    }
}

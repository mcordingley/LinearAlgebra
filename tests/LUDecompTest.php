<?php

namespace mcordingley\LinearAlgebra;

class LUDecompTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Builds a nonsingular matrix
     *
     * @return Matrix
     */
    private function buildMatrix()
    {
        return new Matrix([
            [1, -1, 2],
            [1, -1, 1],
            [2, 3, -1],
        ]);
    }

    public function testNonSquare()
    {
        static::expectException(MatrixException::class);

        new LUDecomposition([[1, 2, 3]]);
    }

    public function testSingularByZeroes()
    {
        static::expectException(MatrixException::class);

        new LUDecomposition([
            [0, 0],
            [0, 0],
        ]);
    }

    public function testWrongSizeKnowns()
    {
        $matrix = $this->buildMatrix();
        $decomp = new LUDecomposition($matrix->toArray());

        static::expectException(MatrixException::class);

        $decomp->solve([1]);
    }

    public function testSingularByDiagonal()
    {
        static::expectException(MatrixException::class);

        new LUDecomposition([
            [1, -1, 2],
            [1, 0, 1],
            [2, 3, -1],
        ]);
    }

    public function testLUDecompConstructor()
    {
        $matrix = $this->buildMatrix();

        $LU = new LUDecomposition($matrix->toArray());

        $this->assertEquals(1, $LU->get(0, 0));
        $this->assertEquals(-1, $LU->get(0, 1));
        $this->assertEquals(1, $LU->get(0, 2));
        $this->assertEquals(2, $LU->get(1, 0));
        $this->assertEquals(5, $LU->get(1, 1));
        $this->assertEquals(-3, $LU->get(1, 2));
        $this->assertEquals(1, $LU->get(2, 0));
        $this->assertEquals(0, $LU->get(2, 1));
        $this->assertEquals(1, $LU->get(2, 2));
    }

    public function testDeterminant()
    {
        $matrix = $this->buildMatrix();

        $LU = new LUDecomposition($matrix->toArray());

        $this->assertEquals(5, $LU->determinant());
    }

    public function testSolve()
    {
        $matrix = new Matrix([
            [1, 1, -1],
            [3, 1, 1],
            [1, -1, 4],
        ]);

        $LU = new LUDecomposition($matrix->toArray());

        $b = [1, 9, 8];

        $x = $LU->solve($b);

        $expected_x = [3, -1, 1];
        $this->assertEquals($expected_x, $x);

        // Try another one
        $matrix = new Matrix([
            [1, 2, 0, -4],
            [-1, 0, 6, 2],
            [3, -2, -25, 0],
            [-2, -3, 4, 4],
        ]);

        $LU = new LUDecomposition($matrix->toArray());

        $b = [-1, 7, -24, 3];

        $x = $LU->solve($b);

        $message = 'Not within tolerance';
        $delta = 0.00000001;
        $this->assertEquals(1, $x[0], $message, $delta);
        $this->assertEquals(1, $x[1], $message, $delta);
        $this->assertEquals(1, $x[2], $message, $delta);
        $this->assertEquals(1, $x[3], $message, $delta);
    }

    public function testInverse()
    {
        $matrix = $this->buildMatrix();

        $LU = new LUDecomposition($matrix->toArray());

        $inverse = $LU->inverse();

        $message = 'Not within tolerance';
        $delta = 0.00000001;
        $this->assertEquals(-0.4, $inverse->get(0, 0), $message, $delta);
        $this->assertEquals(0.6, $inverse->get(1, 0), $message, $delta);
        $this->assertEquals(1, $inverse->get(2, 0), $message, $delta);
        $this->assertEquals(1, $inverse->get(0, 1), $message, $delta);
        $this->assertEquals(-1, $inverse->get(1, 1), $message, $delta);
        $this->assertEquals(-1, $inverse->get(2, 1), $message, $delta);
        $this->assertEquals(0.2, $inverse->get(0, 2), $message, $delta);
        $this->assertEquals(0.2, $inverse->get(1, 2), $message, $delta);
        $this->assertEquals(0, $inverse->get(2, 2), $message, $delta);
    }
}
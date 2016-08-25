<?php

namespace mcordingley\LinearAlgebra;

class MatrixTest extends \PHPUnit_Framework_TestCase
{
    private function buildMatrix()
    {
        return new Matrix([
            [1, 2, 3, 4],
            [5, 6, 7, 8],
            [9, 10, 11, 12],
        ]);
    }

    public function testBadConstruction()
    {
        static::expectException(MatrixException::class);

        new Matrix([
            [1, 2, 3, 4],
            [5, 6, 7],
            [9, 10],
        ]);
    }

    public function testEmptyRows()
    {
        static::expectException(MatrixException::class);

        new Matrix([]);
    }

    public function testEmptyColumns()
    {
        static::expectException(MatrixException::class);

        new Matrix([
            [],
            [],
        ]);
    }

    public function testSize()
    {
        $matrix = $this->buildMatrix();

        static::assertEquals(3, $matrix->getRowCount());
        static::assertEquals(4, $matrix->getColumnCount());
    }

    public function testIdentity()
    {
        $identity = Matrix::identity(3);

        static::assertEquals(1, $identity->get(0, 0));
        static::assertEquals(0, $identity->get(0, 1));
        static::assertEquals(0, $identity->get(0, 2));
        static::assertEquals(0, $identity->get(1, 0));
        static::assertEquals(1, $identity->get(1, 1));
        static::assertEquals(0, $identity->get(1, 2));
        static::assertEquals(0, $identity->get(2, 0));
        static::assertEquals(0, $identity->get(2, 1));
        static::assertEquals(1, $identity->get(2, 2));
    }

    public function testMap()
    {
        $matrix = new Matrix([
            [1, 2],
            [3, 4],
        ]);

        $mapped = $matrix->map(function ($value, $row, $column) {
            return $value + $row + $column;
        });

        static::assertEquals(1, $mapped->get(0, 0));
        static::assertEquals(3, $mapped->get(0, 1));
        static::assertEquals(4, $mapped->get(1, 0));
        static::assertEquals(6, $mapped->get(1, 1));
    }

    public function testGet()
    {
        $matrix = $this->buildMatrix();

        // Check constructed value
        static::assertEquals(8, $matrix->get(1, 3));
    }

    public function testEquals()
    {
        $matrix1 = new Matrix([
            [1, 2, 3],
            [4, 5, 6],
            [7, 8, 9],
        ]);

        $matrix2 = new Matrix([
            [4, 2, 6],
            [1, 7, 3],
            [7, 3, 2],
        ]);

        static::assertTrue($matrix1->equals($matrix1));
        static::assertFalse($matrix1->equals($matrix2));
    }

    public function testAddMatrix()
    {
        $matrix1 = new Matrix([
            [1, 2, 3],
            [4, 5, 6],
            [7, 8, 9],
        ]);

        $matrix2 = new Matrix([
            [4, 2, 6],
            [1, 7, 3],
            [7, 3, 2],
        ]);

        $added = $matrix1->addMatrix($matrix2);

        static::assertEquals(5, $added->get(0, 0));
        static::assertEquals(4, $added->get(0, 1));
        static::assertEquals(9, $added->get(0, 2));
        static::assertEquals(5, $added->get(1, 0));
        static::assertEquals(12, $added->get(1, 1));
        static::assertEquals(9, $added->get(1, 2));
        static::assertEquals(14, $added->get(2, 0));
        static::assertEquals(11, $added->get(2, 1));
        static::assertEquals(11, $added->get(2, 2));
    }

    public function testAddMatrixWrongSizes()
    {
        $matrix1 = $this->buildMatrix();

        $matrix2 = new Matrix([
            [4, 2, 6],
            [1, 7, 3],
            [7, 3, 2],
        ]);

        static::expectException(MatrixException::class);

        $matrix1->addMatrix($matrix2);
    }

    public function testAddScalar()
    {
        $matrix = new Matrix([
            [4, 2, 6],
            [1, 7, 3],
            [7, 3, 2],
        ]);

        $added = $matrix->addScalar(4);

        static::assertEquals(8, $added->get(0, 0));
        static::assertEquals(6, $added->get(0, 1));
        static::assertEquals(10, $added->get(0, 2));
        static::assertEquals(5, $added->get(1, 0));
        static::assertEquals(11, $added->get(1, 1));
        static::assertEquals(7, $added->get(1, 2));
        static::assertEquals(11, $added->get(2, 0));
        static::assertEquals(7, $added->get(2, 1));
        static::assertEquals(6, $added->get(2, 2));
    }

    public function testSubtractMatrix()
    {
        $matrix1 = new Matrix([
            [1, 2, 3],
            [4, 5, 6],
            [7, 8, 9],
        ]);

        $matrix2 = new Matrix([
            [4, 2, 6],
            [1, 7, 3],
            [7, 3, 2],
        ]);

        $subtracted = $matrix1->subtractMatrix($matrix2);

        static::assertEquals(-3, $subtracted->get(0, 0));
        static::assertEquals(0, $subtracted->get(0, 1));
        static::assertEquals(-3, $subtracted->get(0, 2));
        static::assertEquals(3, $subtracted->get(1, 0));
        static::assertEquals(-2, $subtracted->get(1, 1));
        static::assertEquals(3, $subtracted->get(1, 2));
        static::assertEquals(0, $subtracted->get(2, 0));
        static::assertEquals(5, $subtracted->get(2, 1));
        static::assertEquals(7, $subtracted->get(2, 2));
    }

    public function testSubtractMatrixWrongSizes()
    {
        $matrix1 = $this->buildMatrix();

        $matrix2 = new Matrix([
            [4, 2, 6],
            [1, 7, 3],
            [7, 3, 2],
        ]);

        static::expectException(MatrixException::class);

        $matrix1->subtractMatrix($matrix2);
    }

    public function testSubtractScalar()
    {
        $matrix = new Matrix([
            [4, 2, 6],
            [1, 7, 3],
            [7, 3, 2],
        ]);

        $subtracted = $matrix->subtractScalar(4);

        static::assertEquals(0, $subtracted->get(0, 0));
        static::assertEquals(-2, $subtracted->get(0, 1));
        static::assertEquals(2, $subtracted->get(0, 2));
        static::assertEquals(-3, $subtracted->get(1, 0));
        static::assertEquals(3, $subtracted->get(1, 1));
        static::assertEquals(-1, $subtracted->get(1, 2));
        static::assertEquals(3, $subtracted->get(2, 0));
        static::assertEquals(-1, $subtracted->get(2, 1));
        static::assertEquals(-2, $subtracted->get(2, 2));
    }

    public function testMultiplyMatrix()
    {
        $matrix1 = new Matrix([
            [1, 2, 3],
            [4, 5, 6],
        ]);

        $matrix2 = new Matrix([
            [7, 8],
            [9, 10],
            [11, 12],
        ]);

        $multiplied = $matrix1->multiplyMatrix($matrix2);

        static::assertEquals(58, $multiplied->get(0, 0));
        static::assertEquals(64, $multiplied->get(0, 1));
        static::assertEquals(139, $multiplied->get(1, 0));
        static::assertEquals(154, $multiplied->get(1, 1));
    }

    public function testMultiplyMatrixWrongSizes()
    {
        $matrix1 = $this->buildMatrix();

        $matrix2 = new Matrix([
            [4, 2, 6],
            [1, 7, 3],
            [7, 3, 2],
        ]);

        static::expectException(MatrixException::class);

        $matrix1->multiplyMatrix($matrix2);
    }

    public function testMultiplyScalar()
    {
        $matrix = new Matrix([
            [4, 2, 6],
            [1, 7, 3],
            [7, 3, 2],
        ]);

        $multiplied = $matrix->multiplyScalar(2);

        static::assertEquals(8, $multiplied->get(0, 0));
        static::assertEquals(4, $multiplied->get(0, 1));
        static::assertEquals(12, $multiplied->get(0, 2));
        static::assertEquals(2, $multiplied->get(1, 0));
        static::assertEquals(14, $multiplied->get(1, 1));
        static::assertEquals(6, $multiplied->get(1, 2));
        static::assertEquals(14, $multiplied->get(2, 0));
        static::assertEquals(6, $multiplied->get(2, 1));
        static::assertEquals(4, $multiplied->get(2, 2));
    }

    public function testDiagonal()
    {
        $matrix = new Matrix([
            [1, 2, 3],
            [4, 5, 6],
            [7, 8, 9],
        ]);

        $diagonal = $matrix->diagonal();

        static::assertEquals(1, $diagonal->rows);
        static::assertEquals(3, $diagonal->columns);
        static::assertEquals(1, $diagonal->get(0, 0));
        static::assertEquals(5, $diagonal->get(0, 1));
        static::assertEquals(9, $diagonal->get(0, 2));
    }

    public function testTrace()
    {
        $matrix = new Matrix([
            [1, 2, 3],
            [0, 1, 4],
            [5, 6, 0],
        ]);

        static::assertEquals(2, $matrix->trace());
    }

    public function testRectangularTrace()
    {
        $matrix = $this->buildMatrix();

        static::expectException(MatrixException::class);

        $matrix->trace();
    }

    public function testTranspose()
    {
        $matrix = $this->buildMatrix()->transpose();

        static::assertEquals(5, $matrix->get(0, 1));
        static::assertEquals(7, $matrix->get(2, 1));
        static::assertEquals(10, $matrix->get(1, 2));
    }

    public function testInverse()
    {
        $matrix = new Matrix([
            [1, 2, 3],
            [0, 1, 4],
            [5, 6, 0],
        ]);

        $inverse = $matrix->inverse();

        static::assertEquals(-24, $inverse->get(0, 0));
        static::assertEquals(18, $inverse->get(0, 1));
        static::assertEquals(5, $inverse->get(0, 2));
        static::assertEquals(20, $inverse->get(1, 0));
        static::assertEquals(-15, $inverse->get(1, 1));
        static::assertEquals(-4, $inverse->get(1, 2));
        static::assertEquals(-5, $inverse->get(2, 0));
        static::assertEquals(4, $inverse->get(2, 1));
        static::assertEquals(1, $inverse->get(2, 2));
    }

    public function testNonSquareInverse()
    {
        $matrix = $this->buildMatrix();

        static::expectException(MatrixException::class);

        $matrix->inverse();
    }

    public function testAdjoint()
    {
        $matrix = new Matrix([
            [1, -1, 2],
            [4, 0, 6],
            [0, 1, -1],
        ]);

        $adjoint = $matrix->adjoint();

        static::assertEquals(-6, $adjoint->get(0, 0));
        static::assertEquals(1, $adjoint->get(0, 1));
        static::assertEquals(-6, $adjoint->get(0, 2));
        static::assertEquals(4, $adjoint->get(1, 0));
        static::assertEquals(-1, $adjoint->get(1, 1));
        static::assertEquals(2, $adjoint->get(1, 2));
        static::assertEquals(4, $adjoint->get(2, 0));
        static::assertEquals(-1, $adjoint->get(2, 1));
        static::assertEquals(4, $adjoint->get(2, 2));
    }

    public function testRectangularAdjoint()
    {
        $matrix = $this->buildMatrix();

        static::expectException(MatrixException::class);

        $matrix->adjoint();
    }

    public function testConcatenateBottom()
    {
        $matrixA = new Matrix([[1, 2, 3]]);
        $matrixB = new Matrix([[4, 5, 6]]);

        $concatenated = $matrixA->concatenateBottom($matrixB);

        static::assertEquals([
            [1, 2, 3],
            [4, 5, 6],
        ], $concatenated->toArray());
    }

    public function testConcatenateBottomWrongSizes()
    {
        $matrixA = new Matrix([[1, 2, 3]]);
        $matrixB = new Matrix([[4], [5], [6]]);

        static::expectException(MatrixException::class);

        $matrixA->concatenateBottom($matrixB);
    }

    public function testConcatenateRight()
    {
        $matrixA = new Matrix([[1], [2], [3]]);
        $matrixB = new Matrix([[4], [5], [6]]);

        $concatenated = $matrixA->concatenateRight($matrixB);

        static::assertEquals([
            [1, 4],
            [2, 5],
            [3, 6],
        ], $concatenated->toArray());
    }

    public function testConcatenateRightWrongSizes()
    {
        $matrixA = new Matrix([[1, 2, 3]]);
        $matrixB = new Matrix([[4], [5], [6]]);

        static::expectException(MatrixException::class);

        $matrixA->concatenateRight($matrixB);
    }

    public function testDeterminant()
    {
        $matrix = new Matrix([
            [6, 1, 1],
            [4, -2, 5],
            [2, 8, 7],
        ]);

        static::assertEquals(-306, $matrix->determinant());
    }

    public function testNonSquareDeterminant()
    {
        $matrix = $this->buildMatrix();

        static::expectException(MatrixException::class);

        $matrix->determinant();
    }
}

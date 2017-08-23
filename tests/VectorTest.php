<?php

declare(strict_types = 1);

namespace MCordingley\LinearAlgebraTest;

use MCordingley\LinearAlgebra\Matrix;
use MCordingley\LinearAlgebra\MatrixException;
use MCordingley\LinearAlgebra\Vector;

class VectorTest extends \PHPUnit_Framework_TestCase
{
    private function buildVector()
    {
        return new Vector([1, 2, 3, 4]);
    }

    public function testEmptyCount()
    {
        static::expectException(MatrixException::class);

        new Vector([]);
    }

    public function testConstructor()
    {
        $this->assertInstanceOf(Vector::class, self::buildVector());
    }

    public function testMap()
    {
        $vector = self::buildVector();

        $mapped = $vector->map(function ($value, $row, $column) {
            return $value + $row + $column;
        });

        static::assertInstanceOf(Matrix::class, $mapped);

        static::assertEquals(1, $mapped->get(0, 0));
        static::assertEquals(3, $mapped->get(0, 1));
        static::assertEquals(5, $mapped->get(0, 2));
        static::assertEquals(7, $mapped->get(0, 3));
    }

    public function testGetSize()
    {
        $vector = self::buildVector();

        $this->assertEquals(4, $vector->getSize());
    }

    public function testAddVector()
    {
        $vector1 = new Vector([1, 2, 3, 4]);
        $vector2 = new Vector([-1, 3, 5, 2]);

        $added = $vector1->addVector($vector2);

        static::assertEquals(0, $added->get(0, 0));
        static::assertEquals(5, $added->get(0, 1));
        static::assertEquals(8, $added->get(0, 2));
        static::assertEquals(6, $added->get(0, 3));
    }

    public function testSubtractVector()
    {
        $vector1 = new Vector([1, 2, 3, 4]);
        $vector2 = new Vector([-1, 3, 5, 2]);

        $added = $vector1->subtractVector($vector2);

        static::assertEquals(2, $added->get(0, 0));
        static::assertEquals(-1, $added->get(0, 1));
        static::assertEquals(-2, $added->get(0, 2));
        static::assertEquals(2, $added->get(0, 3));
    }

    public function testSum()
    {
        $vector = self::buildVector();

        $this->assertEquals(10, $vector->sum());
    }

    public function testLength()
    {
        $vector = new Vector([3, 4]);

        $this->assertEquals(5, $vector->length());
    }

    public function testMagnitude()
    {
        $vector = new Vector([3, 4]);

        $this->assertEquals(5, $vector->magnitude());
    }

    public function testDotProduct()
    {
        $vector1 = new Vector([1, 2, 3, 4]);
        $vector2 = new Vector([-1, 3, 5, 2]);

        $this->assertEquals((1 * -1) + (2 * 3) + (3 * 5) + (4 * 2), $vector1->dotProduct($vector2));
    }

    public function testOuterProduct()
    {
        $vector1 = new Vector([1, 2]);
        $vector2 = new Vector([3, 4, 5]);

        $this->assertEquals(new Matrix([[3, 4, 5], [6, 8, 10]]), $vector1->outerProduct($vector2));

        //this test case appear  A ⨂ B != B ⨂ A
        $vector1 = new Vector([3, 4, 5]);
        $vector2 = new Vector([1, 2]);

        $this->assertEquals(new Matrix([[3, 6], [4, 8], [5, 10]]), $vector1->outerProduct($vector2));
    }

    public function testCrossProduct()
    {
        $vector1 = new Vector([2, 3, 4]);
        $vector2 = new Vector([5, 6, 7]);

        $this->assertEquals(new Vector([-3, 6, -3]), $vector1->crossProduct($vector2));
    }

    public function testNormalize()
    {
        $vector = new Vector([3, 4]);

        $this->assertEquals(new Vector([3 / 5, 4 / 5]), $vector->normalize());
    }

    public function testL1Norm()
    {
        $this->assertEquals(10, self::buildVector()->l1Norm());
    }

    public function testL2Norm()
    {
        $vector = new Vector([3, 4]);

        $this->assertEquals(5, $vector->l2Norm());
    }

    public function testProjection()
    {
        $vector1 = new Vector([1, 1]);
        $vector2 = new Vector([3, 1]);

        $this->assertEquals(new Vector([12 / 10, 4 / 10]), $vector1->projection($vector2));
    }

    public function testOffsetExists()
    {
        $vector = self::buildVector();

        static::assertTrue(isset($vector[0]));
        static::assertFalse(isset($vector[100]));
    }

    public function testOffsetGet()
    {
        $vector = self::buildVector();

        static::assertEquals(2, $vector[1]);
    }
}

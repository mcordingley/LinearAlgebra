<?php
namespace MathPHP\Tests\LinearAlgebra;

use MCordingley\LinearAlgebra\Vector;
use MCordingley\LinearAlgebra\VectorException;

class VectorTest extends \PHPUnit_Framework_TestCase
{
    private function buildVector()
    {
        return new Vector([1, 2, 3, 4]);
    }

    public function testEmptyCount()
    {
        static::expectException(VectorException::class);

        new Vector([]);
    }

    public function testConstructor()
    {
        $this->assertInstanceOf(Vector::class, self::buildVector());
    }

    public function getSize()
    {
        $vector = self::buildVector();

        $this->assertEquals(4, $vector->getSize());
    }

    public function testGet()
    {
        $vector = self::buildVector();

        $this->assertEquals(2, $vector->get(1));
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

    public function testDotProduct()
    {
        $vector1 = new Vector([1,2,3,4]);
        $vector2 = new Vector([-1,3,5,2]);

        $this->assertEquals((1 * -1) + (2 * 3) + (3 * 5) + (4 * 2), $vector1->dotProduct($vector2));
    }

    public function testAddVector()
    {
        $vector1 = new Vector([1,2,3,4]);
        $vector2 = new Vector([-1,3,5,2]);

        $this->assertEquals(new Vector([(1 + (-1)),  (2 + 3),  (3 + 5), (4 + 2)]), $vector1->addVector($vector2));
    }

    public function testSubtractVector()
    {
        $vector1 = new Vector([1,2,3,4]);
        $vector2 = new Vector([-1,3,5,2]);

        $this->assertEquals(new Vector([(1 - (-1)),  (2 - 3),  (3 - 5), (4 - 2)]), $vector1->subtractVector($vector2));
    }

    public function testScalarMultiply()
    {
        $vector = self::buildVector();

        $this->assertEquals(new Vector([(1*3),  (2*3),  (3*3), (4*3)]), $vector->scalarMultiply(3));
    }

    public function testScalarDivide()
    {
        $vector = self::buildVector();

        $this->assertEquals(new Vector([(1/3),  (2/3),  (3/3), (4/3)]), $vector->scalarDivide(3));
    }

    public function testNormalize()
    {
        $vector = new Vector([3,4]);

        $this->assertEquals(new Vector([3/5, 4/5]), $vector->normalize());
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
}

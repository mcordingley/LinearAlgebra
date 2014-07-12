<?php

namespace mcordingley\LinearAlgebra;

class LUDecompTest extends \PHPUnit_Framework_TestCase {

    /**
     * Builds a nonsingular matrix
     * 
     * @return \mcordingley\LinearAlgebra\Matrix
     */
    private function buildMatrix() {  
        return new Matrix([
            [1, -1, 2],
            [1, -1, 1],
            [2, 3, -1]
        ]);
    }

    public function testLUDecompConstructor() {
        $matrix = $this->buildMatrix();
        
        $LUDecomp = new LUDecomposition($matrix);
        
        $this->assertEquals(1, $LUDecomp->get(0,0));
        $this->assertEquals(-1, $LUDecomp->get(0,1));
        $this->assertEquals(1, $LUDecomp->get(0,2));
        $this->assertEquals(2, $LUDecomp->get(1,0));
        $this->assertEquals(5, $LUDecomp->get(1,1));
        $this->assertEquals(-3, $LUDecomp->get(1,2));
        $this->assertEquals(1, $LUDecomp->get(2,0));
        $this->assertEquals(0, $LUDecomp->get(2,1));
        $this->assertEquals(1, $LUDecomp->get(2,2));
    }

    public function testDeterminant()
    {
        $matrix = $this->buildMatrix();

        $LUDecomp = new LUDecomposition($matrix);

        $this->assertEquals(5, $LUDecomp->determinant());
    }

    public function testSolve()
    {
        $matrix = new Matrix([
	       [1, 1, -1],
           [3, 1, 1],
           [1, -1, 4]
        ]);

        $LUDecomp = new LUDecomposition($matrix);

        $b = new Vector([1,9,8]);

        $x = $LUDecomp->solve($b);

        $this->assertEquals(3, $x->get(0));
        $this->assertEquals(-1, $x->get(1));
        $this->assertEquals(1, $x->get(2));

        // Try another one
        $matrix = new Matrix([ 
            [1, 2, 0, -4],
            [-1, 0, 6, 2],
            [3, -2, -25, 0],
            [-2, -3, 4, 4]
        ]);

        $LUDecomp = new LUDecomposition($matrix);

        $b = new Vector([-1, 7, -24, 3]);

        $x = $LUDecomp->solve($b);

        $message = 'Not within tolerance';
        $delta = 0.00000001;
        $this->assertEquals(1, $x->get(0), $message, $delta);
        $this->assertEquals(1, $x->get(1), $message, $delta);
        $this->assertEquals(1, $x->get(2), $message, $delta);
        $this->assertEquals(1, $x->get(3), $message, $delta);
    }
}
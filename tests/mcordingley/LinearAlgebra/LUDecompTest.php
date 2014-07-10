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
        $matrix = $this->buildMatrix(3,3);
        
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
    
    public function testBackSubstitute()
    {
        
    }
}
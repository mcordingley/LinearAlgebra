<?php

namespace MCordingley\Matrix;

class MatrixTest extends \PHPUnit_Framework_TestCase {
    public function testConstruction() {
        $matrix = new Matrix([
            [1, 2, 3, 4],
            [5, 6, 7, 8],
            [9, 10, 11, 12]
        ]);
        
        // This won't execute if there's an exception. If there is one,
        // pull a full stop, something's very wrong.
        $this->assertTrue(true);
    }
    
    public function testBadConstruction() {
        try {
            $matrix = new Matrix([
                [1, 2, 3, 4],
                [5, 6, 7],
                [9, 10]
            ]);
        }
        catch (MatrixException $exception) {
            return;
        }
        
        $this->fail('MatrixException not raised.');
    }
    
    public function testSize() {
        $matrix = new Matrix([
            [1, 2, 3, 4],
            [5, 6, 7, 8],
            [9, 10, 11, 12]
        ]);
        
        $this->assertEquals(3, $matrix->rows);
        $this->assertEquals(4, $matrix->columns);
    }
}
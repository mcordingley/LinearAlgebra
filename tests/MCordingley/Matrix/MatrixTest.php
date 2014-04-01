<?php

namespace MCordingley\Matrix;

class MatrixTest extends \PHPUnit_Framework_TestCase {
    private function buildMatrix() {
        return new Matrix([
            [1, 2, 3, 4],
            [5, 6, 7, 8],
            [9, 10, 11, 12]
        ]);
    }
    
    public function testConstruction() {
        $matrix = $this->buildMatrix();
        
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
        $matrix = $this->buildMatrix();
        
        $this->assertEquals(3, $matrix->rows);
        $this->assertEquals(4, $matrix->columns);
    }
    
    public function testGetSet() {
        $matrix = $this->buildMatrix();
        
        // Check constructed value
        $this->assertEquals(8, $matrix->get(1, 3));
        
        // Check chainable return value
        $this->assertInstanceOf('\MCordingley\Matrix\Matrix', $matrix->set(1, 3, 20));
        
        // Check that the expected element was set to the expected value
        $this->assertEquals(20, $matrix->get(1, 3));
    }
    
    public function testTranspose() {
        $matrix = $this->buildMatrix()->transpose();
        
        $this->assertEquals(5, $matrix->get(0, 1));
        $this->assertEquals(7, $matrix->get(2, 1));
        $this->assertEquals(10, $matrix->get(1, 2));
    }
}
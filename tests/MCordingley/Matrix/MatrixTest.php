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
    
    public function testMap() {
        $matrix = new Matrix([
            [1, 2],
            [3, 4]
        ]);
        
        $mapped = $matrix->map(function($value, $row, $column) {
            return $value + $row + $column;
        });
        
        $this->assertEquals(1, $mapped->get(0, 0));
        $this->assertEquals(3, $mapped->get(0, 1));
        $this->assertEquals(4, $mapped->get(1, 0));
        $this->assertEquals(6, $mapped->get(1, 1));
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
    
    public function testAddMatrix() {
        $matrix1 = new Matrix([
            [1, 2, 3],
            [4, 5, 6],
            [7, 8, 9]
        ]);
        
        $matrix2 = new Matrix([
            [4, 2, 6],
            [1, 7, 3],
            [7, 3, 2]
        ]);
        
        $added = $matrix1->add($matrix2);
        
        $this->assertEquals(5, $added->get(0, 0));
        $this->assertEquals(4, $added->get(0, 1));
        $this->assertEquals(9, $added->get(0, 2));
        $this->assertEquals(5, $added->get(1, 0));
        $this->assertEquals(12, $added->get(1, 1));
        $this->assertEquals(9, $added->get(1, 2));
        $this->assertEquals(14, $added->get(2, 0));
        $this->assertEquals(11, $added->get(2, 1));
        $this->assertEquals(11, $added->get(2, 2));
    }
    
    public function testAddScalar() {
        $matrix = new Matrix([
            [4, 2, 6],
            [1, 7, 3],
            [7, 3, 2]
        ]);
        
        $added = $matrix->add(4);
        
        $this->assertEquals(8, $added->get(0, 0));
        $this->assertEquals(6, $added->get(0, 1));
        $this->assertEquals(10, $added->get(0, 2));
        $this->assertEquals(5, $added->get(1, 0));
        $this->assertEquals(11, $added->get(1, 1));
        $this->assertEquals(7, $added->get(1, 2));
        $this->assertEquals(11, $added->get(2, 0));
        $this->assertEquals(7, $added->get(2, 1));
        $this->assertEquals(6, $added->get(2, 2));
    }
    
    public function testTranspose() {
        $matrix = $this->buildMatrix()->transpose();
        
        $this->assertEquals(5, $matrix->get(0, 1));
        $this->assertEquals(7, $matrix->get(2, 1));
        $this->assertEquals(10, $matrix->get(1, 2));
    }
    
    public function testDeterminant() {
        $matrix = new Matrix([
            [6, 1, 1],
            [4, -2, 5],
            [2, 8, 7]
        ]);
        
        $this->assertEquals(-306, $matrix->determinant());
    }
    
    public function testReduce() {
        $matrix = $this->buildMatrix()->reduce(1, 2);
        
        $this->assertEquals(1, $matrix->get(0, 0));
        $this->assertEquals(2, $matrix->get(0, 1));
        $this->assertEquals(4, $matrix->get(0, 2));
        $this->assertEquals(9, $matrix->get(1, 0));
        $this->assertEquals(10, $matrix->get(1, 1));
        $this->assertEquals(12, $matrix->get(1, 2));
    }
}
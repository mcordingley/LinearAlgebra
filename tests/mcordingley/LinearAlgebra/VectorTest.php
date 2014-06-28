<?php

namespace mcordingley\LinearAlgebra;

class VectorTest extends \PHPUnit_Framework_TestCase {
    private function buildVector() {
        return new Vector([1, 2, 3, 4]);
    }
    
    public function testConstruction() {
        $vector = $this->buildVector();
        
        // This won't execute if there's an exception. If there is one,
        // pull a full stop, something's very wrong.
        $this->assertTrue(true);
    }
    
    public function testSize() {
        $matrix = $this->buildVector();
        
        $this->assertEquals(1, $matrix->rows);
        $this->assertEquals(4, $matrix->columns);
    }
    
    public function testMap() {
        $matrix = $this->buildVector();
        
        $mapped = $matrix->map(function($value, $row, $column) {
            return $value + $row + $column;
        });
        
        $this->assertEquals(1, $mapped->get(0));
        $this->assertEquals(3, $mapped->get(1));
        $this->assertEquals(5, $mapped->get(2));
        $this->assertEquals(7, $mapped->get(3));
    }
    
    public function testGet() {
        $matrix = $this->buildVector();
        
        // Check constructed value
        $this->assertEquals(2, $matrix->get(1));
    }
    
    public function testAddVector() {
        $matrix1 = $this->buildVector();
        $matrix2 = $this->buildVector();
        
        $added = $matrix1->add($matrix2);
        
        $this->assertEquals(2, $added->get(0));
        $this->assertEquals(4, $added->get(1));
        $this->assertEquals(6, $added->get(2));
        $this->assertEquals(8, $added->get(3));
    }
    
    public function testAddScalar() {
        $matrix = $this->buildVector();
        
        $added = $matrix->add(4);
        
        $this->assertEquals(5, $added->get(0));
        $this->assertEquals(6, $added->get(1));
        $this->assertEquals(7, $added->get(2));
        $this->assertEquals(8, $added->get(3));
    }
    
    public function testSubtractMatrix() {
        $matrix1 = $this->buildVector();
        $matrix2 = $this->buildVector();
        
        $subtracted = $matrix1->subtract($matrix2);
        
        $this->assertEquals(0, $subtracted->get(0));
        $this->assertEquals(0, $subtracted->get(1));
        $this->assertEquals(0, $subtracted->get(2));
        $this->assertEquals(0, $subtracted->get(3));
    }
    
    public function testSubtractScalar() {
        $matrix = $this->buildVector();
        
        $subtracted = $matrix->subtract(4);
        
        $this->assertEquals(-3, $subtracted->get(0));
        $this->assertEquals(-2, $subtracted->get(1));
        $this->assertEquals(-1, $subtracted->get(2));
        $this->assertEquals(0, $subtracted->get(3));
    }
    
    public function testMultiplyMatrix() {
        $matrix1 = $this->buildVector();
        $matrix2 = $this->buildVector();
        
        $multiplied = $matrix1->multiply($matrix2);
        
        $this->assertEquals(30, $multiplied->get(0));
    }
    
    public function testMultiplyScalar() {
        $matrix = $this->buildVector();
        
        $multiplied = $matrix->multiply(2);
        
        $this->assertEquals(2, $multiplied->get(0));
        $this->assertEquals(4, $multiplied->get(1));
        $this->assertEquals(6, $multiplied->get(2));
        $this->assertEquals(8, $multiplied->get(3));
    }
    
    public function testSubmatrix() {
        $matrix = $this->buildVector()->submatrix(NULL, 1);
        
        $this->assertEquals(1, $matrix->get(0));
        $this->assertEquals(3, $matrix->get(1));
        $this->assertEquals(4, $matrix->get(2));
    }
}
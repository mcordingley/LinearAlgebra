<?php

namespace mcordingley\LinearAlgebra;

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
        $this->assertInstanceOf('\mcordingley\LinearAlgebra\Matrix', $matrix->set(1, 3, 20));
        
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
    
    public function testSubtractMatrix() {
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
        
        $subtracted = $matrix1->subtract($matrix2);
        
        $this->assertEquals(-3, $subtracted->get(0, 0));
        $this->assertEquals(0, $subtracted->get(0, 1));
        $this->assertEquals(-3, $subtracted->get(0, 2));
        $this->assertEquals(3, $subtracted->get(1, 0));
        $this->assertEquals(-2, $subtracted->get(1, 1));
        $this->assertEquals(3, $subtracted->get(1, 2));
        $this->assertEquals(0, $subtracted->get(2, 0));
        $this->assertEquals(5, $subtracted->get(2, 1));
        $this->assertEquals(7, $subtracted->get(2, 2));
    }
    
    public function testSubtractScalar() {
        $matrix = new Matrix([
            [4, 2, 6],
            [1, 7, 3],
            [7, 3, 2]
        ]);
        
        $subtracted = $matrix->subtract(4);
        
        $this->assertEquals(0, $subtracted->get(0, 0));
        $this->assertEquals(-2, $subtracted->get(0, 1));
        $this->assertEquals(2, $subtracted->get(0, 2));
        $this->assertEquals(-3, $subtracted->get(1, 0));
        $this->assertEquals(3, $subtracted->get(1, 1));
        $this->assertEquals(-1, $subtracted->get(1, 2));
        $this->assertEquals(3, $subtracted->get(2, 0));
        $this->assertEquals(-1, $subtracted->get(2, 1));
        $this->assertEquals(-2, $subtracted->get(2, 2));
    }
    
    public function testMultiplyMatrix() {
        $matrix1 = new Matrix([
            [1, 2, 3],
            [4, 5, 6]
        ]);
        
        $matrix2 = new Matrix([
            [7, 8],
            [9, 10],
            [11, 12]
        ]);
        
        $multiplied = $matrix1->multiply($matrix2);
        
        $this->assertEquals(58, $multiplied->get(0, 0));
        $this->assertEquals(64, $multiplied->get(0, 1));
        $this->assertEquals(139, $multiplied->get(1, 0));
        $this->assertEquals(154, $multiplied->get(1, 1));
    }
    
    public function testMultiplyScalar() {
        $matrix = new Matrix([
            [4, 2, 6],
            [1, 7, 3],
            [7, 3, 2]
        ]);
        
        $multiplied = $matrix->multiply(2);
        
        $this->assertEquals(8, $multiplied->get(0, 0));
        $this->assertEquals(4, $multiplied->get(0, 1));
        $this->assertEquals(12, $multiplied->get(0, 2));
        $this->assertEquals(2, $multiplied->get(1, 0));
        $this->assertEquals(14, $multiplied->get(1, 1));
        $this->assertEquals(6, $multiplied->get(1, 2));
        $this->assertEquals(14, $multiplied->get(2, 0));
        $this->assertEquals(6, $multiplied->get(2, 1));
        $this->assertEquals(4, $multiplied->get(2, 2));
    }
    
    public function testTrace() {
        $matrix = new Matrix([
            [1, 2, 3],
            [0, 1, 4],
            [5, 6, 0]
        ]);
        
        $this->assertEquals(2, $matrix->trace());
    }
    
    public function testTranspose() {
        $matrix = $this->buildMatrix()->transpose();
        
        $this->assertEquals(5, $matrix->get(0, 1));
        $this->assertEquals(7, $matrix->get(2, 1));
        $this->assertEquals(10, $matrix->get(1, 2));
    }
    
    public function testInverse() {
        $matrix = new Matrix([
            [1, 2, 3],
            [0, 1, 4],
            [5, 6, 0]
        ]);
        
        $inverse = $matrix->inverse();
        
        $this->assertEquals(-24, $inverse->get(0, 0));
        $this->assertEquals(18, $inverse->get(0, 1));
        $this->assertEquals(5, $inverse->get(0, 2));
        $this->assertEquals(20, $inverse->get(1, 0));
        $this->assertEquals(-15, $inverse->get(1, 1));
        $this->assertEquals(-4, $inverse->get(1, 2));
        $this->assertEquals(-5, $inverse->get(2, 0));
        $this->assertEquals(4, $inverse->get(2, 1));
        $this->assertEquals(1, $inverse->get(2, 2));
    }
    
    public function testCholeskyInverse() {
        $matrix = new Matrix([
            [25, 15, -5],
            [15, 18, 0],
            [-5, 0, 11]
        ]);
        
        $inverse = $matrix->inverse();
        
        $this->assertEquals(22 / 225, $inverse->get(0, 0));
        $this->assertEquals(-11 / 135, $inverse->get(0, 1));
        $this->assertEquals(2 / 45, $inverse->get(0, 2));
        $this->assertEquals(-11 / 135, $inverse->get(1, 0));
        $this->assertEquals(10 / 81, $inverse->get(1, 1));
        $this->assertEquals(-1 / 27, $inverse->get(1, 2));
        $this->assertEquals(2 / 45, $inverse->get(2, 0));
        $this->assertEquals(-1 / 27, $inverse->get(2, 1));
        $this->assertEquals(1 / 9, $inverse->get(2, 2));
    }
    
    public function testAdjoint() {
        $matrix = new Matrix([
            [1, -1, 2],
            [4, 0, 6],
            [0, 1, -1]
        ]);
        
        $adjoint = $matrix->adjoint();
        
        $this->assertEquals(-6, $adjoint->get(0, 0));
        $this->assertEquals(1, $adjoint->get(0, 1));
        $this->assertEquals(-6, $adjoint->get(0, 2));
        $this->assertEquals(4, $adjoint->get(1, 0));
        $this->assertEquals(-1, $adjoint->get(1, 1));
        $this->assertEquals(2, $adjoint->get(1, 2));
        $this->assertEquals(4, $adjoint->get(2, 0));
        $this->assertEquals(-1, $adjoint->get(2, 1));
        $this->assertEquals(4, $adjoint->get(2, 2));
    }
    
    public function testDeterminant() {
        $matrix = new Matrix([
            [6, 1, 1],
            [4, -2, 5],
            [2, 8, 7]
        ]);
        
        $this->assertEquals(-306, $matrix->determinant());
    }
    
    public function testSubmatrix() {
        $matrix = $this->buildMatrix()->submatrix(1, 2);
        
        $this->assertEquals(1, $matrix->get(0, 0));
        $this->assertEquals(2, $matrix->get(0, 1));
        $this->assertEquals(4, $matrix->get(0, 2));
        $this->assertEquals(9, $matrix->get(1, 0));
        $this->assertEquals(10, $matrix->get(1, 1));
        $this->assertEquals(12, $matrix->get(1, 2));
    }
}
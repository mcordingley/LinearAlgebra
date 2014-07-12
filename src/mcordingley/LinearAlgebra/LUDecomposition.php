<?php

namespace mcordingley\LinearAlgebra;

/**
 * Creates an LU Decomposition using Crout's Method and provides methods for using it.
 * 
 * LU Decomposition references:
 * @reference: Numerical Recipes, 3rd edition (section 2.3) http://nrbook.com
 * @reference: http://www.cs.rpi.edu/~flaherje/pdf/lin6.pdf
 * 
 * Crout's Method reference:
 * @reference: http://www.physics.utah.edu/~detar/phys6720/handouts/crout.txt
 * 
 * Code reference:
 * @reference: http://rosettacode.org/wiki/LU_decomposition
 */


class LUDecomposition extends Matrix {

    protected $parity = 1;  // 1 if the number of row interchanges is even, -1 if it is odd. (used for determinants)
    protected $permutations; // Stores a vector representation of the row permutations performed on this matrix.

    /**
     * Constructor
     * 
     * Copies the matrix, then performs the LU Decomposition.
     * 
     * @param \mcordingley\LinearAlgebra\Matrix  The matrix to decompose.
     */
    public function __construct(\mcordingley\LinearAlgebra\Matrix $matrix) {        
        // Copy the matrix
        $matrix->map(function($element, $i, $j, $matrix){ 
            $this->internal[$i][$j] = $element;
        });
        $this->rowCount = $matrix->rows;
        $this->columnCount = $matrix->columns;

        if( ! $this->isSquare()) throw new MatrixException("Matrix is not square.");

        $this->LUDecomp();
    }

    /**
     *  Performs the LU Decomposition.
     *  
     *  This uses Crout's method with partial (row) pivoting and implicit scaling
     *  to perform the decomposition in-place on a copy of the original matrix. 
     */
    private function LUDecomp() {
        $scaling = array();
        $this->parity = 1;   // start parity at +1 (parity is "even" for zero row interchanges)
        $n = $this->rowCount;
        $p =& $this->permutations;

        // We want to find the largest element in each row for scaling.
        for ($i = 0; $i < $n; ++$i) {
            $biggest = 0;
            for ($j = 0; $j < $n; ++$j) {
                $temp = abs($this->internal[$i][$j]);
                $biggest = max($temp, $biggest);
            }
            if ($biggest == 0) throw new MatrixException("Matrix is singular.");
            $scaling[$i] = 1 / $biggest;
            $p[$i] = $i; // Initialize permutations vector
        }

        // Now we find the LU decomposition. This is the outer loop over diagonal elements.
        for($k = 0; $k < $n; ++$k) {
            
            // Search for the best (biggest) pivot element
            $biggest = 0;
            $max_row_index = $k;
            for($i = $k; $i < $n; ++$i) {
                $temp = $scaling[$i] * abs($this->internal[$i][$k]);
                if($temp > $biggest) {
                    $biggest = $temp;
                    $max_row_index = $i;
                }      
            }
            
            // Perform the row pivot and store in the permuations vector
            if($k != $max_row_index) {
                $this->rowPivot($k, $max_row_index);
            	$temp = $p[$k];
            	$p[$k] = $p[$max_row_index];
            	$p[$max_row_index] = $temp;
            	$this->parity = -$this->parity;   // flip parity
            }

            if ($this->internal[$k][$k] == 0) throw new MatrixException("Matrix is singular.");           

            // Crout's algorithm
            for ($i = $k + 1; $i < $n; ++$i) {
                
                // Divide by the pivot element
                $this->internal[$i][$k] = $this->internal[$i][$k] / $this->internal[$k][$k];
                
                // Subtract from each element in the sub-matrix
                for ($j = $k + 1; $j < $n; ++$j) {
                    $this->internal[$i][$j] = $this->internal[$i][$j] - $this->internal[$i][$k] * $this->internal[$k][$j];
                }
            }
        }
    }
    
    /**
     * Returns the determinant of the LU decomposition
     * 
     * @see \mcordingley\LinearAlgebra\Matrix::determinant()
     * @return double
     */
    public function determinant() {
        $n = $this->rowCount;
        $determinant = $this->parity;   // Start with +1 for an even # of row swaps, -1 for an odd #
        
        // The determinant is simply the product of the diagonal elements, with sign given
        // by the number of row permutations (-1 for odd, +1 for even)
        for($i = 0; $i < $n; ++$i) {
            $determinant *= $this->get($i, $i);
        }
        return $determinant;
    }
    
    /**
     * Swaps $thisRow for $thatRow
     * 
     * @param int $thisRow
     * @param int $thatRow
     */
    private function rowPivot($thisRow, $thatRow) {
        	$temp = $this->internal[$thisRow];
        	$this->internal[$thisRow]= $this->internal[$thatRow];
        	$this->internal[$thatRow] = $temp;
    }
    
    /**
     * Solves a linear set of equations in the form A * x = b for x, where A
     * is the decomposed matrix of coefficients (now P*L*U), $x is the vector
     * of unknowns, and $b is the vector of knowns.
     *  
     * @param \mcordingley\LinearAlgebra\Vector $b - vector of knowns
     * @return \mcordingley\LinearAlgebra\Vector $x - the solution vector
     */
    public function solve(\mcordingley\LinearAlgebra\Vector $b) {        
        $n = $this->rowCount;
        if( ! ($b->rows !== $n || $b->columns !== $n)) {
            throw new MatrixException ('The knowns vector must be the same size as the coefficient matrix.');
        }

        $y = array();   // L*y = b
        $x = array();   // U*x = y

        // Solve L * y = b for y (forward substitution)
        for($i = 0; $i < $n; ++$i) {
            $y[$i] = $b->get($this->permutations[$i]);
            for($j = 0; $j < $i; ++$j) {
                $y[$i] = $y[$i] - $this->get($i, $j) * $y[$j];
            }
        }

        // Solve U * x = y for x (backward substitution)
        for($i = $n - 1; $i >= 0; --$i) {
            $x[$i] = $y[$i];
            for($j = $i + 1; $j < $n; ++$j) {                
                $x[$i] = $x[$i] - $this->get($i, $j) * $x[$j];       
            }
            $x[$i] = $x[$i] / $this->get($i, $i);   // Keep division out of the inner loop
        }

        return new Vector($x);
    }
}
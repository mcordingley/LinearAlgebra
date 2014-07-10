<?php

namespace mcordingley\LinearAlgebra;

/**
 * Creates an LU Decomposition using Crout's Method and provides methods for using it.
 * 
 * LU Decomposition references:
 * @reference: Numerical Recipes, 3rd edition (section 2.3) http://nrbook.com
 * @reference: http://www.cs.rpi.edu/~flaherje/pdf/lin6.pdf
 * 
 * Crout's Algorithm reference:
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
    public function __construct(Matrix $matrix) {        
        $matrix->map(function($element, $i, $j, $matrix){ 
            $this->internal[$i][$j] = $element;
        });
        $this->rowCount = $matrix->rows;
        $this->columnCount = $matrix->columns;
        
        if( ! $this->isSquare()) throw new \Exception("Matrix is not square.");
        
        $this->LUDecomp();
    }
    
    /**
     *  Performs the LU Decomposition.
     *  
     *  This method uses Crout's algorithm on an in-place matrix with partial row 
     *  pivoting and implicit scaling. The pivots are not actually performed on the matrix.
     *  Instead, they are stored in a permutation vector and applied as needed.
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
            if ($biggest == 0) throw new \Exception("Matrix is singular.");
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
            
            // Store the pivot in the permuations vector
            if($k != $max_row_index)
            {
            	$temp = $p[$k];
            	$p[$k] = $p[$max_row_index];
            	$p[$max_row_index] = $temp;
            	$this->parity = -$this->parity;   // flip parity
            }

            if ($this->internal[$p[$k]][$k] == 0) throw new \Exception("Matrix is singular.");           
            
            // Crout's algorithm
            for ($i = $k + 1; $i < $n; ++$i) {
                
                // Divide by the pivot element
                $this->internal[$p[$i]][$k] = $this->internal[$p[$i]][$k] / $this->internal[$p[$k]][$k];
                
                // Subtract from each element in the sub-matrix
                for ($j = $k + 1; $j < $n; ++$j) {
                    $this->internal[$p[$i]][$j] = $this->internal[$p[$i]][$j] - $this->internal[$p[$i]][$k] * $this->internal[$p[$k]][$j];
                }
            }
        }
    }
    
	/**
	 * Returns the specified value after applying the permutation vector.
	 * @see \mcordingley\LinearAlgebra\Matrix::get()
	 */
	public function get($row, $column) {
        return $this->internal[$this->permutations[$row]][$column];
	}
    
	/**
	 * Returns the determinant of the LU decomposition
	 * 
	 * @see \mcordingley\LinearAlgebra\Matrix::determinant()
	 * @return double
	 */
    public function determinant() {
        $n = $this->rowCount;
        
        // The determinant is simply the product of the diagonal elements, with sign given
        // by the number of row permutations (-1 for odd, +1 for even)
        for($i = 0; $i < $n; ++$i) {
            $determinant *= $this->internal[$this->permutations[$i]][$i];
        }
        return $this->parity * $determinant;
    }
    
    /**
     * Swaps $thisRow for $thatRow
     * 
     * @param int $thisRow
     * @param int $thatRow
     */
    private function rowPivot($thisRow, $thatRow)
    {
        for ($col = 0; $col < $this->rowCount; ++$col) {
        	$temp = $this->internal[$thisRow][$col];
        	$this->internal[$thisRow][$col] = $this->internal[$thatRow][$col];
        	$this->internal[$thatRow][$col] = $temp;
        }
    }
}
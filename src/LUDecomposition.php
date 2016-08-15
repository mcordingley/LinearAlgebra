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
 *
 * @internal
 */
final class LUDecomposition extends Matrix
{
    private $parity = 1;  // 1 if the number of row interchanges is even, -1 if it is odd. (used for determinants)
    private $permutations = []; // Stores a vector representation of the row permutations performed on this matrix.

    /**
     * @param array $literal The matrix to decompose.
     * @throws MatrixException
     */
    public function __construct(array $literal)
    {
        parent::__construct($literal);

        if (!$this->isSquare()) {
            throw new MatrixException("Matrix is not square.");
        }

        $this->decompose();
    }

    /**
     *  Performs the LU Decomposition.
     *
     *  This uses Crout's method with partial (row) pivoting and implicit scaling
     *  to perform the decomposition in-place on a copy of the original matrix.
     */
    private function decompose()
    {
        $scaling = [];
        $this->parity = 1;   // start parity at +1 (parity is "even" for zero row interchanges)
        $rowCount = $this->getRowCount();

        // We want to find the largest element in each row for scaling.
        for ($i = 0; $i < $rowCount; ++$i) {
            $biggest = 0;

            for ($j = 0; $j < $rowCount; ++$j) {
                $temp = abs($this->internal[$i][$j]);
                $biggest = max($temp, $biggest);
            }

            if ($biggest == 0) {
                throw new MatrixException("Matrix is singular.");
            }

            $scaling[$i] = 1 / $biggest;
            $this->permutations[$i] = $i; // Initialize permutations vector
        }

        // Now we find the LU decomposition. This is the outer loop over diagonal elements.
        for ($k = 0; $k < $rowCount; ++$k) {
            // Search for the best (biggest) pivot element
            $biggest = 0;
            $maxRowIndex = $k;
            for ($i = $k; $i < $rowCount; ++$i) {
                $temp = $scaling[$i] * abs($this->internal[$i][$k]);
                if ($temp > $biggest) {
                    $biggest = $temp;
                    $maxRowIndex = $i;
                }
            }

            // Perform the row pivot and store in the permutations vector
            if ($k != $maxRowIndex) {
                $this->rowPivot($k, $maxRowIndex);
                $temp = $this->permutations[$k];
                $this->permutations[$k] = $this->permutations[$maxRowIndex];
                $this->permutations[$maxRowIndex] = $temp;
                $this->parity = -$this->parity;   // flip parity
            }

            if ($this->internal[$k][$k] == 0) {
                throw new MatrixException("Matrix is singular.");
            }

            // Crout's algorithm
            for ($i = $k + 1; $i < $rowCount; ++$i) {

                // Divide by the pivot element
                $this->internal[$i][$k] = $this->internal[$i][$k] / $this->internal[$k][$k];

                // Subtract from each element in the sub-matrix
                for ($j = $k + 1; $j < $rowCount; ++$j) {
                    $this->internal[$i][$j] = $this->internal[$i][$j] - $this->internal[$i][$k] * $this->internal[$k][$j];
                }
            }
        }
    }

    /**
     * Returns the determinant of the LU decomposition
     *
     * @return float
     */
    public function determinant()
    {
        $rowCount = $this->getRowCount();
        $determinant = $this->parity;   // Start with +1 for an even # of row swaps, -1 for an odd #

        // The determinant is simply the product of the diagonal elements, with sign given
        // by the number of row permutations (-1 for odd, +1 for even)
        for ($i = 0; $i < $rowCount; $i++) {
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
    private function rowPivot($thisRow, $thatRow)
    {
        $temp = $this->internal[$thisRow];
        $this->internal[$thisRow] = $this->internal[$thatRow];
        $this->internal[$thatRow] = $temp;
    }

    /**
     * Solves a linear set of equations in the form A * unknown = known for unknown, where A
     * is the decomposed matrix of coefficients (now P*L*U)
     *
     * @param array $known
     * @return array
     * @throws MatrixException
     */
    public function solve(array $known)
    {
        $rowCount = $this->getRowCount();

        if (count($known) !== $rowCount) {
            throw new MatrixException('The known vector must be the same size as the coefficient matrix.');
        }

        $y = []; // L * y = b
        $unknown = []; // U * unknown = y

        $skip = true;

        // Solve L * y = b for y (forward substitution)
        for ($i = 0; $i < $rowCount; $i++) {
            $thisB = $known[$this->permutations[$i]]; // Unscramble the permutations

            if ($skip && $thisB == 0) {
                // Leading zeroes in b give zeroes in y.
                $y[$i] = 0;
            } else {
                // We found a non-zero element, so don't skip any more.
                $skip = false;
                $y[$i] = $thisB;

                for ($j = 0; $j < $i; $j++) {
                    $y[$i] = $y[$i] - $this->get($i, $j) * $y[$j];
                }
            }
        }

        // Solve U * unknown = y for unknown (backward substitution)
        for ($i = $rowCount - 1; $i >= 0; $i--) {
            $unknown[$i] = $y[$i];

            for ($j = $i + 1; $j < $rowCount; $j++) {
                $unknown[$i] = $unknown[$i] - $this->get($i, $j) * $unknown[$j];
            }

            $unknown[$i] = $unknown[$i] / $this->get($i, $i);   // Keep division out of the inner loop
        }

        return $unknown;
    }

    /**
     * Finds the inverse matrix using the LU decomposition.
     *
     * Works by solving LUX = B for X where X is the inverse matrix of same rank and order as LU,
     * and B is an identity matrix, also of the same rank and order.
     *
     * @return Matrix
     */
    public function inverse()
    {
        $inverse = [];
        $rowCount = $this->getRowCount();
        $currentRow = array_fill(0, $rowCount, 0);

        // For each j from 0 to n-1
        for ($j = 0; $j < $rowCount; $j++) {
            // this is the jth column of the identity matrix
            $currentRow[$j] = 1;

            $solution = $this->solve($currentRow);

            for ($i = 0; $i < $rowCount; ++$i) {
                $inverse[$i][$j] = $solution[$i];
            }

            $currentRow[$j] = 0; // Get the vector ready for the next column.
        }

        return new Matrix($inverse);
    }
}

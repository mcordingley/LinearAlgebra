<?php

namespace MCordingley\Matrix;

class Matrix {
    protected $rowCount;
    protected $columnCount;

    // Internal array representation of the matrix
    protected $internal;
    
    /**
     * Constructor
     * 
     * Creates a new matrix. e.g. 
     *      $transform = new Matrix([
     *          [0, 1, 2],
     *          [3, 4, 5],
     *          [6, 7, 8]
     *      ]);
     * 
     * @param array $literal Array representation of the matrix.
     */
    public function __construct(array $literal) {
        if (!$this->isLiteralValid($literal)) {
            throw new MatrixException('Invalid array provided: ' . print_r($literal, true));
        }
        
        $this->internal = $literal;
        
        $this->rowCount = count($literal);
        $this->columnCount = count($literal[0]);
    }
    
    /**
     * map
     * 
     * Iterates over the current matrix with a callback function to return a new
     * matrix with the mapped values. $callback takes four arguments:
     * - The current matrix element
     * - The current row
     * - The current column
     * - The matrix being iterated over
     * 
     * @param callable $callback A function that returns the computed new values.
     * @return \MCordingley\Matrix\Matrix A new matrix with the mapped values.
     */
    public function map(callable $callback) {
        $class = get_called_class();
        
        $literal = array();

        for ($i = 0; $i < $this->rows; $i++) {
            $row = array();

            for ($j = 0; $j < $this->columns; $j++) {
                $row[] = $callback($this->get($i, $j), $i, $j, $this);
            }

            $literal[] = $row;
        }
        
        return new $class($literal);
    }
    
    /**
     * isLiteralValid
     * 
     * Tests a literal value to see if it's valid input for a new instance of
     * this class.
     * 
     * @param array $literal Array literal representation of this class.
     * @return boolean True if a valid representation. False otherwise.
     */
    protected function isLiteralValid(array $literal) {
        // Matrix must have at least one row
        if (!count($literal)) {
            return false;
        }
        
        // Matrix must have at least one column
        if (!count($literal[0])) {
            return false;
        }
        
        // Matrix must have the same number of columns in each row
        $lastRow = false;
        foreach ($literal as $row) {
            $thisRow = count($row);
            
            if ($lastRow !== false && $lastRow != $thisRow) {
                return false;
            }
            
            $lastRow = $thisRow;
        }
        
        return true;
    }
    
    /**
     * get
     * 
     * @param int $row Which zero-based row index to access.
     * @param int $column Which zero-based column index to access.
     * @return numeric The value at $row, $column position in the matrix.
     */
    public function get($row, $column) {
        return $this->internal[$row][$column];
    }
    
    /**
     * set
     * 
     * Alters the current matrix to have a new value and then returns $this for
     * method chaining.
     * 
     * @param int $row Which zero-based row index to set.
     * @param int $column Which zero-based column index to set.
     * @param numeric $value The new value for the position at $row, $column.
     * @return \MCordingley\Matrix\Matrix
     */
    public function set($row, $column, $value) {
        $this->internal[$row][$column] = $value;
        
        return $this;
    }
    
    /**
     * add
     * 
     * Adds either another matrix or a scalar to the current matrix, returning
     * a new matrix instance.
     * 
     * @param mixed $value Matrix or scalar to add to this matrix
     * @return \MCordingley\Matrix\Matrix New matrix with the added value
     * @throws MatrixException
     */
    public function add($value) {
        if ($value instanceof Matrix) {
            if ($this->rows != $value->rows || $this->columns != $value->columns) {
                throw new MatrixException('Cannot add two matrices of different size.');
            }
            
            return $this->map(function($element, $i, $j) use ($value) {
                return $element + $value->get($i, $j);
            });
        }
        else {
            return $this->map(function($element) use ($value) {
                return $element + $value;
            });
        }
    }
    
    /**
     * subtract
     * 
     * Subtracts either another matrix or a scalar from the current matrix,
     * returning a new matrix instance.
     * 
     * @param mixed $value Matrix or scalar to subtract from this matrix
     * @return \MCordingley\Matrix\Matrix New matrix with the subtracted value
     * @throws MatrixException
     */
    public function subtract($value) {
        if ($value instanceof Matrix) {
            if ($this->rows != $value->rows || $this->columns != $value->columns) {
                throw new MatrixException('Cannot subtract two matrices of different size.');
            }
            
            return $this->map(function($element, $i, $j) use ($value) {
                return $element - $value->get($i, $j);
            });
        }
        else {
            return $this->map(function($element) use ($value) {
                return $element - $value;
            });
        }
    }
    
    /**
     * transpose
     * 
     * Creates and returns a new matrix that is a transposition of this matrix.
     * 
     * @return \MCordingley\Matrix\Matrix Transposed matrix.
     */
    public function transpose() {
        $class = get_called_class();
        
        $literal = array();
        
        for ($i = 0; $i < $this->columns; $i++) {
            $literal[] = array();
            
            for ($j = 0; $j < $this->rows; $j++) {
                $literal[$i][] = $this->get($j, $i);
            }
        }
        
        return new $class($literal);
    }
    
    /**
      * Determinant function
      *
      * Returns the determinant of the matrix
      *
      * @return float The matrix's determinant
      */
    public function determinant() {
        /* TODO: This function is a good candidate for optimization by the
                 mathematically-inclined. Suggest doing the operation without
                 generating new matrices during the calculation. */
        
        if (!$this->isSquare($this)) {
            throw new MatrixException('Determinants can only be called on square matrices: ' . print_r($this->literal, true));
        }

        // Base case for a 1 by 1 matrix
        if ($this->rows == 1) {
            return $this->get(0, 0);
        }

        $sum = 0;
        
        // Statically choose the first row for cofactor expansion, because it
        // doesn't matter which row we choose for it.
        for ($j = 0; $j < $this->columns; $j++) {
            $sum += pow(-1, $j) * $this->get(0, $j) * $this->reduce(0, $j)->determinant();
        }
        
        return $sum;
    }
    
    // Potentially a good thing to take public. We'll see if that's a good idea.
    protected function isSquare() {
        return $this->rows == $this->columns;
    }
    
    /**
     * reduce
     *
     * Returns a new matrix with the selected row and column removed, useful for
     * calculating determinants or other recursive operations on matrices.
     *
     * @param int $row Row to remove, null to remove no row.
     * @param int $column Column to remove, null to remove no column.
     * @return \MCordingley\Matrix\Matrix Reduced matrix.
     */
    public function reduce($row = null, $column = null) {
        $class = get_called_class();

        $literal = array();

        for ($i = 0; $i < $this->rows; $i++) {
            if ($i === $row) {
                continue;
            }

            $rowLiteral = array();

            for ($j = 0; $j < $this->columns; $j++) {
                if ($j === $column) {
                    continue;
                }

                $rowLiteral[] = $this->get($i, $j);
            }

            $literal[] = $rowLiteral;
        }

        return new $class($literal);
    }
    
    public function __get($property) {
        switch ($property) {
            case 'columns':
                return $this->columnCount;
            case 'rows':
                return $this->rowCount;
            default:
                return null;
        }
    }
}
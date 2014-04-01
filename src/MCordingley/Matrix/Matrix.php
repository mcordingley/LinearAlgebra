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
     * isLiteralValid
     * 
     * Tests a literal value to see if it's valid input for a new instance of
     * this class.
     * 
     * @param array $literal Array literal representation of this class.
     * @return boolean True if a valid representation. False otherwise.
     */
    protected function isLiteralValid(array $literal) {
        // Check size
        
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
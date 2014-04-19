<?php

namespace mcordingley\LinearAlgebra;

class Vector extends Matrix {
    public function __construct(array $literal) {
        // Just wrap the incoming array, so we again have an array of arrays.
        parent::__construct(array($literal));
    }
    
    protected function isHorizontal() {
        return $this->columnCount > 1;
    }
    
    /**
     * get
     * 
     * Vector-specific implementation of `get`. Call with $column = NULL to look
     * up values along the vector's major axis. e.g. `$vector->get(1)` will
     * return the second value in the vector whether it is horizontal or not.
     * 
     * @param int $row The row to return. 
     * @param int|null $column The column to return.
     * @return numeric The vector value at the specified row and column
     */
    public function get($row, $column = NULL) {
        if (is_null($column)) {
            if ($this->isHorizontal()) {
                $column = $row;
                $row = 0;
            }
            else {
                $column = 0;
            }
        }
        
        return parent::get($row, $column);
    }
    
    /**
     * set
     * 
     * Vector-specific implementation of `set`. Call with $value = NULL to set
     * values along the vector's major axis. e.g. `$vector->set(1, 2)` will set
     * the second value in the vector to be `2` whether the vector is horizontal
     * or not.
     * 
     * @param int $row
     * @param numeric $column
     * @param numeric|null $value
     * @return \mcordingley\Matrix\Vector
     */
    public function set($row, $column, $value = NULL) {
        // If called with 2 arguments, second one is the value and first is the row/column
        if (is_null($value)) {
            $value = $column;
            
            // For a horizontal vector, argument 1 specifies the column.
            if ($this->isHorizontal()) {
                $column = $row;
                $row = 0;
            }
            // For vertical vectors, argument 1 specifies the row.
            else {
                $column = 0;
            }
        }
        
        return parent::set($row, $column, $value);
    }
    
    /**
     * multiply
     * 
     * Vector-specific implementation of `multiply`. If multiplying two vectors,
     * will ensure that they are of correct orientation to be multiplied
     * together.
     * 
     * @param mixed $value Another matrix or a numeric scalar value.
     * @return \mcordingley\Matrix\Vector
     */
    public function multiply($value) {
        // Multiplying vectors yields a scalar, so make sure that the two vectors are in the appropriate orientations
        if ($value instanceof Vector && $this->isHorizontal() == $value->isHorizontal()) {
            $value = $value->transpose();
        }
        
        return parent::multiply($value);
    }
}
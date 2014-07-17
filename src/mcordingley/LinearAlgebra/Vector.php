<?php

namespace mcordingley\LinearAlgebra;

class Vector extends Matrix {
    public function __construct(array $literal) {
        // Just wrap the incoming array, so we again have an array of arrays,
        // if it isn't already so.
        if (!is_array($literal[0])) {
            $literal = array($literal);
        }
        
        parent::__construct($literal);
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
    
    //
    // ArrayAccess interface
    //
    
    public function offsetExists($offset) {
        if ($this->isHorizontal()) {
            $column = $offset;
            $row = 0;
        }
        else {
            $column = 0;
            $row = $offset;
        }
            
        return isset($this->internal[$row][$column]);
    }
    
    public function offsetGet($offset) {
        if ($this->isHorizontal()) {
            $column = $offset;
            $row = 0;
        }
        else {
            $column = 0;
            $row = $offset;
        }
        
        return $this->internal[$row][$column];
    }
}
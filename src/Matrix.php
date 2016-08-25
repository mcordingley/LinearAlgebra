<?php

namespace mcordingley\LinearAlgebra;

use ArrayAccess;

class Matrix implements ArrayAccess
{
    /**
     * @var int
     */
    protected $columnCount;

    /**
     * @var int
     */
    protected $rowCount;

    /**
     * @var array
     */
    protected $internal;

    /**
     * @var LUDecomposition
     */
    protected $decomposition;

    /**
     * __construct
     *
     * Example:
     *      $transform = new Matrix([
     *          [0, 1, 2],
     *          [3, 4, 5],
     *          [6, 7, 8]
     *      ]);
     *
     * @param array $literal Array representation of the matrix.
     * @throws MatrixException
     */
    public function __construct(array $literal)
    {
        if (!$this->isLiteralValid($literal)) {
            throw new MatrixException('Invalid array provided: ' . print_r($literal, true));
        }

        $this->internal = $literal;

        $this->rowCount = count($literal);
        $this->columnCount = count($literal[0]);
    }

    /**
     * @param array $literal
     * @return boolean
     */
    protected function isLiteralValid(array $literal)
    {
        if (!$literal) {
            return false;
        }

        $firstRowSize = count($literal[0]);

        if (!$firstRowSize) {
            return false;
        }

        foreach ($literal as $row) {
            if (count($row) !== $firstRowSize) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param int $size How many rows and columns the identity matrix should have
     * @return Matrix
     */
    public static function identity($size)
    {
        $literal = [];

        for ($i = 0; $i < $size; ++$i) {
            $literal[] = [];

            for ($j = 0; $j < $size; ++$j) {
                $literal[$i][] = ($i === $j) ? 1 : 0;
            }
        }

        return new static($literal);
    }

    /**
     * Adds either another matrix or a scalar to the current matrix, returning
     * a new matrix instance.
     *
     * @param Matrix|int|float $value Matrix or scalar to add to this matrix
     * @return Matrix
     * @throws MatrixException
     * @deprecated Use `addMatrix` or `addScalar` instead.
     */
    public function add($value)
    {
        if ($value instanceof Matrix) {
            return $this->addMatrix($value);
        }

        return $this->addScalar($value);
    }

    /**
     * @param Matrix $value
     * @return Matrix
     * @throws MatrixException
     */
    public function addMatrix(Matrix $value)
    {
        if ($this->getRowCount() !== $value->getRowCount() || $this->getColumnCount() !== $value->getColumnCount()) {
            throw new MatrixException('Cannot add two matrices of different size.');
        }

        return $this->map(function ($element, $i, $j) use ($value) {
            return $element + $value->get($i, $j);
        });
    }

    /**
     * Iterates over the current matrix with a callback function to return a new
     * matrix with the mapped values. $callback takes four arguments:
     * - The current matrix element
     * - The current row
     * - The current column
     * - The matrix being iterated over
     *
     * @param callable $callback
     * @return Matrix
     */
    public function map(callable $callback)
    {
        $literal = [];

        for ($i = 0; $i < $this->getRowCount(); $i++) {
            $row = [];

            for ($j = 0; $j < $this->getColumnCount(); $j++) {
                $row[] = $callback($this->get($i, $j), $i, $j, $this);
            }

            $literal[] = $row;
        }

        return new static($literal);
    }

    /**
     * @param int $row
     * @param int $column
     * @return float
     */
    public function get($row, $column)
    {
        return $this->internal[$row][$column];
    }

    /**
     * @param float $value
     * @return Matrix
     */
    public function addScalar($value)
    {
        return $this->map(function ($element) use ($value) {
            return $element + $value;
        });
    }

    /**
     * @return Matrix
     * @throws MatrixException
     */
    public function adjoint()
    {
        if (!$this->isSquare()) {
            throw new MatrixException('Adjoints can only be called on square matrices: ' . print_r($this->internal, true));
        }

        return $this->inverse()->multiplyScalar($this->determinant());
    }

    /**
     * @return boolean
     */
    public function isSquare()
    {
        return $this->getRowCount() === $this->getColumnCount();
    }

    /**
     * Multiplies either another matrix or a scalar with the current matrix,
     * returning a new matrix instance.
     *
     * @param Matrix|int|float $value Matrix or scalar to multiply with this matrix
     * @return Matrix
     * @throws MatrixException
     * @deprecated Use `multiplyMatrix` or `multiplyScalar` instead.
     */
    public function multiply($value)
    {
        if ($value instanceof Matrix) {
            return $this->multiplyMatrix($value);
        }

        return $this->multiplyScalar($value);
    }

    /**
     * @param Matrix $value
     * @return Matrix
     * @throws MatrixException
     */
    public function multiplyMatrix(Matrix $value)
    {
        if ($this->getColumnCount() !== $value->getRowCount()) {
            throw new MatrixException('Cannot multiply matrices of these sizes.');
        }

        $literal = [];

        for ($i = 0; $i < $this->getRowCount(); $i++) {
            $row = [];

            for ($j = 0; $j < $value->columnCount; $j++) {
                $sum = 0;

                for ($k = 0; $k < $this->getColumnCount(); $k++) {
                    $sum += $this->get($i, $k) * $value->get($k, $j);
                }

                $row[] = $sum;
            }

            $literal[] = $row;
        }

        return new static($literal);
    }

    /**
     * @param float $value
     * @return Matrix
     */
    public function multiplyScalar($value)
    {
        return $this->map(function ($element) use ($value) {
            return $element * $value;
        });
    }

    /**
     * @return Matrix
     * @throws MatrixException
     */
    public function inverse()
    {
        if (!$this->isSquare()) {
            throw new MatrixException('Inverse can only be called on square matrices: ' . print_r($this->internal, true));
        }

        if ($this->determinant() === 0) {
            throw new MatrixException('This matrix has a zero determinant and is therefore not invertable: ' . print_r($this->internal, true));
        }

        return $this->getLUDecomp()->inverse();
    }

    /**
     * @return float The matrix's determinant
     * @throws MatrixException
     */
    public function determinant()
    {
        if (!$this->isSquare()) {
            throw new MatrixException('Determinants can only be called on square matrices: ' . print_r($this->internal, true));
        }

        return $this->getLUDecomp()->determinant();
    }

    /**
     * @return LUDecomposition
     */
    protected function getLUDecomp()
    {
        if (!$this->decomposition) {
            $this->decomposition = new LUDecomposition($this->internal);
        }

        return $this->decomposition;
    }

    /**
     * @return boolean
     * @deprecated Not useful enough to remain. Test if the transpose is equal, instead.
     */
    public function isSymmetric()
    {
        if (!$this->isSquare()) {
            return false;
        }

        for ($i = 0; $i < $this->getRowCount(); $i++) {
            for ($j = 0; $j < $this->getColumnCount(); $j++) {
                if ($i == $j) {
                    continue;
                }

                if ($this->get($i, $j) != $this->get($j, $i)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return $this->internal;
    }

    /**
     * @param Matrix $other
     * @return Matrix
     * @throws MatrixException
     */
    public function concatenateBottom(Matrix $other)
    {
        if ($this->getColumnCount() !== $other->getColumnCount()) {
            throw new MatrixException(
                'Cannot concatenate matrices of incompatible size: '
                . print_r($this->internal, true)
                . ' and '
                . print_r($other->internal, true)
            );
        }

        return new static(array_merge($this->internal, $other->internal));
    }

    /**
     * @param Matrix $other
     * @return Matrix
     * @throws MatrixException
     */
    public function concatenateRight(Matrix $other)
    {
        if ($this->getRowCount() !== $other->getRowCount()) {
            throw new MatrixException(
                'Cannot concatenate matrices of incompatible size: '
                . print_r($this->internal, true)
                . ' and '
                . print_r($other->internal, true)
            );
        }

        $concatenated = [];

        for ($i = 0; $i < $this->getRowCount(); $i++) {
            $concatenated[] = array_merge($this->internal[$i], $other->internal[$i]);
        }

        return new static($concatenated);
    }

    /**
     * @return Matrix
     */
    public function diagonal()
    {
        $diagonal = [];
        $max = min([$this->getRowCount(), $this->getColumnCount()]);

        for ($i = 0; $i < $max; $i++) {
            $diagonal[] = $this->get($i, $i);
        }

        return new static([$diagonal]);
    }

    /**
     * @param Matrix $matrixB
     * @return boolean
     */
    public function equals(Matrix $matrixB)
    {
        return $this->internal === $matrixB->internal;
    }

    /**
     * Returns a new matrix with the selected row and column removed, useful for
     * calculating determinants or other recursive operations on matrices.
     *
     * @param int|null $row Row to remove, null to remove no row.
     * @param int|null $column Column to remove, null to remove no column.
     * @return Matrix
     * @deprecated
     */
    public function submatrix($row = null, $column = null)
    {
        $literal = [];

        for ($i = 0; $i < $this->getRowCount(); $i++) {
            if ($i === $row) {
                continue;
            }

            $rowLiteral = [];

            for ($j = 0; $j < $this->getColumnCount(); $j++) {
                if ($j === $column) {
                    continue;
                }

                $rowLiteral[] = $this->get($i, $j);
            }

            $literal[] = $rowLiteral;
        }

        return new static($literal);
    }

    /**
     * Subtracts either another matrix or a scalar from the current matrix,
     * returning a new matrix instance.
     *
     * @param Matrix|int|float $value Matrix or scalar to subtract from this matrix
     * @return Matrix
     * @throws MatrixException
     * @deprecated Use `subtractMatrix` or `subtractScalar` instead.
     */
    public function subtract($value)
    {
        if ($value instanceof Matrix) {
            return $this->subtractMatrix($value);
        }

        return $this->subtractScalar($value);
    }

    /**
     * @param Matrix $value
     * @return Matrix
     * @throws MatrixException
     */
    public function subtractMatrix(Matrix $value)
    {
        if ($this->getRowCount() !== $value->getRowCount() || $this->getColumnCount() !== $value->columnCount) {
            throw new MatrixException('Cannot subtract two matrices of different size.');
        }

        return $this->map(function ($element, $i, $j) use ($value) {
            return $element - $value->get($i, $j);
        });
    }

    /**
     * @param float $value
     * @return Matrix
     */
    public function subtractScalar($value)
    {
        return $this->map(function ($element) use ($value) {
            return $element - $value;
        });
    }

    /**
     * @return float
     * @throws MatrixException
     */
    public function trace()
    {
        if (!$this->isSquare()) {
            throw new MatrixException('Trace can only be called on square matrices: ' . print_r($this->internal, true));
        }

        $trace = 0;

        for ($i = 0; $i < $this->getRowCount(); $i++) {
            $trace += $this->get($i, $i);
        }

        return $trace;
    }

    /**
     * @return Matrix
     */
    public function transpose()
    {
        $literal = [];

        for ($i = 0; $i < $this->getColumnCount(); $i++) {
            $literal[] = [];

            for ($j = 0; $j < $this->getRowCount(); $j++) {
                $literal[$i][] = $this->get($j, $i);
            }
        }

        return new static($literal);
    }

    /**
     * @param mixed $offset
     * @return bool
     * @deprecated Use `get()` instead of ArrayAccess methods.
     */
    public function offsetExists($offset)
    {
        return isset($this->internal[$offset]);
    }

    /**
     * @param mixed $offset
     * @return mixed
     * @deprecated Use `get()` instead of ArrayAccess methods.
     */
    public function offsetGet($offset)
    {
        return $this->internal[$offset];
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     * @throws MatrixException
     * @deprecated Use `get()` instead of ArrayAccess methods.
     */
    public function offsetSet($offset, $value)
    {
        throw new MatrixException('Attempt to set a value on a matrix. Matrix instances are immutable.');
    }

    /**
     * @param mixed $offset
     * @throws MatrixException
     * @deprecated Use `get()` instead of ArrayAccess methods.
     */
    public function offsetUnset($offset)
    {
        throw new MatrixException('Attempt to unset a value on a matrix. Matrix instances are immutable.');
    }

    /**
     * Magic method to make the public "properties" read-only.
     *
     * @param string $property
     * @return int|null
     * @deprecated Use `getColumns` or `getRows` instead
     */
    public function __get($property)
    {
        switch ($property) {
            case 'columns':
                return $this->getColumnCount();
            case 'rows':
                return $this->getRowCount();
            default:
                return null;
        }
    }

    /**
     * @return int
     */
    public function getColumnCount()
    {
        return $this->columnCount;
    }

    /**
     * @return int
     */
    public function getRowCount()
    {
        return $this->rowCount;
    }

    /**
     * @return string
     * @deprecated
     */
    public function __toString()
    {
        $rowStrings = array_map(function ($row) {
            return '[' . implode(', ', $row) . ']';
        }, $this->internal);

        return '[' . implode(', ', $rowStrings) . ']';
    }
}

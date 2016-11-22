<?php

declare(strict_types = 1);

namespace mcordingley\LinearAlgebra;

class Matrix
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
     * @var Matrix
     */
    protected $luDecomposition;

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
    protected function isLiteralValid(array $literal): bool
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
    public static function identity(int $size): Matrix
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
     * @param int $row
     * @param int $column
     * @return float
     */
    public function get($row, $column): float
    {
        return $this->internal[$row][$column];
    }

    /**
     * @return boolean
     */
    public function isSquare(): bool
    {
        return $this->columnCount === $this->rowCount;
    }

    /**
     * @return int
     */
    public function getColumnCount(): int
    {
        return $this->columnCount;
    }

    /**
     * @return int
     */
    public function getRowCount(): int
    {
        return $this->rowCount;
    }

    /**
     * @param Matrix $matrix
     * @return boolean
     */
    public function equals(Matrix $matrix): bool
    {
        return $this->internal === $matrix->internal;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return $this->internal;
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
    public function map(callable $callback): Matrix
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
     * @param Matrix $value
     * @return Matrix
     * @throws MatrixException
     */
    public function addMatrix(Matrix $value): Matrix
    {
        if ($this->getRowCount() !== $value->getRowCount() || $this->getColumnCount() !== $value->getColumnCount()) {
            throw new MatrixException('Cannot add two matrices of different size.');
        }

        return $this->map(function ($element, $i, $j) use ($value) {
            return $element + $value->get($i, $j);
        });
    }

    /**
     * @param float $value
     * @return Matrix
     */
    public function addScalar($value): Matrix
    {
        return $this->map(function ($element) use ($value) {
            return $element + $value;
        });
    }

    /**
     * @param Matrix $value
     * @return Matrix
     * @throws MatrixException
     */
    public function subtractMatrix(Matrix $value): Matrix
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
    public function subtractScalar(float $value): Matrix
    {
        return $this->map(function ($element) use ($value) {
            return $element - $value;
        });
    }

    /**
     * @param Matrix $value
     * @return Matrix
     * @throws MatrixException
     */
    public function multiplyMatrix(Matrix $value): Matrix
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
    public function multiplyScalar(float $value): Matrix
    {
        return $this->map(function ($element) use ($value) {
            return $element * $value;
        });
    }

    /**
     * @param Matrix $value
     * @return Matrix
     * @throws MatrixException
     * @link https://en.wikipedia.org/wiki/Hadamard_product_%28matrices%29
     */
    public function entrywise(Matrix $value): Matrix
    {
        if ($this->getRowCount() !== $value->getRowCount() || $this->getColumnCount() !== $value->getColumnCount()) {
            throw new MatrixException('Unable to take the entrywise product of matrices of dissimilar size.');
        }

        $rows = $this->getRowCount();
        $columns = $this->getColumnCount();
        $product = [];

        for ($row = 0; $row < $rows; $row++) {
            $product[] = [];

            for ($column = 0; $column < $columns; $column++) {
                $product[$row][$column] = $this->get($row, $column) * $value->get($row, $column);
            }
        }

        return new static($product);
    }

    /**
     * @return Matrix
     * @throws MatrixException
     */
    public function adjoint(): Matrix
    {
        if (!$this->isSquare()) {
            throw new MatrixException('Adjoints can only be called on square matrices: ' . print_r($this->internal, true));
        }

        return $this->inverse()->multiplyScalar($this->determinant());
    }

    /**
     * @return Matrix
     * @throws MatrixException
     */
    public function inverse(): Matrix
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
    public function determinant(): float
    {
        if (!$this->isSquare()) {
            throw new MatrixException('Determinants can only be called on square matrices: ' . print_r($this->internal, true));
        }

        return $this->getLUDecomp()->determinant();
    }

    /**
     * @return Matrix
     */
    public function getLUDecomposition(): Matrix
    {
        if (!$this->luDecomposition) {
            $decomposition = new static($this->toArray());

            for ($k = 0; $k < $this->rowCount; $k++) {
                for ($i = $k + 1; $i < $this->rowCount; $i++) {
                    $decomposition->internal[$i][$k] = $decomposition->internal[$i][$k] / $decomposition->internal[$k][$k];
                }

                for ($i = $k + 1; $i < $this->rowCount; $i++) {
                    for ($j = $k + 1; $j < $this->rowCount; $j++) {
                        $decomposition->internal[$i][$j] = $decomposition->internal[$i][$j] - $decomposition->internal[$i][$k] * $decomposition->internal[$k][$j];
                    }
                }
            }

            $this->luDecomposition = $decomposition;
        }

        return $this->luDecomposition;
    }

    /**
     * @param Matrix $other
     * @return Matrix
     * @throws MatrixException
     */
    public function concatenateBottom(Matrix $other): Matrix
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
    public function concatenateRight(Matrix $other): Matrix
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
    public function diagonal(): Matrix
    {
        $diagonal = [];
        $max = min([$this->getRowCount(), $this->getColumnCount()]);

        for ($i = 0; $i < $max; $i++) {
            $diagonal[] = $this->get($i, $i);
        }

        return new static([$diagonal]);
    }

    /**
     * @return float
     * @throws MatrixException
     */
    public function trace(): float
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
    public function transpose(): Matrix
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
}

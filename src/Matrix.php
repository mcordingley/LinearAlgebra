<?php

declare(strict_types = 1);

namespace mcordingley\LinearAlgebra;

use mcordingley\LinearAlgebra\Decomposition\LU;

final class Matrix
{
    /**
     * @var array
     */
    private $internal;

    /**
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
        if (!static::isLiteralValid($literal)) {
            throw new MatrixException('Invalid array provided: ' . print_r($literal, true));
        }

        $this->internal = $literal;
    }

    /**
     * @param array $literal
     * @return boolean
     */
    private static function isLiteralValid(array $literal): bool
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
        return $this->getColumnCount() === $this->getRowCount();
    }

    /**
     * @return int
     */
    public function getColumnCount(): int
    {
        return count($this->internal[0]);
    }

    /**
     * @return int
     */
    public function getRowCount(): int
    {
        return count($this->internal);
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

        for ($i = 0, $rows = $this->getRowCount(); $i < $rows; $i++) {
            $row = [];

            for ($j = 0, $columns = $this->getColumnCount(); $j < $columns; $j++) {
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
        $this->checkEqualSize($value);

        return $this->map(function ($element, $i, $j) use ($value) {
            return $element + $value->get($i, $j);
        });
    }

    /**
     * @param Matrix $matrix
     * @throws MatrixException
     */
    private function checkEqualSize(Matrix $matrix)
    {
        if ($this->getRowCount() !== $matrix->getRowCount() || $this->getColumnCount() !== $matrix->getColumnCount()) {
            throw new MatrixException('Operation requires matrices of equal size: ' . print_r($this->internal, true) . ' ' . print_r($matrix->internal, true));
        }
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
        $this->checkEqualSize($value);

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

        for ($i = 0, $rows = $this->getRowCount(); $i < $rows; $i++) {
            $row = [];

            for ($j = 0, $valueColumns = $value->getColumnCount(); $j < $valueColumns; $j++) {
                $sum = 0;

                for ($k = 0, $columns = $this->getColumnCount(); $k < $columns; $k++) {
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
        $this->checkEqualSize($value);

        $product = [];

        for ($row = 0, $rows = $this->getRowCount(); $row < $rows; $row++) {
            $product[] = [];

            for ($column = 0, $columns = $this->getColumnCount(); $column < $columns; $column++) {
                $product[$row][$column] = $this->get($row, $column) * $value->get($row, $column);
            }
        }

        return new static($product);
    }

    /**
     * @return Matrix
     * @throws MatrixException
     */
    public function adjugate(): Matrix
    {
        $this->checkSquare();

        return $this->inverse()->multiplyScalar($this->determinant());
    }

    /**
     * @throws MatrixException
     */
    private function checkSquare()
    {
        if (!$this->isSquare()) {
            throw new MatrixException('Operation can only be called on square matrix: ' . print_r($this->internal, true));
        }
    }

    /**
     * @return Matrix
     * @throws MatrixException
     */
    public function inverse(): Matrix
    {
        $this->checkSquare();

        if ($this->determinant() === 0) {
            throw new MatrixException('This matrix has a zero determinant and is therefore not invertable: ' . print_r($this->internal, true));
        }

        return $this->getLUDecomp()->inverse();
    }

    /**
     * @return float
     */
    public function determinant(): float
    {
        $this->checkSquare();

        try {
            $decomp = (new LU($this))->getUpper();
        } catch (MatrixException $exception) {
            // Singular matrix, so determinant is defined to be zero.
            return 0.0;
        }

        $determinant = 1.0;

        for ($i = 0, $size = $decomp->getRowCount(); $i < $size; $i++) {
            $determinant *= $decomp->get($i, $i);
        }

        return $determinant;
    }

    /**
     * @param bool $unitriangular True to have ones along the diagonal. False to include parent matrix values, instead.
     * @return Matrix
     */
    public function upper(bool $unitriangular): Matrix
    {
        $triangle = [];

        for ($row = 0, $rows = $this->getRowCount(); $row < $rows; $row++) {
            $triangle[] = [];

            for ($column = 0, $columns = $this->getColumnCount(); $column < $columns; $column++) {
                if ($unitriangular && $row === $column) {
                    $triangle[$row][] = 1;
                } else {
                    $triangle[$row][] = $column < $row ? 0 : $this->internal[$row][$column];
                }
            }
        }

        return new static($triangle);
    }

    /**
     * @param bool $unitriangular True to have ones along the diagonal. False to include parent matrix values, instead.
     * @return Matrix
     */
    public function lower(bool $unitriangular): Matrix
    {
        $triangle = [];

        for ($row = 0, $rows = $this->getRowCount(); $row < $rows; $row++) {
            $triangle[] = [];

            for ($column = 0, $columns = $this->getColumnCount(); $column < $columns; $column++) {
                if ($unitriangular && $row === $column) {
                    $triangle[$row][] = 1;
                } else {
                    $triangle[$row][] = $row < $column ? 0 : $this->internal[$row][$column];
                }
            }
        }

        return new static($triangle);
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

        for ($i = 0, $rows = $this->getRowCount(); $i < $rows; $i++) {
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

        for ($row = 0, $rows = $this->getRowCount(); $row < $rows; $row++) {
            $diagonal[] = [];

            for ($column = 0, $columns = $this->getColumnCount(); $column < $columns; $column++) {
                $diagonal[$row][] = $row === $column ? $this->get($row, $column) : 0;
            }
        }

        return new static($diagonal);
    }

    /**
     * @return float
     * @throws MatrixException
     */
    public function trace(): float
    {
        $this->checkSquare();

        $trace = 0;

        for ($i = 0, $rows = $this->getRowCount(); $i < $rows; $i++) {
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

        for ($i = 0, $columns = $this->getColumnCount(); $i < $columns; $i++) {
            $literal[] = [];

            for ($j = 0, $rows = $this->getRowCount(); $j < $rows; $j++) {
                $literal[$i][] = $this->get($j, $i);
            }
        }

        return new static($literal);
    }
}

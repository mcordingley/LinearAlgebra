<?php

declare(strict_types = 1);

namespace MCordingley\LinearAlgebra;

use ArrayAccess;
use MCordingley\LinearAlgebra\Decomposition\LUP;

class Matrix implements ArrayAccess
{
    /** @var array */
    protected $internal;

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
        return $literal && $literal[0] && static::subArraysAreEqualSize($literal);
    }

    /**
     * @param array $subArrays
     * @return bool
     */
    private static function subArraysAreEqualSize(array $subArrays): bool
    {
        $firstSize = count($subArrays[0]);

        foreach ($subArrays as $subArray) {
            if (count($subArray) !== $firstSize) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param int $size How many rows and columns the identity matrix should have
     * @return self
     */
    final public static function identity(int $size): self
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
    final public function get($row, $column): float
    {
        return $this->internal[$row][$column];
    }

    /**
     * @return boolean
     */
    final public function isSquare(): bool
    {
        return $this->getColumnCount() === $this->getRowCount();
    }

    /**
     * @return int
     */
    final public function getColumnCount(): int
    {
        return count($this->internal[0]);
    }

    /**
     * @return int
     */
    final public function getRowCount(): int
    {
        return count($this->internal);
    }

    /**
     * @param Matrix $matrix
     * @return boolean
     */
    final public function equals(Matrix $matrix): bool
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
     * @return self
     */
    final public function map(callable $callback): self
    {
        $literal = [];

        for ($i = 0, $rows = $this->getRowCount(); $i < $rows; $i++) {
            $row = [];

            for ($j = 0, $columns = $this->getColumnCount(); $j < $columns; $j++) {
                $row[] = $callback($this->get($i, $j), $i, $j, $this);
            }

            $literal[] = $row;
        }

        return new self($literal);
    }

    /**
     * @param self $value
     * @return self
     * @throws MatrixException
     */
    final public function addMatrix(self $value): self
    {
        $this->checkEqualSize($value);

        return $this->map(function (float $element, int $i, int $j) use ($value) {
            return $element + $value->get($i, $j);
        });
    }

    /**
     * @param self $matrix
     * @throws MatrixException
     */
    private function checkEqualSize(self $matrix)
    {
        if ($this->getRowCount() !== $matrix->getRowCount() || $this->getColumnCount() !== $matrix->getColumnCount()) {
            throw new MatrixException('Operation requires matrices of equal size: ' . print_r($this->internal, true) . ' ' . print_r($matrix->internal, true));
        }
    }

    /**
     * @param float $value
     * @return self
     */
    final public function addScalar(float $value): self
    {
        return $this->map(function (float $element) use ($value) {
            return $element + $value;
        });
    }

    /**
     * @param self $value
     * @return self
     * @throws MatrixException
     */
    final public function subtractMatrix(self $value): self
    {
        $this->checkEqualSize($value);

        return $this->map(function (float $element, int $i, int $j) use ($value) {
            return $element - $value->get($i, $j);
        });
    }

    /**
     * @param float $value
     * @return self
     */
    final public function subtractScalar(float $value): self
    {
        return $this->map(function (float $element) use ($value) {
            return $element - $value;
        });
    }

    /**
     * @param self $value
     * @return self
     * @throws MatrixException
     */
    final public function multiplyMatrix(self $value): self
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
     * @return self
     */
    final public function multiplyScalar(float $value): self
    {
        return $this->map(function (float $element) use ($value) {
            return $element * $value;
        });
    }


    /**
     * @param float $value
     * @return self
     * @throws MatrixException
     */
    final public function divideScalar(float $value): self
    {
        if ($value == 0) {
            throw new MatrixException("Zero can not be denominator.");
        }

        return $this->map(function (float $element) use ($value) {
            return $element / $value;
        });
    }

    /**
     * @param self $value
     * @return self
     * @throws MatrixException
     * @link https://en.wikipedia.org/wiki/Hadamard_product_%28matrices%29
     */
    final public function entrywise(self $value): self
    {
        $this->checkEqualSize($value);

        return $this->map(function (float $element, int $i, int $j) use ($value) {
            return $element * $value->get($i, $j);
        });
    }

    /**
     * @return self
     * @throws MatrixException
     */
    final public function adjugate(): self
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
     * @return self
     * @throws MatrixException
     */
    final public function inverse(): self
    {
        $this->checkSquare();

        $size = $this->getRowCount();
        $transpose = $this->transpose();
        $aTa = $transpose->multiplyMatrix($this);

        $padded = $aTa->pad($size);
        $inverted = $this->recursiveSolveInverse($padded);
        $trimmed = $inverted->sliceRows(0, $size)->sliceColumns(0, $size);

        return $trimmed->multiplyMatrix($transpose);
    }

    /**
     * @param int $size
     * @return self
     */
    private function pad(int $size): self
    {
        $nextPower = pow(2, ceil(log($size, 2)));
        $padded = $this->toArray();

        for ($row = 0; $row < $size; $row++) {
            for ($column = $size; $column < $nextPower; $column++) {
                $padded[$row][$column] = $row === $column ? 1 : 0;
            }
        }

        for ($row = $size; $row < $nextPower; $row++) {
            $padded[] = [];

            for ($column = 0; $column < $nextPower; $column++) {
                $padded[$row][$column] = $row === $column ? 1 : 0;
            }
        }

        return new static($padded);
    }

    /**
     * @param self $source
     * @return self
     */
    private function recursiveSolveInverse(self $source): self
    {
        $size = $source->getRowCount();

        if ($size === 1) {
            return new static([[1 / $source->get(0, 0)]]);
        }

        $half = (int) ($size / 2);

        // Partition source matrix.
        $B = $source->sliceRows(0, $half)->sliceColumns(0, $half);
        $CT = $source->sliceRows(0, $half)->sliceColumns($half);
        $D = $source->sliceRows($half)->sliceColumns($half);
        $C = $source->sliceRows($half)->sliceColumns(0, $half);

        // Handle intermediate calculations.
        $Binv = $this->recursiveSolveInverse($B);
        $W = $C->multiplyMatrix($Binv);
        $WT = $W->transpose();
        $X = $W->multiplyMatrix($CT);
        $S = $D->subtractMatrix($X);
        $Sinv = $this->recursiveSolveInverse($S);
        $V = $Sinv;
        $Y = $Sinv->multiplyMatrix($W);
        $YT = $Y->transpose();
        $T = $YT->multiplyScalar(-1);
        $U = $Y->multiplyScalar(-1);
        $Z = $WT->multiplyMatrix($Y);
        $R = $Binv->addMatrix($Z);

        // Stitch together intermediate results into the final result
        return $R->concatenateRight($T)->concatenateBottom($U->concatenateRight($V));
    }

    /**
     * @param int $offset
     * @param int|null $length
     * @return self
     */
    final public function sliceRows(int $offset, int $length = null): self
    {
        return new static(array_slice($this->toArray(), $offset, $length));
    }

    /**
     * @param int $offset
     * @param int|null $length
     * @return self
     */
    final public function sliceColumns(int $offset, int $length = null): self
    {
        return new static(array_map(function (array $row) use ($offset, $length) {
            return array_slice($row, $offset, $length);
        }, $this->toArray()));
    }

    /**
     * @param int $offset
     * @param int|null $length
     * @param array|null $replacement
     * @return Matrix
     * @throws MatrixException
     */
    final public function spliceRows(int $offset, int $length = null, array $replacement = null): self
    {
        if ($replacement) {
            if ($this->getColumnCount() !== count($replacement[0])) {
                throw new MatrixException(
                    'Cannot splice ['
                    . count($replacement)
                    . '] columns into matrix of ['
                    . $this->getRowCount()
                    . '] column.'
                );
            }

            if (!static::subArraysAreEqualSize($replacement)) {
                throw new MatrixException('Cannot splice in new rows of unequal size.');
            }
        }

        $spliced = $this->toArray();

        array_splice($spliced, $offset, $length, $replacement);

        return new static($spliced);
    }

    /**
     * @param int $offset
     * @param int|null $length
     * @param array|null $replacement
     * @return Matrix
     * @throws MatrixException
     */
    final public function spliceColumns(int $offset, int $length = null, array $replacement = null): self
    {
        if ($replacement) {
            if ($this->getRowCount() !== count($replacement)) {
                throw new MatrixException(
                    'Cannot splice ['
                    . count($replacement)
                    . '] row into matrix of ['
                    . $this->getRowCount()
                    . '] rows.'
                );
            }

            if (!static::subArraysAreEqualSize($replacement)) {
                throw new MatrixException('Cannot splice in new columns of unequal size.');
            }
        }

        $rowIndex = 0;

        $spliced = array_map(function (array $row) use ($offset, $length, $replacement, &$rowIndex) {
            array_splice($row, $offset, $length, $replacement ? $replacement[$rowIndex++] : null);

            return $row;
        }, $this->toArray());

        return new static($spliced);
    }

    /**
     * @return float
     */
    final public function determinant(): float
    {
        $this->checkSquare();

        try {
            $decomp = new LUP($this);
        } catch (MatrixException $exception) {
            // Singular matrix, so determinant is defined to be zero.
            return 0.0;
        }

        $upper = $decomp->upper();

        $determinant = 1.0;

        for ($i = 0, $size = $upper->getRowCount(); $i < $size; $i++) {
            $determinant *= $upper->get($i, $i);
        }

        $sign = $decomp->parity() % 2 ? -1 : 1;

        return $sign * $determinant;
    }

    /**
     * @param bool $unitriangular True to have ones along the diagonal. False to include parent matrix values, instead.
     * @return self
     */
    final public function upper(bool $unitriangular): self
    {
        return $this->map(function (float $element, int $i, int $j) use ($unitriangular) {
            if ($unitriangular && $i === $j) {
                return 1;
            }

            return $j < $i ? 0 : $element;
        });
    }

    /**
     * @param bool $unitriangular True to have ones along the diagonal. False to include parent matrix values, instead.
     * @return self
     */
    final public function lower(bool $unitriangular): self
    {
        return $this->map(function (float $element, int $i, int $j) use ($unitriangular) {
            if ($unitriangular && $i === $j) {
                return 1;
            }

            return $i < $j ? 0 : $element;
        });
    }

    /**
     * @param self $other
     * @return self
     * @throws MatrixException
     */
    final public function concatenateBottom(self $other): self
    {
        return $this->spliceRows($this->getRowCount(), 0, $other->toArray());
    }

    /**
     * @param self $other
     * @return self
     * @throws MatrixException
     */
    final public function concatenateRight(self $other): self
    {
        return $this->spliceColumns($this->getColumnCount(), 0, $other->toArray());
    }

    /**
     * @return self
     */
    final public function diagonal(): self
    {
        return $this->map(function (float $element, int $i, int $j) {
            return $i === $j ? $element : 0;
        });
    }

    /**
     * @return float
     * @throws MatrixException
     */
    final public function trace(): float
    {
        $this->checkSquare();

        $trace = 0;

        for ($i = 0, $rows = $this->getRowCount(); $i < $rows; $i++) {
            $trace += $this->get($i, $i);
        }

        return $trace;
    }

    /**
     * @return self
     */
    final public function transpose(): self
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

    /**
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset): bool
    {
        return isset($this->internal[$offset]);
    }

    /**
     * @param mixed $offset
     * @return Vector|null
     * @throws MatrixException
     */
    public function offsetGet($offset)
    {
        return $this->offsetExists($offset) ? new Vector($this->internal[$offset]) : null;
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     * @throws MatrixException
     */
    final public function offsetSet($offset, $value)
    {
        throw new MatrixException('Matrices are immutable.');
    }

    /**
     * @param mixed $offset
     * @throws MatrixException
     */
    final public function offsetUnset($offset)
    {
        throw new MatrixException('Matrices are immutable.');
    }
}

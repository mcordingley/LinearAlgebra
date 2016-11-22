<?php

declare(strict_types = 1);

namespace mcordingley\LinearAlgebra;

final class Matrix
{
    /**
     * @var int
     */
    private $columnCount;

    /**
     * @var int
     */
    private $rowCount;

    /**
     * @var array
     */
    private $internal;

    /**
     * @var Matrix
     */
    private $luDecomposition;

    /**
     * @var Matrix
     */
    private $lupDecomposition;

    /**
     * @var Matrix
     */
    private $lupPermutation;

    /**
     * @var Matrix
     */
    private $upper;

    /**
     * @var Matrix
     */
    private $lower;

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
    private function isLiteralValid(array $literal): bool
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
        $this->checkEqualSize($value);

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
            $decomp = $this->getLUDecomposition();
        } catch (MatrixException $exception) {
            // Singular matrix, so determinant is defined to be zero.
            return 0.0;
        }

        $determinant = 1.0;

        for ($i = 0; $i < $decomp->rowCount; $i++) {
            $determinant *= $decomp->get($i, $i);
        }

        return $determinant;
    }

    /**
     * @return Matrix
     */
    public function getLUDecomposition(): Matrix
    {
        if (!$this->luDecomposition) {
            $this->checkSquare();

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
     * @return Matrix
     * @throws MatrixException
     */
    public function getLUPDecomposition(): Matrix
    {
        if (!$this->lupDecomposition) {
            $this->calculateLUP();
        }

        return $this->lupDecomposition;
    }

    /**
     * @return Matrix
     * @throws MatrixException
     */
    public function getLUPPermutation(): Matrix
    {
        if (!$this->lupDecomposition) {
            $this->calculateLUP();
        }

        return $this->lupPermutation;
    }

    /**
     * @throws MatrixException
     */
    private function calculateLUP()
    {
        $this->checkSquare();

        $decomposition = new static($this->toArray());
        $permutations = range(0, $this->rowCount - 1);

        for ($k = 0; $k < $this->rowCount; $k++) {
            $p = 0.0;
            $kPrime = $k;

            for ($i = $k; $i < $this->rowCount; $i++) {
                $absolute = abs($decomposition->internal[$i][$k]);

                if ($absolute > $p) {
                    $p = $absolute;
                    $kPrime = $i;
                }
            }

            if ($p === 0.0) {
                throw new MatrixException('Cannot take the LUP decomposition of a singular matrix: ' . print_r($this->internal, true));
            }

            list($permutations[$k], $permutations[$kPrime]) = [$permutations[$kPrime], $permutations[$k]];

            for ($i = 0; $i < $this->rowCount; $i++) {
                list($decomposition->internal[$k][$i], $decomposition->internal[$kPrime][$i]) = [$decomposition->internal[$kPrime][$i], $decomposition->internal[$k][$i]];
            }

            for ($i = $k + 1; $i < $this->rowCount; $i++) {
                $decomposition->internal[$i][$k] = $decomposition->internal[$i][$k] / $decomposition->internal[$k][$k];

                for ($j = $k + 1; $j < $this->rowCount; $j++) {
                    $decomposition->internal[$i][$j] = $decomposition->internal[$i][$j] - $decomposition->internal[$i][$k] * $decomposition->internal[$k][$j];
                }
            }
        }

        $permutationMatrix = [];

        for ($i = 0; $i < $this->rowCount; $i++) {
            $permutationMatrix[] = [];

            for ($j = 0; $j < $this->rowCount; $j++) {
                $permutationMatrix[$i][] = $permutations[$i] === $j ? 1 : 0;
            }
        }

        $this->lupDecomposition = $decomposition;
        $this->lupPermutation = new static($permutationMatrix);
    }

    /**
     * @param bool $unitriangular True to have ones along the diagonal. False to include parent matrix values, instead.
     * @return Matrix
     */
    public function upper(bool $unitriangular): Matrix
    {
        if (!$this->upper) {
            $triangle = [];

            for ($row = 0; $row < $this->rowCount; $row++) {
                $triangle[] = [];

                for ($column = 0; $column < $this->columnCount; $column++) {
                    if ($unitriangular && $row === $column) {
                        $triangle[$row][] = 1;
                    } else {
                        $triangle[$row][] = $column < $row ? 0 : $this->internal[$row][$column];
                    }
                }
            }

            $this->upper = new static($triangle);
        }

        return $this->upper;
    }

    /**
     * @param bool $unitriangular True to have ones along the diagonal. False to include parent matrix values, instead.
     * @return Matrix
     */
    public function lower(bool $unitriangular): Matrix
    {
        if (!$this->lower) {
            $triangle = [];

            for ($row = 0; $row < $this->rowCount; $row++) {
                $triangle[] = [];

                for ($column = 0; $column < $this->columnCount; $column++) {
                    if ($unitriangular && $row === $column) {
                        $triangle[$row][] = 1;
                    } else {
                        $triangle[$row][] = $row < $column ? 0 : $this->internal[$row][$column];
                    }
                }
            }

            $this->lower = new static($triangle);
        }

        return $this->lower;
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

        for ($row = 0; $row < $this->rowCount; $row++) {
            $diagonal[] = [];

            for ($column = 0; $column < $this->rowCount; $column++) {
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

<?php

declare(strict_types = 1);

namespace MCordingley\LinearAlgebra;

class Vector
{
    /**
     * Number of elements
     * @var int
     */
    private $size;

    /**
     * Vector
     * @var array
     */
    private $literal;

    public function __construct(array $literal)
    {
        if (!static::isLiteralValid($literal)) {
            throw new VEctorException('Invalid array provided: ' . print_r($literal, true));
        }

        $this->literal = $literal;
        $this->size = count($literal);
    }

    /**
     * @param array $literal
     * @return boolean
     */
    private static function isLiteralValid(array $literal): bool
    {
        return $literal && isset($literal[0]);
    }

    /**************************************************************************
     * BASIC VECTOR GETTERS
     *  - getVector
     *  - getSize
     *  - get
     *  - asColumnMatrix
     *  - asRowMatrix
     **************************************************************************/

    /**
     * Get matrix
     * @return array of arrays
     */
    public function getVector(): array
    {
        return $this->literal;
    }

    /**
     * Get item count
     * @return int number of items
     */
    public function getSize(): int
    {
        return $this->size;
    }

    /**
     * @param int $i
     * @return int
     * @throws VectorException
     */
    public function get(int $i)
    {
        if ($i >= $this->size) {
            throw new VectorException("Element $i does not exist");
        }

        return $this->literal[$i];
    }

    /**
     * Get the vector as an nx1 column matrix
     *
     * Example:
     *  V = [1, 2, 3]
     *
     *      [1]
     *  R = [2]
     *      [3]
     *
     * @return Matrix
     */
    public function asColumnMatrix()
    {
        $literal = [];
        foreach ($this->literal as $element) {
            $literal[] = [$element];
        }

        return new Matrix(array($literal));
    }

    /**
     * Get the vector as a 1xn row matrix
     *
     * Example:
     *  V = [1, 2, 3]
     *  R = [
     *   [1, 2, 3]
     *  ]
     *
     * @return Matrix
     */
    public function asRowMatrix()
    {
        return new Matrix([$this->literal]);
    }

    /**************************************************************************
     * VECTOR OPERATIONS - Return a number
     *  - sum
     *  - length (magnitude)
     *  - dotProduct (innerProduct)
     **************************************************************************/

    /**
     * Sum of all elements
     *
     * @return number
     */
    public function sum()
    {
        return array_sum($this->literal);
    }

    /**
     * Vector length (magnitude)
     * Same as l2-norm
     *
     * @return number
     */
    public function length()
    {
        return $this->l2norm();
    }

    /**
     * Dot product (inner product) (A⋅B)
     * https://en.wikipedia.org/wiki/Dot_product
     *
     * @param self $value
     *
     * @return number
     */
    public function dotProduct(self $value)
    {
        if ($value->getSize() !== $this->size) {
            throw new VectorException('Vectors have different number of items');
        }

        return array_sum(array_map(
            function ($a, $b) {
                return $a * $b;
            },
            $this->literal,
            $value->getVector()
        ));
    }

    /**
     * Inner product (convience method for dot product) (A⋅B)
     *
     * @param Vector $B
     *
     * @return number
     */
    public function innerProduct(Vector $B)
    {
        return $this->dotProduct($B);
    }

    /**************************************************************************
     * VECTOR OPERATIONS - Return a Vector or Matrix
     *  - add
     *  - subtract
     *  - scalarMultiply
     *  - scalarDivide
     *  - outerProduct
     *  - normalize
     *  - projection
     **************************************************************************/

    /**
     * Add (A + B)
     *
     * A = [a₁, a₂, a₃]
     * B = [b₁, b₂, b₃]
     * A + B = [a₁ + b₁, a₂ + b₂, a₃ + b₃]
     *
     * @param self $other
     *
     * @return self
     */
    public function addVector(self $other): self
    {
        if ($other->getSize() !== $this->size) {
            throw new VectorException('Vectors must be the same length for addition');
        }

        $sums = array_fill(0, $this->size, 0);
        for($i = 0; $i < $this->size; $i++) {
            $sums[$i] = $other->literal[$i] + $this->literal[$i];
        }

        return new Vector($sums);
    }

    /**
     * Subtract (A - B)
     *
     * A = [a₁, a₂, a₃]
     * B = [b₁, b₂, b₃]
     * A - B = [a₁ - b₁, a₂ - b₂, a₃ - b₃]
     *
     * @param self $other
     *
     * @return self
     */
    public function subtractVector(Vector $other): self
    {
        if ($other->getSize() !== $this->size) {
            throw new VectorException('Vectors must be the same length for addition');
        }

        $subtracts = array_fill(0, $this->size, 0);
        for($i = 0; $i < $this->size; $i++) {
            $subtracts[$i] =  $this->literal[$i] - $other->literal[$i];
        }

        return new Vector($subtracts);
    }

    /**
     * Scalar multiplication (scale)
     * kA = [k * a₁, k * a₂, k * a₃ ...]
     *
     * @param number $value Scale factor
     *
     * @return self
     */
    public function scalarMultiply($value): self
    {
        $scalarMultiplies = array();
        for($i = 0; $i < $this->size; $i++) {
            $scalarMultiplies[$i] =  $this->literal[$i] * $value;
        }

        return new Vector($scalarMultiplies);
    }

    /**
     * Scalar divide
     * kA = [k / a₁, k / a₂, k / a₃ ...]
     *
     * @param number $value Scale factor
     *
     * @return self
     */
    public function scalarDivide($value): self
    {
        $scalarDivide = array();
        for($i = 0; $i < $this->size; $i++) {
            $scalarDivide[$i] =  $this->literal[$i] / $value;
        }

        return new Vector($scalarDivide);
    }

    /**
     * Outer product (A⨂B)
     * https://en.wikipedia.org/wiki/Outer_product
     * Same as direct product.
     *
     * @param Vector $value
     *
     * @return Matrix
     */
    public function outerProduct(Vector $value): Matrix
    {
        $R = [];
        for ($i = 0; $i < $this->size; $i++) {
            for ($j = 0; $j < $value->getSize(); $j++) {
                $R[$i][$j] = $this->literal[$i] * $value[$j];
            }
        }

        return new Matrix($R);
    }

    /**
     * Normalize (Â)
     * The normalized vector Â is a vector in the same direction of A
     * but with a norm (length) of 1. It is a unit vector.
     * http://mathworld.wolfram.com/NormalizedVector.html
     *
     *      A
     * Â ≡ ---
     *     |A|
     *
     *  where |A| is the l²-norm (|A|₂)
     *
     * @return self
     */
    public function normalize(): self
    {
        $norm = $this->l2norm();

        return $this->scalarDivide($norm);
    }

    /**
     * Projection of A onto B
     * https://en.wikipedia.org/wiki/Vector_projection#Vector_projection
     *
     *          A⋅B
     * projᵇA = --- B
     *          |B|²
     *
     * @param self $value
     *
     * @return self
     */
    public function projection(self $value): self
    {
        return $value->scalarMultiply($this->dotProduct($value) / (($value->l2norm())**2));
    }

    /**************************************************************************
     * VECTOR NORMS
     *  - l1Norm
     *  - l2Norm
     *  - maxNorm
     **************************************************************************/

    /**
     * l₁-norm (|x|₁)
     * Also known as Taxicab norm or Manhattan norm
     *
     * https://en.wikipedia.org/wiki/Norm_(mathematics)#Taxicab_norm_or_Manhattan_norm
     *
     * |x|₁ = ∑|xᵢ|
     *
     * @return number
     */
    public function l1Norm()
    {
        $sum = 0;
        foreach($this->literal as $value) {
            $sum += abs($value);
        }

        return $sum;
    }

    /**
     * l²-norm (|x|₂)
     * Also known as Euclidean norm, Euclidean length, L² distance, ℓ² distance
     * Used to normalize a vector.
     *
     * http://mathworld.wolfram.com/L2-Norm.html
     * https://en.wikipedia.org/wiki/Norm_(mathematics)#Euclidean_norm
     *         ______
     * |x|₂ = √∑|xᵢ|²
     *
     * @return number
     */
    public function l2Norm()
    {
        $literal = array();
        for ($i = 0, $rows = $this->getSize(); $i < $rows; $i++) {
            $literal[] = $this->literal[$i]**2;
        }

        return sqrt(array_sum($literal));
    }

    /**
     * Max norm (infinity norm) (|x|∞)
     *
     * |x|∞ = max |x|
     *
     * @return number
     */
    public function maxNorm()
    {
        $max = abs($this->literal[0]);
        foreach($this->literal as $value) {
            if(abs($value) > $max) {
                $max = abs($value);
            }
        }

        return $max;
    }
}

<?php

declare(strict_types = 1);

namespace MCordingley\LinearAlgebra;

class Vector extends Matrix
{
    /**
     * Number of elements
     * @var int
     */
    private $size;
    /**
     * @var array
     */
    private $vector;

    public function __construct(array $literal)
    {
        parent::__construct($literal);

        $this->vector = $literal[0];
        $this->size = count($this->vector);
    }

    /**************************************************************************
     * BASIC VECTOR GETTERS
     *  - getVector
     *  - getSize
     **************************************************************************/

    /**
     * Get matrix
     * @return array of arrays
     */
    public function getVector(): array
    {
        return $this->vector;
    }

    /**
     * Get item count
     * @return int number of items
     */
    public function getSize(): int
    {
        return $this->size;
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
        return array_sum($this->vector);
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
     *
     * Dot product (inner product) (A⋅B)
     * https://en.wikipedia.org/wiki/Dot_product
     *
     * @param self $other
     * @return float
     * @throws VectorException
     */
    public function dotProduct(self $other)
    {
        if ($other->getSize() !== $this->size) {
            throw new VectorException('Vectors have to have same size');
        }

        return array_sum(array_map(
            function ($a, $b) {
                return $a * $b;
            },
            $this->vector,
            $other->getVector()
        ));
    }

    /**
     * Inner product (convience method for dot product) (A⋅B)
     *
     * @param Vector $other
     *
     * @return number
     */
    public function innerProduct(Vector $other)
    {
        return $this->dotProduct($other);
    }

    /**************************************************************************
     * VECTOR OPERATIONS - Return a Vector or Matrix
     *  - outerProduct
     *  - normalize
     *  - projection
     **************************************************************************/

    /**
     * Outer product (A⨂B)
     * https://en.wikipedia.org/wiki/Outer_product
     * Same as direct product.
     *
     * @param Vector $other
     *
     * @return Matrix
     */
    public function outerProduct(Vector $other): Matrix
    {
        $R = [];
        for ($i = 0; $i < $this->size; $i++) {
            for ($j = 0; $j < $other->getSize(); $j++) {
                $R[$i][$j] = $this->internal[$i] * $other[$j];
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
     */
    public function normalize()
    {
        $norm = $this->l2norm();

        return $this->divideScalar($norm);
    }

    /**
     * Projection of A onto B
     * https://en.wikipedia.org/wiki/Vector_projection#Vector_projection
     *
     *          A⋅B
     * projᵇA = --- B
     *          |B|²
     *
     * @param self $other
     *
     * @return self
     */
    public function projection(self $other)
    {
        return $other->multiplyScalar($this->dotProduct($other) / (($other->l2norm())**2));
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
        foreach($this->vector as $value) {
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
     * @return float
     */
    public function l2Norm()
    {
        $literal = array();
        for ($i = 0, $rows = $this->getSize(); $i < $rows; $i++) {
            $literal[] = $this->vector[$i]**2;
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
        $max = abs($this->vector[0]);
        foreach($this->vector as $value) {
            if(abs($value) > $max) {
                $max = abs($value);
            }
        }

        return $max;
    }
}

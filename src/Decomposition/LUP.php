<?php

declare(strict_types = 1);

namespace MCordingley\LinearAlgebra\Decomposition;

use MCordingley\LinearAlgebra\Matrix;
use MCordingley\LinearAlgebra\MatrixException;

final class LUP
{
    /** @var Matrix */
    private $decomposition;

    /** @var int */
    private $parity = 0;

    /** @var array */
    private $permutation;

    /**
     * @param Matrix $source
     * @throws MatrixException
     */
    public function __construct(Matrix $source)
    {
        $this->decompose($source);
    }

    /**
     * @param Matrix $source
     * @throws MatrixException
     */
    private function decompose(Matrix $source)
    {
        $sourceLiteral = $source->toArray();
        $decompositionLiteral = $sourceLiteral;

        if (!$source->isSquare()) {
            throw new MatrixException('Operation can only be called on square matrix: ' . print_r($decompositionLiteral, true));
        }

        $size = $source->getRowCount();
        $this->permutation = range(0, $size - 1);

        for ($k = 0; $k < $size; $k++) {
            $p = 0.0;
            $kPrime = $k;

            for ($i = $k; $i < $size; $i++) {
                $absolute = abs($decompositionLiteral[$i][$k]);

                if ($absolute > $p) {
                    $p = $absolute;
                    $kPrime = $i;
                }
            }

            if ($p === 0.0) {
                throw new MatrixException('Cannot take the LUP decomposition of a singular matrix: ' . print_r($sourceLiteral, true));
            }

            if ($k !== $kPrime) {
                list($this->permutation[$k], $this->permutation[$kPrime]) = [
                    $this->permutation[$kPrime],
                    $this->permutation[$k]
                ];

                $this->parity++;
            }

            for ($i = 0; $i < $size; $i++) {
                list($decompositionLiteral[$k][$i], $decompositionLiteral[$kPrime][$i]) = [$decompositionLiteral[$kPrime][$i], $decompositionLiteral[$k][$i]];
            }

            for ($i = $k + 1; $i < $size; $i++) {
                $decompositionLiteral[$i][$k] = $decompositionLiteral[$i][$k] / $decompositionLiteral[$k][$k];

                for ($j = $k + 1; $j < $size; $j++) {
                    $decompositionLiteral[$i][$j] = $decompositionLiteral[$i][$j] - $decompositionLiteral[$i][$k] * $decompositionLiteral[$k][$j];
                }
            }
        }

        $this->decomposition = new Matrix($decompositionLiteral);
    }

    /**
     * @return Matrix
     */
    public function lower(): Matrix
    {
        return $this->decomposition->lower(true);
    }

    /**
     * @return Matrix
     */
    public function upper(): Matrix
    {
        return $this->decomposition->upper(false);
    }

    /**
     * @return int
     */
    public function parity(): int
    {
        return $this->parity;
    }

    /**
     * @return Matrix
     */
    public function permutationMatrix(): Matrix
    {
        $size = count($this->permutation);
        $permutationMatrix = [];

        for ($i = 0; $i < $size; $i++) {
            $permutationMatrix[] = [];

            for ($j = 0; $j < $size; $j++) {
                $permutationMatrix[$i][] = $this->permutation[$i] === $j ? 1 : 0;
            }
        }

        return new Matrix($permutationMatrix);
    }

    /**
     * @return array
     */
    public function permutationArray(): array
    {
        return $this->permutation;
    }
}

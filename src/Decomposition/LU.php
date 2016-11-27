<?php

declare(strict_types = 1);

namespace MCordingley\LinearAlgebra\Decomposition;

use MCordingley\LinearAlgebra\Matrix;
use MCordingley\LinearAlgebra\MatrixException;

final class LU
{
    /** @var Matrix */
    private $decomposition;

    /**
     * @param Matrix $matrix
     * @throws MatrixException
     */
    public function __construct(Matrix $matrix)
    {
        $this->decompose($matrix);
    }

    /**
     * @param Matrix $source
     * @throws MatrixException
     */
    private function decompose(Matrix $source)
    {
        $decompositionLiteral = $source->toArray();

        if (!$source->isSquare()) {
            throw new MatrixException('Operation can only be called on square matrix: ' . print_r($decompositionLiteral, true));
        }

        $size = $source->getRowCount();

        for ($k = 0; $k < $size; $k++) {
            for ($i = $k + 1; $i < $size; $i++) {
                $decompositionLiteral[$i][$k] = $decompositionLiteral[$i][$k] / $decompositionLiteral[$k][$k];
            }

            for ($i = $k + 1; $i < $size; $i++) {
                for ($j = $k + 1; $j < $size; $j++) {
                    $decompositionLiteral[$i][$j] = $decompositionLiteral[$i][$j] - $decompositionLiteral[$i][$k] * $decompositionLiteral[$k][$j];
                }
            }
        }

        $this->decomposition =  new Matrix($decompositionLiteral);
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
}

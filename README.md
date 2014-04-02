# Matrix

Stand-alone Linear Algebra Library for PHP

## Installation

Include this in your composer.json and then run `composer install`:

    "mcordingley/matrix": "0.1.*"

## Usage

Instantiate a new instance of the matrix class like so:

    $matrix = new \MCordingley\Matrix\Matrix([
        [0, 1, 2],
        [3, 4, 5],
        [6, 7, 8]
    ]);

With the matrix instance, you can retrieve individual elements with `get` using
the zero-based indices of the row and column that you want:

    $element = $matrix->get($row, $column);

Or change the value of an element in the matrix with `set`. The method returns
the current matrix for convenience in chaining:

    $matrix->set($row, $column, 21);

You can also add, subtract, and multiply the matrix with scalar values and other
matrices. All operations return a new Matrix and do not modify the underlying matrix:

    $addedScalar = $matrix->add(3);
    $addedMatrix = $matrix->add($anotherMatrix);
    $subtractedScalar = $matrix->subtract(2);
    $subtractedMatrix = $matrix->subtract($anotherMatrix);
    $multipliedByScalar = $matrix->multiply(4);
    $multipliedByMatrix = $matrix->multiply($anotherMatrix);

In addition to these basic operations, the Matrix class offers other common
matrix operations:

    $matrix->inverse()
    $matrix->adjoint()
    $matrix->determinant()
    $matrix->trace()
    $matrix->transpose()
    $matrix->submatrix()

It's also possible to run a map over the matrix:

    $squaredElements = $matrix->map(function($element, $row, $column, $matrix) {
        return $element * $element
    });

## Change-log

- 0.1.0
    - Created the Matrix type
    - Scalar Addition
    - Scalar Subtraction
    - Scalar Multiplication
    - Matrix Addition
    - Matrix Subtraction
    - Matrix Multiplication
    - Inverse
    - Adjoint
    - Determinant
    - Trace
    - Transpose
    - Submatrix
    - Map
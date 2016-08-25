# Matrix

[![Build Status](https://api.travis-ci.org/repositories/mcordingley/LinearAlgebra.svg)](https://travis-ci.org/mcordingley/LinearAlgebra)
[![Code Climate](https://codeclimate.com/github/mcordingley/LinearAlgebra/badges/gpa.svg)](https://codeclimate.com/github/mcordingley/LinearAlgebra)
[![Code Coverage](https://codeclimate.com/github/mcordingley/LinearAlgebra/badges/coverage.svg)](https://codeclimate.com/github/mcordingley/LinearAlgebra)

Stand-alone Linear Algebra Library for PHP

## Installation

    composer require mcordingley/LinearAlgebra

Alternately, include this in your composer.json and then update:

    "mcordingley/linearalgebra": "^1.3.0"

If Composer isn't an option for you, clone this repository and run `build-phar.php` to generate a phar
archive that you can include into your project. PHP will autoload classes from inside the archive as needed.

## Usage

Start with a `use` statement for the class:

    use mcordingley\LinearAlgebra\Matrix;

Then, instantiate a new instance of the matrix class like so:

    $matrix = new Matrix([
        [0, 1, 2],
        [3, 4, 5],
        [6, 7, 8]
    ]);

You can also generate an identity matrix with the `identity` factory function:

    $threeByThreeIdentityMatrix = Matrix::identity(3);

With the matrix instance, you can retrieve individual elements with `get` using
the zero-based indices of the row and column that you want:

    $element = $matrix->get($row, $column);

It's also possible to find out how large the matrix is with `getRowCount()` and `getColumnCount()`:

    $rows = $matrix->getRowCount();
    $columns = $matrix->getColumnCount();

You can also add, subtract, and multiply the matrix with scalar values and other
matrices. All operations return a new Matrix and do not modify the underlying matrix:

    $addedScalar = $matrix->addScalar(3);
    $addedMatrix = $matrix->addMatrix($anotherMatrix);
    $subtractedScalar = $matrix->subtractScalar(2);
    $subtractedMatrix = $matrix->subtractMatrix($anotherMatrix);
    $multipliedByScalar = $matrix->multiplyScalar(4);
    $multipliedByMatrix = $matrix->multiplyMatrix($anotherMatrix);

Matrices can be compared with `equals` to see if they're equal:

    if ($matrix1->equals($matrix2)) {
        // Equality for all!
    }

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

If you need to combine together matrices, you can do so by calling the concatenation methods:

    $m1 = new Matrix([
      [1,2,3],
      [4,5,6],
    ]);

    $m2 = new Matrix([
      [7],
      [8],
    ]);

    $m3 = new Matrix([[3,2,1]]);

    $m4 = $m1->concatenateRight($m2);
    //  [
    //      [1,2,3,7],
    //      [4,5,6,8],
    //  ]

    $m5 = $m1->concatenateBottom($m3);
    // [
    //     [1,2,3],
    //     [4,5,6],
    //     [3,2,1],
    // ]

## Change-log

- 2.0.0 (future)
    - Drop support for PHP 5.x
    - Introduce strict scalar type hints
    - Drop deprecated functions and properties.
    - Tighten up interface with the `final` and `private` keywords.

- 1.3.2
    - Deprecate `__toString()` magic method.
    - Deprecate `isSymmetric()`.

- 1.3.1
    - Deprecate use of the `ArrayAccess` interface.
    - More internal code style fixes.

- 1.3.0
    - Fix typo in names of `concatenateRight()` and `concatenateBottom()`
    - Remove generated Phar file. Users who need it should use the `build-phar.php` script to generate one.
    - Refactor LUDecomposition to have a less awkward constructor.
    - Split `add()` into `addMatrix()` and `addScalar()`. Deprecate `add()`.
    - Split `subtract()` into `subtractMatrix()` and `subtractScalar()`. Deprecate `subtract()`.
    - Split `multiply()` into `multiplyMatrix()` and `multiplyScalar()`. Deprecate `multiply()`.
    - Add `getRowCount()` and `getColumnCount()` accessors.
    - Deprecate `rows` and `columns` properties.

- 1.2.0
    - Added `concatenateBottom($other)`
    - Added `concatencateRight($other)`

- 1.1.0
    - Added `diagonal()`.

- 1.0.0
    - Switch to PSR-4 from PSR-0.
    - Take `isSymmetric()` public.
    - Rearrange source in `Matrix.php` to be more readable and PSR-compliant.

- 0.9.1
    - Fix several bugs with the Cholesky decomposition and inverse.

- 0.9.0
    - Bump version up to represent that this is close to it's final form.
    - Merged PR for faster `inverse` calculations
    - KISS `Vector` class good-bye.
    - Renamed `eq` to `equals`.
    - Removed `set` function, so instantiated objects are immutable.

- 0.3.0
    - Added the `identity` factory function
    - Using Cholesky decomposition for faster matrix inversions for applicable matrices
    - Added `eq` function to test equality of matrices
    - Implemented the ArrayAccess interface

- 0.2.0
    - Created the Vector type
    - `\MCordingley` namespace is now `\mcordingley`
    - Matrix functions that return a new Matrix now return a new instance of the called class

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

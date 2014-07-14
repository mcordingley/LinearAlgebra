<?php
namespace mcordingley\LinearAlgebra;

require_once './Benchmark.php';
require_once '../vendor/autoload.php';

// Set up the benchmark class
$benchmark = new Benchmark();
$n = 2000;
$step = 10;
$start = 2;

// Loop over matrix sizes
for($size = $start; $size <= $n; $size *= $step) {
    $random_numbers1 = array();
    $random_numbers2 = array();
    $seed = 0;
    $min = 0;
    $max = 10;
    mt_srand($seed);
    for($i = 0; $i < $size; ++$i) {
        for($j = 0; $j < $size; ++$j) {
            $random_numbers1[$i][$j] = mt_rand();
            $random_numbers2[$i][$j] = mt_rand();
        }
    }
    $m1 = new Matrix($random_numbers1);
    $m2 = new Matrix($random_numbers2);

    // add a scalar
    $key = "Add a scalar, size = $size";
    echo $key."\n";
    $benchmark->start($key);
    $x = $m1->add(25);
    $benchmark->end($key);
    
    // add a matrix
    $key = "Add a matrix, size = $size";
    echo $key."\n";
    $benchmark->start($key);
    $x = $m1->add($m2);
    $benchmark->end($key);
    
    // subtract a scalar
    $key = "Subtract a scalar, size = $size";
    echo $key."\n";
    $benchmark->start($key);
    $x = $m1->subtract(25);
    $benchmark->end($key);

    // subtract a matrix
    $key = "Subtract a matrix, size = $size";
    echo $key."\n";
    $benchmark->start($key);
    $x = $m1->subtract($m2);
    $benchmark->end($key);
    
    // multiply by scalar
    $key = "Multiply a scalar, size = $size";
    echo $key."\n";
    $benchmark->start($key);
    $x = $m1->multiply(25);
    $benchmark->end($key);
/**    
    // multiply by matrix
    $key = "Multiply a matrix, size = $size";
    echo $key."\n";
    $benchmark->start($key);
    $x = $m1->multiply($m2);
    $benchmark->end($key);
**/
    // trace
    $key = "Trace, size = $size";
    echo $key."\n";
    $benchmark->start($key);
    $x = $m1->trace();
    $benchmark->end($key);
    
    // transpose
    $key = "Transpose, size = $size";
    echo $key."\n";
    $benchmark->start($key);
    $x = $m1->transpose();
    $benchmark->end($key);
    
    // adjoint
    /**
    $key = "Adjoint, size = $size";
    echo $key."\n";
    $benchmark->start($key);
    $x = $m1->adjoint();
    $benchmark->end($key);
    **/
    // determinant
    $key = "Determinant, size = $size";
    echo $key."\n";
    $benchmark->start($key);
    $x = $m1->determinant();
    $benchmark->end($key);
    
    // inverse
    $key = "Inverse, size = $size";
    echo $key."\n";
    $benchmark->start($key);
    $x = $m1->inverse();
    $benchmark->end($key);
    
    echo "\n\n";
    $benchmark->printReport();
    $benchmark->clear();
    echo "\n\n";
}

<?php
namespace mcordingley\LinearAlgebra;

require_once './Benchmark.php';
require_once '../vendor/autoload.php';

class BenchmarkTest {
    
    protected $benchmark;
    protected $results;
    
    private $m1, $m2, $random_numbers1, $random_numbers2;
    
    public function __construct() {
        // Set up the benchmark class
        $this->benchmark = new Benchmark();
    }
    
    /**
     * Calls $callback($m1, $m2) a number of times equal to $repeat, where m1 and m2 are 
     * matrices populated with random numbers. Saves the total average time in results 
     * array under $name. Resets matrices if $reset == TRUE (defaults to true).
     * 
     * @param string $name
     * @param int $repeat
     * @param callable $callback
     */
    public function test($name, $repeat, callable $callback, $reset = TRUE) {
        for($i = 1; $i <= $repeat; ++$i) {
            if($reset) $this->resetMatrices();
        	$this->benchmark->start($name);
        	$callback($this->m1, $this->m2);
        	$this->benchmark->end($name);
        }
        $this->results[$name] = $this->benchmark->getTime($name) / $repeat;
    }
    
    /**
     * Unsets and re-initialzies matrices to eliminate caching optimizations
     * from the tests
     */
    public function resetMatrices() {
        unset($this->m1);
        unset($this->m2);
        $this->m1 = new Matrix($this->random_numbers1);
        $this->m2 = new Matrix($this->random_numbers2);
    }
    
    /**
     * Generates two square matrices of size $size filled with random numbers
     * @param int $size
     */
    public function initializeMatrices($size) {
        $this->random_numbers1 = array();
        $this->random_numbers2 = array();
        $seed = 0;
        $min = 0;
        $max = 1;
        mt_srand($seed);
        for($i = 0; $i < $size; ++$i) {
        	for($j = 0; $j < $size; ++$j) {
        		$this->random_numbers1[$i][$j] = mt_rand();
        		$this->random_numbers2[$i][$j] = mt_rand();
        	}
        }
        $this->m1 = new Matrix($this->random_numbers1);
        $this->m2 = new Matrix($this->random_numbers2);
    }
    
    /**
     * Returns results array
     * 
     * @return array
     */
    public function getResults() {
        return $this->results;
    }
}

$test = new BenchmarkTest();

$config = array(
		5 => 10,
		10 => 5,
);
// Loop over matrix sizes
foreach($config as $size => $repeat) {

    $test->initializeMatrices($size);
    
    // add a scalar
    $key = "$size, Add a scalar";
    $test->test($key, $repeat, function($m1, $m2){
        $m1->add(25);
    });

    // add a matrix
    $key = "$size, Add a matrix";
    $test->test($key, $repeat, function($m1, $m2){
        $m1->add($m2);
    });

    // subtract a scalar
    $key = "$size, Subtract a scalar";
    $test->test($key, $repeat, function($m1, $m2){
        $m1->subtract(25);
    });

    // subtract a matrix
    $key = "$size, Subtract a matrix";
    $test->test($key, $repeat, function($m1, $m2){
        $m1->subtract($m2);
    });

    // multiply by scalar
    $key = "$size, Multiply a scalar";
    $test->test($key, $repeat, function($m1, $m2){
        $m1->multiply(25);
    });
        
    // multiply by matrix
    $key = "$size, Multiply a matrix";
    $test->test($key, $repeat, function($m1, $m2){
        $m1->multiply($m2);
    });

    // trace
    $key = "$size, Trace";
    $test->test($key, $repeat, function($m1, $m2){
    	$m1->trace(25);
    });

    // transpose
    $key = "$size, Transpose";
    $test->test($key, $repeat, function($m1, $m2){
        $m1->transpose();
    });

    // adjoint
    $key = "$size, Adjoint";
    $test->test($key, $repeat, function($m1, $m2){
        $m1->adjoint();
    });

    // determinant
    $key = "$size, Determinant";
    $test->test($key, $repeat, function($m1, $m2){
        $m1->determinant();
    });

    // inverse
    $key = "$size, Inverse";
    $test->test($key, $repeat, function($m1, $m2){
        $m1->inverse();
    });
}
$results = $test->getResults();

// Write report
foreach($results as $key => $time)
{
    echo $key.', '.$time."\n";
}
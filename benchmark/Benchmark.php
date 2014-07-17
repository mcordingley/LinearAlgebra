<?php
namespace mcordingley\LinearAlgebra;

class Benchmark {
    protected $benchmark_times = array();
    
    public function start($key) {
        $this->benchmark_times[$key]['start'] = microtime(TRUE);
        $this->benchmark_times[$key]['end'] = 0;
        if(! isset($this->benchmark_times[$key]['total'])) {
           $this->benchmark_times[$key]['total'] = 0;
        }
    }
    
    public function end($key) {
        $end = microtime(TRUE);
        $this->benchmark_times[$key][$end] = $end;
        $total = $end - $this->benchmark_times[$key]['start'];
        $this->benchmark_times[$key]['total'] += $total;
    }
    
    public function clear($key = FALSE) {
        if( ! $key) {
            $this->benchmark_times = array();
        } else {
            unset($this->benchmark_times[$key]);
        }
    }
    
    public function getTime($key) {
        return $this->benchmark_times[$key]['total'];
    }
}
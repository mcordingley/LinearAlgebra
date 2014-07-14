<?php
namespace mcordingley\LinearAlgebra;

class Benchmark {
    protected $benchmark_times = array();
    
    public function start($key) {
        $this->benchmark_times[$key]['start'] = microtime(TRUE);
        $this->benchmark_times[$key]['end'] = 0;
        $this->benchmark_times[$key]['total'] = 0;
    }
    
    public function end($key) {
        $end = microtime(TRUE);
        $this->benchmark_times[$key][$end] = $end;
        $total = $end - $this->benchmark_times[$key]['start'];
        $this->benchmark_times[$key]['total'] += $total;
    }
    
    public function printReport() {
        echo "Benchmark Report:\n";
        echo "-----------------\n";
        echo "\n";
        foreach($this->benchmark_times as $key => $time)
        {
            echo $key.': '.$time['total'].' s'."\n";
        }
    }
    
    public function clear() {
        $this->benchmark_times = array();
    }
}
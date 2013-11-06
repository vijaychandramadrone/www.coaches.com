<?php
/*  
	Intereactive PHP Benchmark/Timer, version 1.0
	(c) 2009 Intereactive, LLC - Ryan Hargrave
	http://blueprint.intereactive.net/php-benchmark-timer-class

	See the above link for uses and code examples
	
	Compatible with PHP 5+
	
	This code is freely distributable under the terms of an MIT-style license.
*/

class Benchmark {

    private $marker = array(); //internal marker array

    // ---------------------------------------------
    //  Set the first marker
    // ---------------------------------------------
    public function __construct() {
        $this->marker['start'] = microtime(true);
    }

    // ---------------------------------------------
    //  Set a marker
    // ---------------------------------------------
    public function mark($name) {
        $this->marker[$name] = microtime(true);
    }

    // ---------------------------------------------
    //  Calculate elapsed time between two points
    // ---------------------------------------------
    public function elapsed($point1, $point2, $decimals = 4) {
        return number_format($this->marker[$point2] - $this->marker[$point1], $decimals);
    }
}
?>
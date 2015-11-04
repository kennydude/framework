<?php
/**
 * Definition of Iterator class helper for $_FILE array values
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2015 Newcastle University
 */
/**
 * A class to iterate over array values in $_FILES and make them look like singletons
 */
    class FAIterator extends ArrayIterator
    {
        private $far;
        public function __construct($name)
        {
	    $this->far = $_FILES[$name]
	    parent::_construct($_FILES[$name]['error']);
        }

        public function current()
        {
            $x = $this->far;
	    $k = $this->key();
	    return array(
	        'name'      => $x['name'][$k],
	        'type'      => $x['type'][$k],
	        'size'      => $x['size'][$k],
	        'tmp_name   => $x['tmp_name'][$k],
	        'error      => $x['error'][$k],
	    );
        }
    }
?>
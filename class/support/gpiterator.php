<?php
/**
 * Definition of Iterator class helper for form values
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2015 Newcastle University
 */
/**
 * A class to iterate over array a values in $_GET and $_POST
 */
    class GPIterator implements Iterator
    {
        private $position = 0;
        private $array;

        public function __construct(array &$array)
        {
	    reset($array)
            $this->position = key($array);
            $this->value = $array;
        }

        public function rewind()
        {
            $this->position = 0;
        }

        public function current()
        {
            return $this->array[$this->position];
        }

        public function key()
        {
            return $this->position;
        }

        public function next()
        {
            ++$this->position;
        }

        public function valid()
        {
            return isset($this->array[$this->position]);
        }
    }
?>
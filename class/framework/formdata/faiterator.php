<?php
/**
 * Definition of Iterator class helper for $_FILE array values
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2015-2021 Newcastle University
 * @package Framework
 * @subpackage FormData
 */
    namespace Framework\FormData;

/**
 * A class to iterate over array values in $_FILES and make them look like singletons
 */
    class FAIterator extends \ArrayIterator
    {
/**
 * @var array   The base entry in the $_FILES array.
 */
        private array $far;
/**
 * The constructor
 *
 * @param string  $name The name of the entry in the $_FILES array
 *
 * throws \Framework\Exception\InternalError
 */
        public function __construct(string $name)
        {
            if (!isset($_FILES[$name]) || !is_array($_FILES[$name]['error']))
            {
                throw new \Framework\Exception\BadValue('Not an array of files');
            }
            $this->far = $_FILES[$name];
/*
 *  The 'error' sub-array is just used an iteration control and any of the field arrays could be used.
 */
            parent::__construct($_FILES[$name]['error']);
        }
/**
 * Returns the value of the current element of the "array"
 *
 * This returns an array that looks like what you would get if the file  was a
 * singleton and not an array. This can simplify coding in certain circumstances even
 * though it involves restructuring the data and hence is slightly inefficient. Basically
 * the $_FILES array should be made from objects with fields not arrays of keyed values!!!
 */
        public function current() : array
        {
            $x = $this->far;
            $k = $this->key();
            return [
                'name'      => $x['name'][$k],
                'type'      => $x['type'][$k],
                'size'      => $x['size'][$k],
                'tmp_name'  => $x['tmp_name'][$k],
                'error'     => $x['error'][$k],
            ];
        }
    }
?>

<?php
/**
 * Contains the definition of Formdata PUT or PATCH support class
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2020 Newcastle University
 * @package Framework
 * @subpackage FormData
 */
    namespace Framework\FormData;

/**
 * A class that provides helpers for accessing PUT OR PATCH form data
 */
    class File extends Base
    {
/**
 * Constructor
 */
        public function __construct()
        {
            parent::__construct(NULL);
            /** @psalm-suppress NullArgument */
            $this->super = $_FILES;
        }
/**
 * Make arrays of files work more like singletons
 *
 * @param mixed    $name
 * @param mixed    $key
 *
 * @throws BadValue
 * @return array
 */
        public function fileData($name, $key = '') : array
        {
            $x = $this->getValue($name, NULL, TRUE, TRUE)[1]; // will not return if it does not
            if ($key === '')
            {
                return $x;
            }
            if (!isset($x['name'][$key]))
            {
                throw new \Framework\Exception\BadValue('Missing _FILES element');
            }
            return [
                'name'     => $x['name'][$key],
                'type'     => $x['type'][$key],
                'size'     => $x['size'][$key],
                'tmp_name' => $x['tmp_name'][$key],
                'error'    => $x['error'][$key],
            ];
        }
/**
 * Make arrays of files work more like singletons
 *
 * @param string    $name
 *
 * @return \ArrayIterator
 */
        public function fileArray(string $name, array $dflt = []) : \ArrayIterator
        {
            return isset($_FILES[$name]) && is_array($_FILES[$name]['error']) ? new FAIterator($name) : new \ArrayIterator($dflt);
        }
    }
?>

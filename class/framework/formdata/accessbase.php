<?php
/**
 * Contains the definition of the Formdata Base class
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2020 Newcastle University
 */
    namespace Framework\FormData;

    use \Framework\Exception\BadValue;
    use \Support\Context;
/**
 * A class that provides helpers for accessing form data
 */
    class AccessBase extends Base
    {
        public function __construct(?int $which)
        {
            parent::__construct($which);
        }
/*
 *******************************************************************
 * Existence checking function
 *******************************************************************
 */
/**
 * Is the key in the array?
 *
 * @param mixed    $name   The keys
 *
 * @return bool
 */
        public function exists($name) : bool
        {
            return $this->getValue($name, NULL, FALSE)[0];
        }
/**
 * Is the key in the $_GET array?
 *
 * @param mixed    $name   The keys
 *
 * @throws BadValue
 * @return bool
 */
        public function mustExist($name) : bool
        {
            return $this->getValue($name, NULL, TRUE)[0];
        }
/*
 ***************************************
 * Fetching methods
 ***************************************
 */
/**
 * Look in the array for a key and return its trimmed value
 *
 * N.B. This function assumes the value is a string and will fail if used on array values
 *
 * @param mixed $name   The key or if it is an array then the key and the fields that are needed $_GET['xyz'][0]
 *
 * @return mixed
 */
        public function mustFetch($name, $filter = NULL, $options = '')
        {
            return $this->getValue($name, NULL, TRUE, FALSE, $filter, $options)[1];
        }
/**
 * Look in the array for a key and return its trimmed value or a default value
 *
 * N.B. This function assumes the value is a string and will fail if used on array values
 *
 * @param mixed $name   The key or if it is an array then the key and the fields that are needed XXX['xyz'][0]
 * @param mixed $dflt   Returned if the key does not exist
 *
 * @return mixed
 */
        public function fetch($name, $default = '', $filter = NULL, $options = '')
        {
            return $this->getValue($name, $default, FALSE, FALSE, $filter, $options)[1];
        }
/**
 * Look in the array for a key that is an id for a bean
 *
 * N.B. This function assumes the value is a string and will fail if used on array values
 *
 * @param mixed    $name        The key or if it is an array then the key and the fields that are needed XXX['xyz'][0]
 * @param string   $bean        The bean type
 * @param bool     $forupdate   If TRUE then load for update
 *
 * @return \RedBeanPHP\OODBBean
 */
        public function mustFetchBean($name, $bean, $forupdate = FALSE) : \RedBeanPHP\OODBBean
        {
            return Context::getinstance()->load($bean, $this->getValue($name, NULL, TRUE, FALSE, FILTER_VALIDATE_INT)[1], $forupdate);
        }
/**
 * Look in the array for a key that is an array and return an ArrayIterator over it
 *
 * @param mixed    $name    The key or if it is an array then the key and the fields that are needed XXX['xyz'][0]
 *
 * @throws BadValue
 * @return \ArrayIterator
 */
        public function mustFetchArray($name) : \ArrayIterator
        {
            return new \ArrayIterator($this->getValue($name, NULL, TRUE, TRUE)[1]);
        }
/**
 * Look in the array for a key that is an array and return an ArrayIterator over it
 *
 * @param mixed   $name    The key
 * @param array    $dflt    Returned if the key does not exist
 *
 * @return \ArrayIterator
 */
        public function fetchArray($name, array $default = []) : \ArrayIterator
        {
            return new \ArrayIterator($this->getValue($name, $default, FALSE, TRUE)[1]);
        }
    }
?>
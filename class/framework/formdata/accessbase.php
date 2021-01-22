<?php
/**
 * Contains the definition of the Formdata Base class
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2020 Newcastle University
 * @package Framework
 * @subpackage FormData
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
 ***************************************
 * Fetching methods
 ***************************************
 */
/**
 * Look in the array for a key and return its trimmed value
 *
 * N.B. This function assumes the value is a string and will fail if used on array values
 *
 * @param string|array  $name     The key or if it is an array then the key and the fields that are needed $_GET['xyz'][0]
 * @param ?int          $filter   Filter to apply
 * @param array|int     $options  Filter options
 * @param bool          $isArray  Expect an array ratherthan a simple value
 *
 * @return mixed
 */
        public function mustFetch($name, $filter = NULL, $options = [], bool $isArray = FALSE)
        {
            return $this->getValue($name, NULL, TRUE, $isArray, $filter, $options)[1];
        }
/**
 * Look in the array for a key and return its trimmed value or a default value
 *
 * N.B. This function assumes the value is a string and will fail if used on array values
 *
 * @param string|array  $name     The key or if it is an array then the key and the fields that are needed XXX['xyz'][0]
 * @param mixed         $default  Returned if the key does not exist
 * @param ?int          $filter   Filter to apply
 * @param mixed         $options  Filter options
 * @param bool          $isArray  If TRUE then expect an array rather than a simple value
 *
 * @return mixed
 */
        public function fetch($name, $default = '', $filter = NULL, $options = [], bool $isArray = FALSE)
        {
            return $this->getValue($name, $default, FALSE, $isArray, $filter, $options)[1];
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
            return Context::getinstance()->load($bean, $this->getValue($name, NULL, TRUE, FALSE, FILTER_VALIDATE_INT, FALSE)[1], $forupdate);
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
 * @param mixed[] $dflt    Returned if the key does not exist
 *
 * @return \ArrayIterator
 */
        public function fetchArray($name, array $default = []) : \ArrayIterator
        {
            return new \ArrayIterator($this->getValue($name, $default, FALSE, TRUE)[1]);
        }
/**
 * Return an ArrayIterator over all the values in the form
 *
 * @return \ArrayIterator
 */
        public function fetchAll() : \ArrayIterator
        {
            return new \ArrayIterator($this->getSuper($this->which));
        }
/**
 * Return the array of values
 *
 * @return array
 */
        public function fetchRaw() : array
        {
            return $this->getSuper($this->which);
        }
    }
?>

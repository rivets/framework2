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
        public function __construct(int $which)
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
 * @param string    $name   The key
 *
 * @return bool
 */
        public function has(string $name) : bool
        {
            return $this->exists($name, FALSE);
        }
/**
 * Is the key in the $_GET array?
 *
 * @param string    $name   The key
 *
 * @throws BadValue
 * @return bool
 */
        public function mustHave(string $name) : bool
        {
            return $this->exists($name, TRUE);
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
        public function mustGet($name)
        {
            return $this->fetchValue($name, TRUE);
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
        public function get($name, $dflt = '')
        {
            return $this->fetchValue($name, FALSE, $dflt);
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
        public function mustGetBean($name, $bean, $forupdate = FALSE) : \RedBeanPHP\OODBBean
        {
            return Context::getinstance()->load($bean, $this->fetchValue($name, TRUE, NULL), $forupdate);
        }
/**
 * Look in the array for a key that is an array and return an ArrayIterator over it
 *
 * @param string    $name    The key or if it is an array then the key and the fields that are needed XXX['xyz'][0]
 *
 * @throws BadValue
 * @return \ArrayIterator
 */
        public function mustGetArray($name) : \ArrayIterator
        {
            $this->exists($name, TRUE, TRUE);
            return new \ArrayIterator($_GET[$name]);
        }
/**
 * Look in the $_GET array for a key that is an array and return an ArrayIterator over it
 *
 * @param string   $name    The key
 * @param array    $dflt    Returned if the key does not exist
 *
 * @return \ArrayIterator
 */
        public function getArray(string $name, array $dflt = []) : \ArrayIterator
        {
            return new \ArrayIterator($this->exists($name, FALSE, TRUE) ? $this->super[$name] : $dflt);
        }
/**
 * Look in the array for a key and apply filters
 *
 * @param string   $name    The key
 * @param mixed    $default A default value
 * @param int      $filter  Filter values - see PHP manual
 * @param mixed    $options see PHP manual
 *
 * @return mixed
 */
        public function getFiltered(string $name, $default, int $filter, $options = '')
        {
            return $this->filter($name, $default, $filter, $options);
        }
/**
 * Look in the $_GET array for a key and apply filters
 *
 * @param string    $name       The key
 * @param int       $filter     Filter values - see PHP manual
 * @param mixed     $options    see PHP manual
 *
 * @return mixed
 */
        public function mustGetFiltered(string $name, int $filter, $options = '')
        {
            return $this->mustfilter($name, $filter, $options);
        }
    }
?>
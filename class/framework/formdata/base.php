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
/**
 * A class that provides helpers for accessing form data
 */
    class Base
    {
/**
 * @var int Indicates which Superglobal we are using
 */
        private $which;
/**
 * @var array The array that contains the relevant values.
 *            It is protected rather than private as some items do not have Superglobals and set this value to an array;
 */
        protected $super;
/**
 * Constructor
 *
 * @param ?int  $which  The appropriate INPUT_ filter or NULL (which will be for PUT/PATCH...)
 */
        public function __construct(?int $which)
        {
            $this->which = $which;
            if ($this->which !== NULL)
            {
                $this->super = $this->getSuper($this->which);
            }
        }
/*
 *******************************************************************
 * Utility functions
 *******************************************************************
 */
/**
 *  Return the relevant Superglobal
 *
 *  @TODO Change this to use match() when PHP 8 is released
 *
 *  @param int  $which
 *
 *  @return array
 */
        protected function getSuper(?int $which = NULL) : array
        {
            switch($which ?? $this->which)
            {
            case INPUT_GET:     return $_GET;
            case INPUT_POST:    return $_POST;
            case INPUT_COOKIE:  return $_COOKIE;
            case INPUT_SERVER:  return $_SERVER;
            case INPUT_ENV:     return $_ENV;
            case NULL:          return $this->super;
            }
            throw new BadValue('Invalid Superglobal constant');
        }
/**
 * Look in the specified array for a key and see if it exists
 *
 * @internal
 *
 * @param string    $name       The key
 * @param mixed     $default    The default value if needed
 * @param bool      $throw      If TRUE then throw an execption if it does not exist
 * @param bool      $isArray    If TRUE then check that this is an array
 * @param ?int      $filter     Filter to apply or NULL
 * @param mixed     $options    Filter options
 * @param bool      $allowArray If TRUE then allow return of array elements rather than just a value
 *
 * @return array
 */
        final public function getValue($name, $default = NULL, bool $throw = TRUE, bool $isArray = FALSE, ?int $filter = NULL, $options = []) : array
        {
            try
            {
                $dt = $this->fetchFrom(is_array($name) ? $name : [$name], $default, TRUE, $filter, $options);
            }
            catch (BadValue $e)
            { // does not exist
                if ($throw)
                {
                    throw $e;
                }
                return [FALSE, $default];
            }
            if (is_array($dt))
            {
                if (!$isArray)
                {
                    if ($throw)
                    {
                        throw new BadValue('Form Item '.$name.' is an array');
                    }
                    return [FALSE, $default];
                }
            }
            elseif ($isArray)
            {
                if ($throw)
                {
                    throw new BadValue('Form Item '.$name.' is not an array');
                }
                return [FALSE, $default];
            }
            return [TRUE, $dt];
        }
/**
 * Utility function to dig out an element from a possibly multi-dimensional array
 *
 * It would be nice to do this using array_reduce but that cannot work with Superglobals....
 *
 * @internal
 *
 * @param string[]  $keys       An array of keys
 *
 * @return mixed;
 * @throws BadValue
 */
        private function find(array $keys)
        {
            $part = $this->super;
            $etrack = [];
            while (TRUE) // iterate over the array of keys
            {
                $key = array_shift($keys);
                $etrack[] = $key;
                if (!isset($part[$key]))
                { // missing item so fail or return default
                    throw new BadValue('Missing form item: '.implode('/', $etrack));
                }
                $part = $part[$key];
                if (empty($keys))
                {
                    break 1;
                }
            }
            return $part;
        }
/**
 * Utility function to fetch an element from a possibly multi-dimensional array
 *
 * @internal
 *
 * @param string[]  $keys       An array of keys
 * @param mixed     $default    A value to return if the item is missing and we are not failing
 * @param bool      $throw      If TRUE Then throw an exception rather than returning the default
 * @param ?int      $filter     A PHP filter
 * @param mixed     $options    Filter options
 *
 * @throws BadValue
 * @return mixed
 */
        private function fetchFrom(array $keys, $default = NULL, bool $throw = FALSE, ?int $filter = NULL, $options = [])
        {
            try
            {
                $part = $this->find($keys);
            }
            catch (BadValue $e)
            { // not there
                if ($throw)
                {
                    throw $e;
                }
                return $default;
            }
            if (!is_array($part))
            { // don't try and trim an array!
                $part = trim($part);
                if (!empty($filter))
                { // need to apply a filter to the value
                    $part = filter_var($part, $filter, $options);
                    if ($part === FALSE || $part === NULL)
                    { // it failed
                        if ($throw)
                        {
                            throw new BadValue('Filter failure '.$filter);
                        }
                        return $default;
                    }
                    $part = trim($part);
                }
            }
            return $part;
        }
/*
 *******************************************************************
 * Existence checking functions
 *******************************************************************
 */
/**
 * Is the key in the array?
 *
 * @TODO Fix the need for two calls. They are there because getval does an array check which is
 *       not relevant for an existence check. We need a three valued boolean :-)
 *
 * @param mixed    $name   The keys
 *
 * @return bool
 */
        public function exists($name) : bool
        {
            try
            {
                $this->find(is_array($name) ? $name : [$name]);
            }
            catch (BadValue $e)
            { // not there
                return FALSE;
            }
            return TRUE;
        }
/**
 * Is the key in the array?
 *
 * @TODO Fix the need for two calls. They are there because getval does an array check which is
 *       not relevant for an existence check. We need a three valued boolean :-)
 *
 * @param mixed    $name   The keys
 *
 * @throws BadValue
 * @return bool
 */
        public function mustExist($name) : bool
        {
            $this->find(is_array($name) ? $name : [$name]);
            return TRUE;
        }
    }
?>
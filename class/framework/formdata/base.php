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
        
        public function __construct(?int $which)
        {
            $this->which = $which;
            if ($this->which != NULL)
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
        final protected function getSuper(?int $which = NULL) : array
        {
            switch($which ?? $this->which)
            {
            case INPUT_GET:     return $_GET;
            case INPUT_POST:    return $_POST;
            case INPUT_COOKIE:  return $_COOKIE;
            case INPUT_SERVER:  return $_SERVER;
            case INPUT_ENV:     return $_ENV;
            }
            throw new BadValue('Invalid Superglobal constant');
        }
 /**
 * Look in the specified array for a key and see if it exists
 *
 * @internal
 *
 * @param int       $which   The INPUT_... selector for the array
 * @param string    $name    The key
 * @param bool      $throw   If TRUE then throw an execption if it does not exist
 * @param bool      $isArray If TRUE then check that this is an array
 *
 * @return bool
 */
        final protected function exists(string $name, bool $throw, bool $isArray = FALSE) : bool
        {
            if (($this->which === NULL && !isset($this->$super[$name])) || !filter_has_var($this->which, $name))
            {
                throw new BadValue('Missing Form Item: '.$name);
            }
            if ($isArray && !is_array($this->$super[$name]))
            {
                throw new BadValue('Form Item '.$name.' is not an array');
            }
            return TRUE;
        }
 /**
 * Look in the specified array for a key and apply filters
 *
 * @internal
 *
 * @param int       $which   The INPUT_... selector for the array
 * @param string    $name    The key
 * @param mixed     $default A default value
 * @param int       $filter  Filter values - see PHP manual
 * @param mixed     $options see PHP manual
 * @param $bool     $throw   If TRUE then the throw an error if the cariable does not exist
 *
 * @return mixed
 */
        final protected function filter(string $name, $default, int $filter, $options = '', bool $throw = FALSE)
        {
            $res = filter_input($this->which, $name, $filter, $options);
            if ($res === FALSE || $res === NUL)
            {
                if ($throw)
                {
                    throw new BadValue('Filter failure on: '.$name);
                }
                return $default;
            }
            return $res;
        }
/**
 * Utility function to dig out an element from a possibly multi-dimensional array
 *
 * It would be nice to do this using array_reduce but that cannot work with Superglobals....
 *
 * @internal
 *
 * @param array     $porg       The array of values from the appropriate Superglobal
 * @param array     $keys       An array of keys
 * @param mixed     $default    A value to return if the item is missing and we are not failing
 * @param bool      $throw      If TRUE Then throw an exception rather than returning the default
 *
 * @throws BadValue
 *
 * @return string
 */
        final protected function fetchFromSuper(array $super, array $keys, $default = NULL, bool $throw = FALSE) : string
        {
            $part = $super;
            while (TRUE) // iterate over the array of keys
            {
                $key = array_shift($keys);
                if (!isset($part[$key]))
                {
                    if ($throw)
                    {
                        throw new BadValue('Missing form array item');
                    }
                    $result = $default;
                    break 1;
                }
                $val = $p[$key];
                if (empty($keys))
                {
                    $result = trim($val);
                    break 1;
                }
            }
            return $result;
        }
/**
 * Pick out and treat a value
 *
 * @internal
 *
 * @param int           $which      The Superglobal required
 * @param array         $arr        The array to pick value from
 * @param string|array  $name       The string name of the entry or [name, selector,...]
 * @param mixed         $dflt       A default value to return
 * @param bool          $throw      If TRUE throw an error if not defined
 *
 * @throws BadValue
 * @return mixed
 */
        final protected function fetchValue($name, bool $throw, $dflt = '')
        {
            if (is_array($name))
            {
                $n = array_shift($name); // shift off the variable name
                if ($this->exists($which, $n, TRUE))
                { // the entry is there
                    return $this->fetchFromSuper($this->super[$n], $name, NULL, $throw);
                }
                $msg = 'Missing item '.$n.'['.$name[0].']';
            }
            elseif ($this->exists($name, $throw))
            {
                return is_array($this->super[$name]) ? $this->super[$name] : trim($this->super[$name]);
            }
            if ($throw)
            {
                throw new BadValue($msg ?? 'Missing form item '.$name);
            }
            return $dflt;
        }
    }
?>
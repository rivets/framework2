<?php
/**
 * Contains the definition of the Formdata Base class
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2020 Newcastle University
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
        private function getSuper(?int $which = NULL) : array
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
 * @mixed string    $name    The key
 * @param bool      $throw   If TRUE then throw an execption if it does not exist
 * @param bool      $isArray If TRUE then check that this is an array
 *
 * @return booarrayl
 */
        final public function fetch($name, $default = NULL, bool $throw, bool $isArray = FALSE, $filter = NULL, $options = '') : array
        {
            try
            {
                $dt = $this->fetchFrom(is_array($name) ? $name : [$name], $default, TRUE, $filter, $options);
            }
            catch (BadValue $e)
            { # does not exist
                if ($throw)
                {
                    throw $e;
                }
                return [FALSE, $default];
            }
            if ($isArray && !is_array($dt))
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
 * @param array     $porg       The array of values from the appropriate Superglobal
 * @param array     $keys       An array of keys
 * @param mixed     $default    A value to return if the item is missing and we are not failing
 * @param bool      $throw      If TRUE Then throw an exception rather than returning the default
 *
 * @throws BadValue
 *
 * @return string
 */
        private function fetchFrom(array $keys, $default = NULL, bool $throw = FALSE, ?int $filter = NULL, $options = '') : string
        {
            $part = $this->super;
            $etrack = [];
            while (TRUE) // iterate over the array of keys
            {
                $key = array_shift($keys);
                $etrack[] = $key;
                if (!isset($part[$key]))
                {
                    if ($throw)
                    {
                        throw new BadValue('Missing form item: '.implode('/', $etrack));
                    }
                    $val = $default;
                    break 1;
                }
                $part = $part[$key];
                if (empty($keys))
                {
                    if (!is_array($part))
                    {
                        $part = trim($part);
                        if ($filter != NULL)
                        {
                            $part = filter_var($part, $filter, $options);
                            if ($part === FALSE || $part === NULL)
                            {
                                if ($throw)
                                {
                                    throw new BadValue('Filter failure');
                                }
                                return $default;
                            }
                            $part = trim($part);
                        }
                    }
                    break 1;
                }
            }
            return $part;
        }
    }
?>
<?php
/**
 * Contains the definition of the Formdata class
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2015-2019 Newcastle University
 */
    namespace Framework;

    use Framework\Web\Web as Web;
    use \Support\Context as Context;
/**
 * A class that provides helpers for accessing form data
 */
    class FormData
    {
        use \Framework\Utility\Singleton;
/**
 * @var ?array    Holds data read from php://input
 */
        private $putdata    = NULL;
/**
 * Fetch the input data if required
 *
 * @return void
 */
        private function setput() : void
        {
            if (!is_array($this->putdata))
            { /** @psalm-suppress NullArgument */
                parse_str(file_get_contents('php://input'), $this->putdata);
            }
        }
/*
 *******************************************************************
 * Existence checking functions for $_GET, $_POST, $_COOKIE and $_FILES
 *******************************************************************
 */
/**
 * Is the key in the $_GET array?
 *
 * @param string	$name	The key
 *
 * @return bool
 */
        public function hasget(string $name) : bool
        {
            return filter_has_var(INPUT_GET, $name);
        }
/**
 * Is the key in the $_POST array?
 *
 * @param string	$name	The key
 *
 * @return bool
 */
        public function haspost(string $name) : bool
        {
            return filter_has_var(INPUT_POST, $name);
        }
/**
 * Is the key in the $_COOKIE array?
 *
 * @param string	$name	The key
 *
 * @return bool
 */
        public function hascookie(string $name) : bool
        {
            return filter_has_var(INPUT_COOKIE, $name);
        }
/**
 * Is the key in the $_FILES array?
 *
 * Note: no support for FILES in the filter_has_var function
 *
 * @param string	$name	The key
 *
 * @return bool
 */
        public function hasfile(string $name) : bool
        {
            return isset($_FILES[$name]);
        }
/**
 * Is the key in the PUT/PATCH data
 *
 * @param string	$name	The key
 *
 * @return bool
 */
        public function hasput($name) : bool
        {
            $this->setput();
            return isset($this->putdata[$name]);
        }
/**
 * Utility function to dig out an element from a possibly multi-dimensional array
 *
 * @internal
 * @see \Framework\Context for failure action constants
 *
 * @param array     $porg       The array
 * @param array     $keys       An array of keys
 * @param mixed     $default    A value to return if the item is missing and we are not failing
 * @param bool   $throw      If TRUE Then throw an exception
 *
 * @throws \Framework\Exception\BadValue
 *
 * @return string
 */
        private function getval(array $porg, array $keys, $default = NULL, bool $throw = FALSE) : string
        {
            while (TRUE) // iterate over the array of keys
            {
                $key = array_shift($keys);
                if (!isset($porg[$key]))
                {
                    if ($throw)
                    {
                        throw new \Framework\Exception\BadValue('Missing form array item');
                    }
                    return $default;
                }
                $val = $porg[$key];
                if (empty($keys))
                {
                    return trim($val);
                }
            }
            /* NOT REACHED */
            return ''; // to shut psalm up!!
        }
/**
 * Pick out and treat a value
 *
 * @internal
 *
 * @param int       $filter     The flag used for the filter test
 * @param array     $arr        The array to pick value from
 * @param mixed     $name       The string name of the entry or [name, selector,...]
 * @param mixed     $dflt       A default value to return
 * @param bool      $throw      What to do if not defined - constant defined in Context
 *
 * @throws \Framework\Exception\BadValue
 * @return mixed
 */
        private function fetchit(int $filter, array $arr, $name, $dflt = '', bool $throw = TRUE)
        {
            if (is_array($name))
            {
                $n = array_shift($name); // shift off the variable name
                if (filter_has_var($filter, $n))
                { // the entry is there
                    return $this->getval($arr[$n], $name, NULL, $throw);
                }
                if ($throw)
                {
                    throw new \Framework\Exception\BadValue('Missing item '.$n.'['.$name[0].']');
                }
            }
            elseif (filter_has_var($filter, $name))
            {
                if (!is_array($arr[$name]))
                {
                    return trim($arr[$name]);
                }
                if ($throw)
                {
                    throw new \Framework\Exception\BadValue($name.' is array');
                }
            }
            elseif ($throw)
            {
                throw new \Framework\Exception\BadValue('Missing item '.$name);
            }
            return $dflt;
        }
/*
 ***************************************
 * $_GET fetching methods
 ***************************************
 */
/**
 * Look in the $_GET array for a key and return its trimmed value
 *
 * N.B. This function assumes the value is a string and will fail if used on array values
 *
 * @param mixed 	$name	The key or if it is an array then the key and the fields that are needed $_GET['xyz'][0]
 *
 * @throws \Framework\Exception\BadValue
 * @return mixed
 */
        public function mustget($name)
        {
            return $this->fetchit(INPUT_GET, $_GET, $name, NULL, TRUE);
        }
/**
 * Look in the $_GET array for a key and return its trimmed value or a default value
 *
 * N.B. This function assumes the value is a string and will fail if used on array values
 *
 * @param mixed	    $name	The key or if it is an array then the key and the fields that are needed $_GET['xyz'][0]
 * @param mixed	    $dflt	Returned if the key does not exist
 *
 * @throws \Framework\Exception\BadValue
 * @return mixed
 */
        public function get($name, $dflt = '')
        {
            return $this->fetchit(INPUT_GET, $_GET, $name, $dflt, FALSE);
        }
/**
 * Look in the $_GET array for a key that is an id for a bean
 *
 * N.B. This function assumes the value is a string and will fail if used on array values
 *
 * @param mixed 	$name	    The key or if it is an array then the key and the fields that are needed $_GET['xyz'][0]
 * @param string    $bean       The bean type
 * @param bool   $forupdate  If TRUE then load for update
 *
 * @throws \Framework\Exception\BadValue
 *
 * @return \RedBeanPHP\OODBBean
 */
        public function mustgetbean($name, $bean, $forupdate = FALSE) : \RedBeanPHP\OODBBean
        {
            return Context::getinstance()->load($bean, $this->fetchit(INPUT_GET, $_GET, $name, NULL, TRUE), $forupdate);
        }
/**
 * Look in the $_GET array for a key that is an array and return an ArrayIterator over it
 *
 * @param string	$name	The key or if it is an array then the key and the fields that are needed $_GET['xyz'][0]
 *
 * @throws \Framework\Exception\BadValue
 * @return \ArrayIterator
 */
        public function mustgeta($name) : \ArrayIterator
        {
            if (filter_has_var(INPUT_GET, $name) && is_array($_GET[$name]))
            {
                return new \ArrayIterator($_GET[$name]);
            }
            throw new \Framework\Exception\BadValue('Missing get array '.$name);
        }
/**
 * Look in the $_GET array for a key that is an array and return an ArrayIterator over it
 *
 * @param string	$name	The key
 * @param array		$dflt	Returned if the key does not exist
 *
 * @return \ArrayIterator
 */
        public function geta(string $name, array $dflt = []) : \ArrayIterator
        {
            return new \ArrayIterator(filter_has_var(INPUT_GET, $name) && is_array($_GET[$name]) ? $_GET[$name] : $dflt);
        }
/**
 * Look in the $_GET array for a key and apply filters
 *
 * @param string	$name		The key
 * @param mixed     $default    A default value
 * @param int   	$filter		Filter values - see PHP manual
 * @param mixed		$options	see PHP manual
 *
 * @return mixed
 */
        public function filterget(string $name, $default, int $filter, $options = '')
        {
            $res = filter_input(INPUT_GET, $name, $filter, $options);
            return $res === FALSE || $res === NULL ? $default : $res;
        }
/**
 * Look in the $_GET array for a key and apply filters
 *
 * @param string	$name		The key
 * @param int   	$filter		Filter values - see PHP manual
 * @param mixed		$options	see PHP manual
 *
 * @throws \Framework\Exception\BadValue
 *
 * @return mixed
 */
        public function mustfilterget(string $name, int $filter, $options = '')
        {
            $res = filter_input(INPUT_GET, $name, $filter, $options);
            if ($res === NULL)
            { # no such variable
                throw new \Framework\Exception\BadValue('Missing get item '.$name);
            }
            if ($res === FALSE)
            { # filter error
                throw new \Framework\Exception\BadValue('Get filter failure '.$name);
            }
            return $res;
        }
/*
 ***************************************
 * $_POST fetching methods
 ***************************************
 */
/**
 * Look in the $_POST array for a key and return its trimmed value
 *
 * N.B. This function assumes the value is a string and will fail if used on array values
 *
 * @param mixed	$name	The key or if it is an array then the key and the fields that are needed $_GET['xyz'][0]
 *
 * @throws \Framework\Exception\BadValue
 * @return mixed
 */
        public function mustpost($name)
        {
            return $this->fetchit(INPUT_POST, $_POST, $name, NULL, TRUE);
        }

/**
 * Look in the $_POST array for a key and return its trimmed value or a default value
 *
 * N.B. This function assumes the value is a string and will fail if used on array values
 *
 * @param mixed 	$name	The key or if it is an array then the key and the fields that are needed $_GET['xyz'][0]
 * @param mixed		$dflt	Returned if the key does not exist
 *
 * @return mixed
 */
        public function post($name, $dflt = '')
        {
            return $this->fetchit(INPUT_POST, $_POST, $name, $dflt, FALSE);
        }
/**
 * Look in the $_POST array for a key that is an id for a bean
 *
 * N.B. This function assumes the value is a string and will fail if used on array values
 *
 * @param mixed 	$name	The key or if it is an array then the key and the fields that are needed $_GET['xyz'][0]
 * @param string    $bean   The bean type
 *
 * @throws \Framework\Exception\BadValue
 *
 * @return \RedBeanPHP\OODBBean
 */
        public function mustpostbean($name, $bean) : \RedBeanPHP\OODBBean
        {
            return Context::getinstance()->load($bean, $this->fetchit(INPUT_GET, $_POST, $name, NULL, TRUE));
        }
/**
 * Look in the $_POST array for a key that is an array and return an ArrayIterator over it
 *
 * @param string	$name	The key
 *
 * @throws \Framework\Exception\BadValue
 * @return \ArrayIterator
 */
        public function mustposta(string $name) : \ArrayIterator
        {
            if (filter_has_var(INPUT_POST, $name) && is_array($_POST[$name]))
            {
                return new \ArrayIterator($_POST[$name]);
            }
            throw new \Framework\Exception\BadValue('Missing post array '.$name);
        }
/**
 * Look in the $_POST array for a key that is an array and return an ArrayIterator over it
 *
 * @param string 	$name	The key
 * @param array		$dflt	Returned if the key does not exist
 *
 * @return \ArrayIterator
 */
        public function posta(string $name, array $dflt = []) :\ArrayIterator
        {
            return new \ArrayIterator(filter_has_var(INPUT_POST, $name) && is_array($_POST[$name]) ? $_POST[$name] : $dflt);
        }
/**
 * Look in the $_POST array for a key and apply filters
 *
 * @param string	$name		The key
 * @param string    $default    A default value
 * @param int   	$filter		Filter values - see PHP manual
 * @param mixed		$options	see PHP manual
 *
 * @return mixed
 */
        public function filterpost(string $name, $default, int $filter, $options = '')
        {
            $res = filter_input(INPUT_POST, $name, $filter, $options);
            return $res === FALSE || $res === NULL ? $default : $res;
        }
/**
 * Look in the $_POST array for a key and apply filters
 *
 * @param string	$name		The key
 * @param int   	$filter		Filter values - see PHP manual
 * @param mixed		$options	see PHP manual
 *
 * @throws \Framework\Exception\BadValue
 * @return mixed
 */
        public function mustfilterpost(string $name, int $filter, $options = '')
        {
            $res = filter_input(INPUT_POST, $name, $filter, $options);
            if ($res === NULL)
            {
                throw new \Framework\Exception\BadValue('Missing post item '.$name);
            }
            if ($res === FALSE)
            {
                throw new \Framework\Exception\BadValue('Post filter failure '.$name);
            }
            return $res;
        }
/*
 ***************************************
 * PUT data fetching methods
 ***************************************
 */
/**
 * Get php://input data, check array for a key and return its trimmed value
 *
 * N.B. This function assumes the value is a string and will fail if used on array values
 *
 * @param mixed	$name	The key or if it is an array then the key and the fields that are needed $_GET['xyz'][0]
 *
 * @throws \Framework\Exception\BadValue
 * @return mixed
 */
        public function mustput($name)
        {
            $this->setput();
            if (is_array($name))
            {
                if (isset($this->putdata[$name[0]]))
                {
                    $n = array_shift($name);
                    return $this->getval($this->putdata[$n], $name, NULL, TRUE);
                }
            }
            elseif (isset($this->putdata[$name]))
            {
                return trim($this->putdata[$name]);
            }
            throw new \Framework\Exception\BadValue('Missing put/patch item');
       }
/**
 * Get php://input data, check array for an id and return its bean
 *
 * N.B. This function assumes the value is a string and will fail if used on array values
 *
 * @param mixed 	$name	The key or if it is an array then the key and the fields that are needed $_GET['xyz'][0]
 * @param string        $bean   The bean type
 *
 * @throws \Framework\Exception\BadValue
 * 
 * @return \RedBeanPHP\OODBBean
 */
        public function mustputbean($name, $bean) : \RedBeanPHP\OODBBean
        {
            return Context::getinstance()->load($bean, $this->mustput($name));
        }
/**
 * Get php://input data, check arrayfor a key and return its trimmed value or a default value
 *
 * N.B. This function assumes the value is a string and will fail if used on array values
 *
 * @param mixed	  $name	The key or if it is an array then the key and the fields that are needed $_GET['xyz'][0]
 * @param mixed	  $dflt	Returned if the key does not exist
 *
 * @return mixed
 */
        public function put($name, $dflt = '')
        {
            $this->setput();
            if (is_array($name))
            {
                if (isset($this->putdata[$name[0]]))
                {
                    $n = array_shift($name);
                    return $this->getval($this->putdata[$n], $name, $dflt, FALSE);
                }
                return $dflt;
            }
            return isset($this->putdata[$name]) ? trim($this->putdata[$name]) : $dflt;
        }
/*
 ******************************
 * $_COOKIE helper functions
 ******************************
 */
/**
 * Look in the $_COOKIE array for a key and return its trimmed value or fail
 *
 * @param string     $name The cookie name
 *
 * @throws \Framework\Exception\BadValue
 * @return string
 */
        public function mustcookie(string $name) : string
        {
            return $this->fetchit(INPUT_COOKIE, $_COOKIE, $name, NULL, TRUE);
        }
/**
 * Look in the $_COOKIE array for a key and return its trimmed value or a default value
 *
 * @param string	$name	The key
 * @param mixed		$dflt	Returned if the key does not exist
 *
 * @return string
 */
        public function cookie(string $name, $dflt = '') : string
        {
            return $this->fetchit(INPUT_COOKIE, $_COOKIE, $name, $dflt, FALSE);
        }
/**
 * Look in the $_POST array for a key and  apply filters
 *
 * @param string	$name		The key
 * @param $mixed    $default    The default value
 * @param int		$filter		Filter values - see PHP manual
 * @param mixed		$options	see PHP manual
 *
 * @return mixed
 */
        public function filtercookie(string $name, $default, int $filter, $options = '')
        {
            $res = filter_input(INPUT_COOKIE, $name, $filter, $options);
            return $res === FALSE || $res === NULL ? $default : $res;
        }
/**
 * Look in the $_COOKIE array for a key and apply filters
 *
 * @param string	$name		The key
 * @param int   	$filter		Filter values - see PHP manual
 * @param mixed		$options	see PHP manual
 *
 * @throws \Framework\Exception\BadValue
 * @return mixed
 */
        public function mustfiltercookie(string $name, int $filter, $options = '')
        {
            $res = filter_input(INPUT_COOKIE, $name, $filter, $options);
            if ($res === NULL)
            {
                throw new \Framework\Exception\BadValue('Missing cookie item '.$name);
            }
            if ($res === FALSE)
            {
                throw new \Framework\Exception\BadValue('Cookie filter failure '.$name);
            }
            return $res;
        }
/*
 ******************************
 * $_FILES helper functions
 ******************************
 */
/**
 * Make arrays of files work more like singletons
 *
 * @param string    $name
 * @param mixed     $key
 *
 * @return array
 */
        public function filedata(string $name, $key = '') : array
        {
            $x = $_FILES[$name];
            if ($key !== '')
            {
                return [
                    'name'     => $x['name'][$key],
                    'type'     => $x['type'][$key],
                    'size'     => $x['size'][$key],
                    'tmp_name' => $x['tmp_name'][$key],
                    'error'    => $x['error'][$key]
                ];
            }
            return $x;
        }
/**
 * Make arrays of files work more like singletons
 *
 * @param string    $name
 *
 * @return \ArrayIterator
 */
        public function filea(string $name, array $dflt = []) : \ArrayIterator
        {
            return isset($_FILES[$name]) && is_array($_FILES[$name]['error']) ? new \Framework\FAIterator($name) : new \ArrayIterator($dflt);
        }
    }
?>
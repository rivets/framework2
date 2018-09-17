<?php
/**
 * Contains the definition of the Formdata class
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2015-2018 Newcastle University
 */
    namespace Framework;

    use Framework\Web\Web as Web;
    use \Support\Context as Context;
/**
 * A class that provides helpers for accessing form data
 */
    class Formdata
    {
        use \Framework\Utility\Singleton;
/**
 * @var string    Holds data read from php://input
 */
        private $putdata    = NULL;
/**
 * Fetch the input data if required
 *
 * @return void
 */
        private function setput()
        {
            if (!is_array($this->putdata))
            {
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
 * @return boolean
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
 * @return boolean
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
 * @return boolean
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
 * @return boolean
 */
        public function hasfile(string $name) : bool
        {
            return isset($_FILES[$name]);
        }
/**
 * Utility function to dig out an element from a possibly multi-dimensional array
 *
 * @interna
 * @see \Framework\Context for failure action constants
 *
 * @param array     $porg       The array
 * @param array     $keys       An array of keys
 * @param mixed     $default    A value to return if the item is missing and we are not failing
 * @param int       $fail       Failure action
 *
 * @throws Exception
 * @return string
 */
        private function getval(array $porg, array $keys, $default = NULL, int $fail = Context::RDEFAULT) : string
        {
            while (TRUE)
            {
                $key = array_shift($keys);
                if (!isset($porg[$key]))
                {
                    break;
                }
                $val = $porg[$key];
                if (empty($keys))
                {
                    return trim($val);
                }
            }
            return $this->failure($fail, 'Missing form array item', $default);
        }
/**
 * Method to handle error returning
 *
 * @internal
 * @see \Framework\Context for failure action constants
 *
 * May not actually return;
 *
 * @param int       $option     The action to take (constants defined in Context)
 * @param string    $message    Error message
 * @param mixed     $dflt       Default value to return
 *
 * @throws Exception
 * @return NULL
 */
        private function failure(int $option, string $message, $dflt = NULL)
        {
            switch($option)
            {
            case Context::R400:
                Web::getinstance()->bad($message);

            case Context::RTHROW:
                throw new Exception($message);

            case Context::RNULL:
                return NULL;

            case Context::RDEFAULT:
                return $dflt;
            }
            Web::getinstance()->internal(); // should never get here
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
 * @param int       $fail       What to do if not defined - constant defined in Context
 *
 * @throws Exception
 * @return mixed
 */
        private function fetchit(int $filter, array $arr, $name, $dflt = '', int $fail = Context::R400)
        {
            if (is_array($name))
            {
                $n = array_shift($name); // shift off the variable name
                if (filter_has_var($filter, $n))
                { // the entry is there
                    return $this->getval($arr[$n], $name, NULL, $fail);
                }
            }
            elseif (filter_has_var($filter, $name))
            {
                if (is_array($arr[$name]))
                {
                    return $this->failure($fail, $name.' is array', $dflt);
                }
                return trim($arr[$name]);
            }
            return $this->failure($fail, 'Missing form item '.(is_array($name) ? ($n.'['.$name[0].']') : $name), $dflt);
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
 * @param int    	$fail	What to do if not defined - constant defined in Context
 *
 * @return mixed
 */
        public function mustget($name, int $fail = Context::R400)
        {
            return $this->fetchit(INPUT_GET, $_GET, $name, NULL, $fail);
        }
/**
 * Look in the $_GET array for a key and return its trimmed value or a default value
 *
 * N.B. This function assumes the value is a string and will fail if used on array values
 *
 * @param mixed	    $name	The key or if it is an array then the key and the fields that are needed $_GET['xyz'][0]
 * @param mixed	    $dflt	Returned if the key does not exist
 *
 * @return mixed
 */
        public function get($name, $dflt = '')
        {
            return $this->fetchit(INPUT_GET, $_GET, $name, $dflt, Context::RDEFAULT);
        }
/**
 * Look in the $_GET array for a key that is an array and return an ArrayIterator over it
 *
 * @param string	$name	The key or if it is an array then the key and the fields that are needed $_GET['xyz'][0]
 * @param int   	$fail	if the key does not exist - see Context::load
 *
 * @return \ArrayIterator
 */
        public function mustgeta($name, int $fail = Context::R400)
        {
            if (filter_has_var(INPUT_GET, $name) && is_array($_GET[$name]))
            {
                return new \ArrayIterator($_GET[$name]);
            }
            return $this->failure($fail, 'Missing get array');
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
 * @param string        $default    A default value
 * @param int   	$filter		Filter values - see PHP manual
 * @param mixed		$options	see PHP manual
 *
 * @return mixed
 */
        public function filterget(string $name, string $default, int $filter, $options = '')
        {
            $res = filter_input(INPUT_GET, $name, $filter, $options);
            return $res === FALSE || $res === NULL ? $default : $res;
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
 * @param int 	$fail	Fail if the key does not exist in the array - see Context::load
 *
 * @return mixed
 */
        public function mustpost(string $name, int $fail = Context::R400)
        {
            return $this->fetchit(INPUT_POST, $_POST, $name, NULL, $fail);
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
            return $this->fetchit(INPUT_POST, $_POST, $name, $dflt, Context::RDEFAULT);
        }
/**
 * Look in the $_POST array for a key that is an array and return an ArrayIterator over it
 *
 * @param string	$name	The key
 * @param int   	$fail	What to do if not defined - constant defined in Context
 *
 * @return ArrayIterator
 */
        public function mustposta(string $name, int $fail = Context::R400) : \ArrayIterator
        {
            if (filter_has_var(INPUT_POST, $name) && is_array($_POST[$name]))
            {
                return new \ArrayIterator($_POST[$name]);
            }
            return $this->failure($fail, 'Missing post array');
        }
/**
 * Look in the $_POST array for a key that is an array and return an
 ArrayIterator over it
 *
 * @param string 	$name	The key
 * @param array		$dflt	Returned if the key does not exist
 *
 * @return ArrayIterator
 */
        public function posta(string $name, array $dflt = []) :\ArrayIterator
        {
            return new \ArrayIterator(filter_has_var(INPUT_POST, $name) && is_array($_POST[$name]) ? $_POST[$name] : $dflt);
        }
/**
 * Look in the $_POST array for a key and  apply filters
 *
 * @param string 	$name		The key
 * @param int    	$filter		Filter values - see PHP manual
 * @param mixed		$options	see PHP manual
 *
 * @return mixed
 */
        public function filterpost(string $name, int $filter, $options = '')
        {
            return filter_input(INPUT_POST, $name, $filter, $options);
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
 * @param int	$fail	Fail if the key does not exist in the array - see Context::load
 *
 * @return mixed
 */
        public function mustput($name, int $fail = Context::R400)
        {
            $this->setput();
            if (is_array($name))
            {
                if (isset($this->putdata[$name[0]]))
                {
                    $n = array_shift($name);
                    return $this->getval($this->putdata[$n], $name, NULL, $fail);
                }
            }
            elseif (isset($this->putdata[$name]))
            {
                return trim($this->putdata[$name]);
            }
            return $this->failure($fail, 'Missing put/patch item');
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
 * @param int        $fail Action to take on failure
 *
 * @return mixed
 */
        public function mustcookie(string $name, int $fail = Context::R400)
        {
            return $this->fetchit(INPUT_COOKIE, $_COOKIE, $name, NULL, $fail);
        }
/**
 * Look in the $_COOKIE array for a key and return its trimmed value or a default value
 *
 * @param string	$name	The key
 * @param mixed		$dflt	Returned if the key does not exist
 *
 * @return mixed
 */
        public function cookie(string $name, $dflt = '')
        {
            return $this->fetchit(INPUT_COOKIE, $_COOKIE, $name, $dflt, Context::RDEFAULT);
        }
/**
 * Look in the $_COOKIE array for a key that is an array and return an ArrayIterator over it
 *
 * @param string	$name	The key
 * @param int   	$fail	What to do if not defined - constant defined in Context
 *
 * @return \ArrayIterator
 */
        public function mustcookiea(string $name, int $fail = Context::R400) : \ArrayIterator
        {
            if (filter_has_var(INPUT_COOKIE, $name) && is_array($_COOKIE[$name]))
            {
                return new \ArrayIterator($_POST[$name]);
            }
            return $this->failure($fail, 'Missing cookie array');
        }
/**
 * Look in the $_COOKIE array for a key that is an array and return an ArrayIterator over it
 *
 * @param string	$name	The key
 * @param array		$dflt	Returned if the key does not exist
 *
 * @return \ArrayIterator
 */
        public function cookiea(string $name, array $dflt = []) : \ArrayIterator
        {
            return new \ArrayIterator(filter_has_var(INPUT_COOKIE, $name) && is_array($_COOKIE[$name]) ? $_COOKIE[$name] : $dflt);
        }
/**
 * Look in the $_POST array for a key and  apply filters
 *
 * @param string	$name		The key
 * @param int		$filter		Filter values - see PHP manual
 * @param mixed		$options	see PHP manual
 *
 * @return mixed
 */
        public function filtercookie(string $name, int $filter, $options = '')
        {
            return filter_input(INPUT_COOKIE, $name, $filter, $options);
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
    }
?>

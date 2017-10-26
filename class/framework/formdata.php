<?php
/**
 * Contains the definition of the Formdata class
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2015 Newcastle University
 */
    namespace Framework;

    use Framework\Web\Web as Web;
/**
 * A class that provides helpers for accessing form data
 */
    class Formdata
    {
        use \Utility\Singleton;
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
        public function hasget($name)
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
        public function haspost($name)
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
        public function hascookie($name)
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
        public function hasfile($name)
        {
            return isset($_FILES[$name]);
        }
/**
 * Utility function to dig out an element from a possibly multi-dimensional array
 *
 * @param array     $porg       The array
 * @param array     $keys       An array of keys
 * @param mixed     $default    A value to return if the item is missing and we are not failing
 * @param boolean   $fail       If TRUE then exit the process with a 400 Bad return code
 *
 * @return string
 */
        private function getval($porg, $keys, $default = NULL, $fail = FALSE)
        {
            while (TRUE)
            {
                $key = array_shift($keys);
                if (!isset($porg[$key]))
                {
                    if ($fail)
                    {
                        Web::getinstance()->bad();
                    }
                    return $default;
                }
                $val = $porg[$key];
                if (empty($keys))
                {
                    return trim($val);
                }
            }
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
 * @param boolean	$fail	If TRUE then generate a 400 if the key does not exist in the array
 *
 * @return mixed
 */
        public function mustget($name, $fail = Context::R400)
        {
            if (is_array($name) && filter_has_var(INPUT_GET, $name[0]))
            {
                $n = array_shift($name);
                return $this->getval($_GET[$n], $name, NULL, $fail);
            }
            elseif (filter_has_var(INPUT_GET, $name))
            {
                return trim($_GET[$name]);
            }
            switch($fail)
            {
            case Context::R400:
                Web::getinstance()->bad();
            
            case Context::RTHROW:
                throw new Exception('Missing get item');
            }
            return NULL;
        }
/**
 * Look in the $_GET array for a key and return its trimmed value or a default value
 * 
 * N.B. This function assumes the value is a string and will fail if used on array values
 *
 * @param string	$name	The key
 * @param mixed		$dflt	Returned if the key does not exist
 *
 * @return mixed
 */
        public function get($name, $dflt = '')
        {
            if (is_array($name) && filter_has_var(INPUT_GET, $name[0]))
            {
                $n = array_shift($name);
                return $this->getval($_GET[$n], $name, $dflt, FALSE);
            }
            return filter_has_var(INPUT_GET, $name) ? trim($_GET[$name]) : $dflt;
        }
/**
 * Look in the $_GET array for a key that is an array and return an ArrayIterator over it
 *
 * @param string	$name	The key
 * @param integer	$fail	if the key does not exist - see Context::load
 *
 * @return ArrayIterator
 */
        public function mustgeta($name, $fail = Context::R400)
        {
            if (filter_has_var(INPUT_GET, $name) && is_array($_GET[$name]))
            {
                return new \ArrayIterator($_GET[$name]);
            }
            switch($fail)
            {
            case Context::R400:
                Web::getinstance()->bad();
            
            case Context::RTHROW:
                throw new Exception('Missing get item');
            }
            return NULL;
        }
/**
 * Look in the $_GET array for a key that is an array and return an ArrayIterator over it
 *
 * @param string	$name	The key
 * @param array		$dflt	Returned if the key does not exist
 *
 * @return ArrayIterator
 */
        public function geta($name, array $dflt = [])
        {
            return new \ArrayIterator(filter_has_var(INPUT_GET, $name) && is_array($_GET[$name]) ? $_GET[$name] : $dflt);
        }
/**
 * Look in the $_GET array for a key and apply filters
 *
 * @param string	$name		The key
 * @param string    $default    A default value
 * @param int		$filter		Filter values - see PHP manual
 * @param mixed		$options	see PHP manual
 *
 * @return mixed
 */
        public function filterget($name, $default, $filter, $options = '')
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
 * @param string	$name	The key
 * @param integer	$fail	Fail if the key does not exist in the array - see Context::load
 *
 * @return mixed
 */
        public function mustpost($name, $fail = Context::R400)
        {
            if (is_array($name) && filter_has_var(INPUT_POST, $name[0]))
            {
                $n = array_shift($name);
                return $this->getval($_POST[$n], $name, NULL, $fail);
            }
            elseif (filter_has_var(INPUT_POST, $name))
            {
                return trim($_POST[$name]);
            }
            switch($fail)
            {
            case Context::R400:
                Web::getinstance()->bad();
            
            case Context::RTHROW:
                throw new Exception('Missing post item');
            }
            return NULL;
       }

/**
 * Look in the $_POST array for a key and return its trimmed value or a default value
 * 
 * N.B. This function assumes the value is a string and will fail if used on array values
 *
 * @param string	$name	The key
 * @param mixed		$dflt	Returned if the key does not exist
 *
 * @return mixed
 */
        public function post($name, $dflt = '')
        {
            if (is_array($name) && filter_has_var(INPUT_POST, $name[0]))
            {
                $n = array_shift($name);
                return $this->getval($_POST[$n], $name, $dflt, FALSE);
            }
            return filter_has_var(INPUT_POST, $name) ? trim($_POST[$name]) : $dflt;
        }

/**
 * Look in the $_POST array for a key that is an array and return an ArrayIterator over it
 *
 * @param string	$name	The key
 * @param boolean	$fail	If TRUE then generate a 400 if the key does not exist in the array
 *
 * @return ArrayIterator
 */
        public function mustposta($name, $fail = Context::R400)
        {
            if (filter_has_var(INPUT_POST, $name) && is_array($_POST[$name]))
            {
                return new \ArrayIterator($_POST[$name]);
            }
            switch($fail)
            {
            case Context::R400:
                Web::getinstance()->bad();
            
            case Context::RTHROW:
                throw new Exception('Missing post item');
            }
            return NULL;
        }

/**
 * Look in the $_POST array for a key that is an array and return an
 ArrayIterator over it
 *
 * @param string	$name	The key
 * @param array		$dflt	Returned if the key does not exist
 *
 * @return ArrayIterator
 */
        public function posta($name, array $dflt = [])
        {
            return new \ArrayIterator(filter_has_var(INPUT_POST, $name) && is_array($_POST[$name]) ? $_POST[$name] : $dflt);
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
        public function filterpost($name, $filter, $options = '')
        {
            return filter_input(INPUT_POST, $name, $filter, $options);
        }
/*
 ******************************
 * $_COOKIE helper functions
 ******************************
 */
/**
 * Look in the $_COOKIE array for a key and return its trimmed value or fail
 *
 * @param string    $name
 * @param boolean    $fail
 *
 * @return mixed
 */
        public function mustcookie($name, $fail = Context::R400)
        {
            if (filter_has_var(INPUT_COOKIE, $name))
            {
                return trim($_COOKIE[$name]);
            }
            switch($fail)
            {
            case Context::R400:
                Web::getinstance()->bad();
            
            case Context::RTHROW:
                throw new Exception('Missing cookie item');
            }
            return NULL;
        }
/**
 * Look in the $_COOKIE array for a key and return its trimmed value or a default value
 *
 * @param string	$name	The key
 * @param mixed		$dflt	Returned if the key does not exist
 *
 * @return mixed
 */
        public function cookie($name, $dflt = '')
        {
            return filter_has_var(INPUT_COOKIE, $name) ? trim($_COOKIE[$name]) : $dflt;
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
 * @param string    $key
 *
 * @return array
 */
        public function filedata($name, $key = '')
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

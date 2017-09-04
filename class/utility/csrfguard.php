<?php
    namespace Utility
/**
 * A class to generate and handling CSRF prevention tokens.
 *
 * This is derived directly from the code to be found at
 *
 * @link	https://www.owasp.org/index.php/PHP_CSRF_Guard
 *
 * I have rewritten it a bit to make it a little more "wieldy" and conformant
 * to the style of this framework
 *
 * ****************** NB This is just me playing around at the moment!!!! Not tested at all *******************
 */
/**
 * Contains definition of CRSFGuard class
 */    class CSRFGuard
    {
/**
 * @var string		Used for generating unique ids when there is no sha512
 */
	private static $alfno = 'abcdefghijklmnopqrstuvwxyz0123456789'; # could be a CONST but not all PHPs let you [] a const
/**
 * Generate unique token
 *
 * @param string	$uname	The name to be used for storing the token into the Session data
 *
 * @return string	The token
 */
	private static function maketoken($uname)
	{
	    if (function_exists('hash_algos') && in_array('sha512', hash_algos()))
	    {
		$token = hash('sha512', mt_rand(0, mt_getrandmax()));
	    }
	    else
	    {
		$token = '';
		foreach (range(0, 127) as $i)
		{
		    $token .= self::$alfno[mt_rand(0, 35)];
		}
	    }
	    if (isset($_SESSION))
	    {
		$_SESSION[$uname] = $token;
	    }
	    return $token;
	}
/**
 * Generate unique token
 *
 * @param string	$uname		The name to be used for storing the token into the Session data
 * @param string	$tocheck	The token to be compared with what is stored
 *
 * @return boolean	The token
 */
	private static function validate($uname, $tocheck)
	{
	    if (!isset($_SESSION[$uname]))
	    { # no token in there so we are not checking so it's valid
		return TRUE;
	    }
	    $token = $_SESSION[$uname];
	    unset($_SESSION[$uname]);
	    return $token == $tocheck;
	}
/**
 * Generate a name and a token
 *
 * @return array
 */
	public function generate()
	{
	    $name = 'CSRFGuard_'.mt_rand(0, mt_getrandmax());
	    return array($name,  $this->maketoken($name));
	}
/**
 * A constructor for a CSRF object
 *
 * @throws Exception when CSRFName is expected and not found
 * @throws Exception when token or name is not as stored in session
 */
	public function __construct()
	{
	    if (!empty($_POST))
	    {
		if (!isset($_POST['CSRFName']) || !isset($_POST['CSRFToken']) )
		{
		    throw Exception('No CSRFName found, probable invalid request.');
		}
		if (!csrfguard_validate_token($_POST['CSRFName'], $_POST['CSRFToken']))
		{
		    throw Exception('Invalid CSRF token');
		}
	    }
	}
    }
?>

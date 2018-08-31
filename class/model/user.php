<?php
/**
 * A model class for the RedBean object User
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2013-2018 Newcastle University
 *
 */
    namespace Model;
/**
 * A class implementing a RedBean model for User beans
 */
    class User extends \RedBeanPHP\SimpleModel
    {
/**
 * @var string   The type of the bean that stores roles for this page
 */
        private $roletype = 'role';
/**
 * @var Array   Key is name of field and the array contains flags for checks
 */
        private static $editfields = [
            'email'     => [TRUE, FALSE],         # [NOTEMPTY]
        ];
/**
 * @var Holds the CSRF var for the edit form
 */
        private static $csrf = NULL;

        use \ModelExtend\User;
        use \ModelExtend\FWEdit;
        use \Framework\HandleRole;
/**
 * Is this user an admin?
 *
 * @return boolean
 */
        public function isadmin()
        {
            return is_object($this->hasrole('Site', 'Admin'));
        }
/**
 * Is this user active?
 *
 * @return boolean
 */
        public function isactive()
        {
            return $this->bean->active;
        }
/**
 * Is this user confirmed?
 *
 * @return boolean
 */
        public function isconfirmed()
        {
            return $this->bean->confirm;
        }
/**
 * Is this user a developer?
 *
 * @return boolean
 */
        public function isdeveloper()
        {
            return is_object($this->hasrole('Site', 'Developer'));
        }
/**
 * Set the user's password
 *
 * @param string	$pw	The password
 *
 * @return void
 */
        public function setpw($pw)
        {
            $this->bean->password = password_hash($pw, PASSWORD_DEFAULT);
            \R::store($this->bean);
        }
/**
 * Check a password
 *
 * @param string	$pw The password
 *
 * @return boolean
 */
        public function pwok($pw)
        {
            return password_verify($pw, $this->bean->password);
        }
/**
 * Set the email confirmation flag
 *
 * @return void
 */
        public function doconfirm()
        {
            $this->bean->active = 1;
            $this->bean->confirm = 1;
            \R::store($this->bean);
        }
/**
 * Generate a token for this user that can be used as a unique id from a phone.
 *
 * @param string    $device     Currently not used!!
 *
 * @return string
 */
	public function maketoken($device = '')
	{
	    $token = (object)['iss' => \Config\Config::SITEURL, 'iat' => idate('U'), 'sub' => $this->bean->getID()];
	    return JWT::encode($token, \Framework\Context::KEY);
	}
/**
 * Setup for an edit
 *
 * @param object    $context   The context object
 * 
 * @return void
 */
        public function startEdit($context)
        {
            if (!is_object(self::$csrf))
            {
                self::$csrf = new \Framework\Utility\CSRFGuard;
            }
        }
/**
 * Return the CSRFGuard inputs for inclusion in a form;
 * 
 * @return string
 */
        public function guard()
        {
            return self::$csrf->inputs();
        }
/**
 * Handle an edit form for this user
 *
 * @param object   $context    The context object
 *
 * @return  array   [TRUE if error, [error messages]]
 */
        public function edit($context)
        {
            $fdt = $context->formdata();
            $emess = $this->dofields($fdt);

            $pw = $fdt->post('pw', '');
            if ($pw !== '')
            {
                if ($pw == $fdt->post('rpw', ''))
                {
                    $this->setpw($pw); // setting the password will do a store
                }
                else
                {
                    $emess[] = 'Passwords do not match';
                }
            }
            $this->editroles($context);
            return [!empty($emess), $emess];
        }
    }
?>

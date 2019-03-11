<?php
/**
 * A model class for the RedBean object User
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2013-2018 Newcastle University
 *
 */
    namespace Model;

    use \Support\Context as Context;
    use \Config\Config as Config;
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

        use \ModelExtend\User;
        use \ModelExtend\FWEdit;
        use \ModelExtend\MakeGuard;
        use \Framework\HandleRole;
/**
 * Function called when a user bean is updated - do error checking in here
 *
 * @throws \Framework\Exception\BadValue
 * @return void
 */
        public function update()
        {
            if (!preg_match('/^[a-z0-9]+/i', $this->bean->login))
            {
                throw new \Framework\Exception\BadValue('Invalid login name');
            }
            if (!filter_var($this->bean->email, FILTER_VALIDATE_EMAIL))
            {
                throw new \Framework\Exception\BadValue('Invalid email address');
            }
/**
 * @todo Validate the joined field. Correct date, not in the future
 */
        }
/**
 * Add a User from a form
 *
 * @see Framework\Ajax::bean
 *
 * @param object	$context	The context object for the site
 *
 * @throws \Framework\Exception\BadValue
 *
 * @return object
 */
        public static function add(Context $context) : object
        {
            $now = $context->utcnow(); # make sure time is in UTC
            $fdt = $context->formdata();
            $pw = $fdt->mustpost('password'); // make sure we have a password...
            if (self::checkpw($pw))
            {
                $login = $fdt->mustpost('login');
                if (is_object(\R::findOne('user', 'login=?', [$login])))
                {
                    throw new \Framework\Exception\BadValue('Login name already exists');
                }
                $u = \R::dispense('user');
                $u->login = $login;
                $u->email = $fdt->mustpost('email');
                $u->active = 1;
                $u->confirm = 1;
                $u->joined = $now;
                \R::store($u);
                $u->setpw($pw); // set the password
                if ($fdt->post('admin', 0) == 1)
                {
                    $u->addrole(Config::FWCONTEXT, Config::ADMINROLE, '', $now);
                }
                if ($fdt->post('devel', 0) == 1)
                {
                    $u->addrole(Config::FWCONTEXT, Config::DEVELROLE, '', $now);
                }
                return $u;
            }
            else
            {
                // bad password return
                throw new \Framework\Exception\BadValue('Invalid Password');
            }
        }
/**
 * Is this user an admin?
 *
 * @return boolean
 */
        public function isadmin()
        {
            return is_object($this->hasrole(Config::FWCONTEXT, Config::ADMINROLE));
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
            return is_object($this->hasrole(Config::FWCONTEXT, Config::DEVELROLE));
        }
/**
 * Set the user's password
 *
 * @param string	$pw	The password
 *
 * @return void
 */
        public function setpw(string $pw)
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
        public function pwok(string $pw)
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
	public function maketoken(string $device = '')
	{
	    $token = (object)['iss' => \Config\Config::SITEURL, 'iat' => idate('U'), 'sub' => $this->bean->getID()];
	    return \Framework\Utility\JWT\JWT::encode($token, \Framework\Context::KEY);
	}
/**
 * Setup for an edit
 *
 * @param object    $context   The context object
 * 
 * @return void
 */
        public function startEdit(Context $context, array $rest)
        {
        }
/**
 * Handle an edit form for this user
 *
 * @param object   $context    The context object
 *
 * @return  array   [TRUE if error, [error messages]]
 */
        public function edit(Context $context)
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

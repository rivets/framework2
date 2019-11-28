<?php
/**
 * A model class for the RedBean object User
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2013-2019 Newcastle University
 *
 */
    namespace Model;

    use \Support\Context as Context;
    use \Config\Framework as FW;
/**
 * A class implementing a RedBean model for User beans
 */
    class User extends \RedBeanPHP\SimpleModel
    {
/**
 * @var string   The type of the bean that stores roles for this page
 */
        private $roletype = FW::ROLE;
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
        public function update() : void
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
 * Add a User from a form - invoked by the AJAX bean operation
 *
 * @param \Support\Context	$context	The context object for the site
 *
 * @throws \Framework\Exception\BadValue
 *
 * @return \RedBeanPHP\OODBBean
 */
        public static function add(Context $context) : \RedBeanPHP\OODBBean
        {
            $now = $context->utcnow(); # make sure time is in UTC
            $fdt = $context->formdata();
            $pw = $fdt->mustpost('password'); // make sure we have a password...
            if (self::pwValid($pw))
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
                    $u->addrole(FW::FWCONTEXT, FW::ADMINROLE, '', $now);
                }
                if ($fdt->post('devel', 0) == 1)
                {
                    $u->addrole(FW::FWCONTEXT, FW::DEVELROLE, '', $now);
                }
                $u->addData($context);
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
 * @return bool
 */
        public function isadmin() : bool
        {
            return is_object($this->hasrole(FW::FWCONTEXT, FW::ADMINROLE));
        }
/**
 * Is this user active?
 *
 * @return bool
 */
        public function isactive() : bool
        {
            return $this->bean->active;
        }
/**
 * Is this user confirmed?
 *
 * @return bool
 */
        public function isconfirmed() : bool
        {
            return $this->bean->confirm;
        }
/**
 * Is this user a developer?
 *
 * @return bool
 */
        public function isdeveloper() : bool
        {
            return is_object($this->hasrole(FW::FWCONTEXT, FW::DEVELROLE));
        }
/**
 * Set the user's password
 *
 * @param string	$pw	The password
 *
 * @return void
 */
        public function setpw(string $pw) : void
        {
            $this->bean->password = password_hash($pw, PASSWORD_DEFAULT);
            \R::store($this->bean);
        }
/**
 * Check a password
 *
 * @param string	$pw The password
 *
 * @return bool
 */
        public function pwok(string $pw) : bool
        {
            return password_verify($pw, $this->bean->password);
        }
/**
 * Set the email confirmation flag
 *
 * @return void
 */
        public function doconfirm() : void
        {
            $this->bean->active = 1;
            $this->bean->confirm = 1;
            \R::store($this->bean);
        }
/**
 * Generate a token for this user that can be used as a unique id from a phone.
 *
 * @param string    $url        The URL of the site
 * @param string    $device     Currently not used!!
 *
 * @return string
 */
	public function maketoken(string $url, string $device = '') : string
	{
	    $token = (object)['iss' => $url, 'iat' => idate('U'), 'sub' => $this->bean->getID()];
        /** @psalm-suppress UndefinedClass - JWT is not currently included in the psalm checks... */
	    return \Framework\Utility\JWT\JWT::encode($token, \Framework\Context::KEY);
	}
/**
 * Setup for an edit
 *
 * @param \Support\Context    $context   The context object
 * @param array               $rest      Any other values from the URL
 * 
 * @return void
 */
        public function startEdit(Context $context, array $rest) : void
        {
        }
/**
 * Handle an edit form for this user
 *
 * @param \Support\Context   $context    The context object
 *
 * @return  array   [TRUE if error, [error messages]]
 */
        public function edit(Context $context) : array
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
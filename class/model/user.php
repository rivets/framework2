<?php
/**
 * A model class for the RedBean object User
 *
 * This is a Framework system class - do not edit!
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2013-2020 Newcastle University
 * @package Framework
 * @subpackage SystemModel
 */
    namespace Model;

    use \Config\Framework as FW;
    use \Support\Context;
/**
 * A class implementing a RedBean model for User beans
 * @psalm-suppress UnusedClass
 */
    class User extends \RedBeanPHP\SimpleModel
    {
/**
 * @var string   The type of the bean that stores roles for this page
 * @phpcsSuppress SlevomatCodingStandard.Classes.UnusedPrivateElements
 */
        private $roletype = FW::ROLE;
/**
 * @var Array   Key is name of field and the array contains flags for checks
 * @phpcsSuppress SlevomatCodingStandard.Classes.UnusedPrivateElements
 */
        private static $editfields = [
            'email'     => [TRUE, FALSE],         // [NOTEMPTY]
        ];

        use \ModelExtend\User;
        use \ModelExtend\FWEdit;
        use \ModelExtend\MakeGuard;
        use \Framework\Support\HandleRole;
/**
 * Add a User from a form - invoked by the AJAX bean operation
 *
 * @param Context    $context    The context object for the site
 *
 * @throws \Framework\Exception\BadValue
 * @return \RedBeanPHP\OODBBean
 */
        public static function add(Context $context) : \RedBeanPHP\OODBBean
        {
            $now = $context->utcnow(); // make sure time is in UTC
            $fdt = $context->formdata('post');
            $pw = $fdt->mustFetch('password'); // make sure we have a password...
            if (self::pwValid($pw))
            {
                $login = $fdt->mustFetch('login');
                if (is_object(\R::findOne('user', 'login=?', [$login])))
                {
                    throw new \Framework\Exception\BadValue('Login name already exists');
                }
                $u = \R::dispense('user');
                $u->login = $login;
                $u->email = $fdt->mustFetch('email');
                $u->active = 1;
                $u->confirm = 1;
                $u->joined = $now;
                \R::store($u);
                $u->setpw($pw); // set the password
                if ($fdt->fetch('admin', 0) == 1)
                {
                    $u->addrole(FW::FWCONTEXT, FW::ADMINROLE, '', $now);
                }
                if ($fdt->fetch('devel', 0) == 1)
                {
                    $u->addrole(FW::FWCONTEXT, FW::DEVELROLE, '', $now);
                }
                $u->addData($context);
                return $u;
            }
            // bad password return
            throw new \Framework\Exception\BadValue('Invalid Password');
        }
/**
 * Is this user an admin?
 *
 * @return bool
 */
        public function isAdmin() : bool
        {
            return is_object($this->hasrole(FW::FWCONTEXT, FW::ADMINROLE));
        }
/**
 * Is this user active?
 *
 * @return bool
 */
        public function isActive() : bool
        {
            return $this->bean->active;
        }
/**
 * Is this user confirmed?
 *
 * @return bool
 */
        public function isConfirmed() : bool
        {
            return $this->bean->confirm;
        }
/**
 * Is this user a developer?
 *
 * @return bool
 */
        public function isDeveloper() : bool
        {
            return is_object($this->hasRole(FW::FWCONTEXT, FW::DEVELROLE));
        }
/**
 * Set the user's password
 *
 * @param string    $pw The password
 *
 * @return void
 */
        public function setPW(string $pw) : void
        {
            $this->bean->password = password_hash($pw, PASSWORD_DEFAULT);
            \R::store($this->bean);
        }
/**
 * Check a password
 *
 * @param string    $pw The password
 *
 * @return bool
 */
        public function pwOK(string $pw) : bool
        {
            return password_verify($pw, $this->bean->password);
        }
/**
 * Set the email confirmation flag
 *
 * @return void
 */
        public function doConfirm() : void
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
 * @psalm-suppress UnusedVariable
 * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter
 */
        public function makeToken(string $url, string $device = '') : string
        {
            $token = (object) ['iss' => $url, 'iat' => idate('U'), 'sub' => $this->bean->getID()];
            /** @psalm-suppress UndefinedClass - JWT is not currently included in the psalm checks... */
            return \Framework\Utility\JWT\JWT::encode($token, FW::AUTHKEY);
        }
/**
 * Setup for an edit
 *
 * @param Context       $context   The context object
 * @param array<string> $rest      Any other values from the URL
 *
 * @return void
 * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter
 */
        public function startEdit(Context $context, array $rest) : void
        {
        }
/**
 * Handle an edit form for this user
 *
 * @param Context $context    The context object
 *
 * @return array  [TRUE if error, [error messages]]
 */
        public function edit(Context $context) : array
        {
            $fdt = $context->formData('post');
            $emess = $this->doFields($fdt);

            $pw = $fdt->fetch('pw', '');
            if ($pw !== '')
            {
                if ($pw === $fdt->fetch('rpw', ''))
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
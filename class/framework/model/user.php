<?php
/**
 * A model class for the RedBean object User
 *
 * !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!! This is a Framework system class - do not edit !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2013-2021 Newcastle University
 * @package Framework\Model
 */
    namespace Framework\Model;

    use \Config\Framework as FW;
    use \Support\Context;
/**
 * A class implementing a RedBean model for User beans
 * @psalm-suppress UnusedClass
 */
    final class User extends \RedBeanPHP\SimpleModel
    {
/**
 * @var string   The type of the bean that stores roles for this page
 * @phpcsSuppress SlevomatCodingStandard.Classes.UnusedPrivateElements
 */
        private string $roletype = FW::ROLE;
/**
 * @var array<array<bool>>  Key is name of field and the array contains flags for checks
 * @phpcsSuppress SlevomatCodingStandard.Classes.UnusedPrivateElements
 */
        private static array $editfields = [
            'email'     => [TRUE, FALSE],         // [NOTEMPTY]
        ];

        use \Framework\Support\MakeGuard;
        use \Framework\Support\HandleRole;
        use \ModelExtend\FWEdit;
        use \ModelExtend\User;
/**
 * Add a User from a form - invoked by the AJAX bean operation
 *
 * @see \Framework\Ajax\Bean
 *
 * @throws \Framework\Exception\BadValue
 */
        public static function add(Context $context) : \RedBeanPHP\OODBBean
        {
            $now = $context->utcnow(); // make sure time is in UTC
            $fdt = $context->formdata('post');
            $pw = $fdt->mustFetch('password'); // make sure we have a password...
            if (self::pwValid($pw))
            {
                $login = $fdt->mustFetch('login');
                if (\is_object(\R::findOne(FW::USER, 'login=?', [$login])))
                {
                    throw new \Framework\Exception\BadValue('Login name already exists');
                }
                $u = \R::dispense(FW::USER);
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
 */
        public function isAdmin() : bool
        {
            return \is_object($this->hasrole(FW::FWCONTEXT, FW::ADMINROLE));
        }
/**
 * Is this user active?
 */
        public function isActive() : bool
        {
            return $this->bean->active != 0;
        }
/**
 * Is this user confirmed?
 */
        public function isConfirmed() : bool
        {
            return $this->bean->confirm != 0;
        }
/**
 * Is this user a developer?
 */
        public function isDeveloper() : bool
        {
            return \is_object($this->hasRole(FW::FWCONTEXT, FW::DEVELROLE));
        }
/**
 * Set the user's password
 */
        public function setPW(string $password) : void
        {
            $this->bean->password = \password_hash($password, PASSWORD_DEFAULT);
            \R::store($this->bean);
        }
/**
 * Check a password
 */
        public function pwOK(string $password) : bool
        {
            return \password_verify($password, $this->bean->password);
        }
/**
 * Set the email confirmation flag
 */
        public function doConfirm() : void
        {
            $this->bean->active = 1;
            $this->bean->confirm = 1;
            \R::store($this->bean);
        }
/**
 * Generate a JWT token for this user that can be used as a unique id from a phone.
 *
 * @param $url    The URL of the site
 * @param $device  Currently not used!!
 *
 * @psalm-suppress UnusedVariable
 * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter
 */
        public function makeToken(string $url, string $device = '') : string
        {
            $token = (object) ['iss' => $url, 'iat' => \idate('U'), 'sub' => $this->bean->getID()];
            /** @psalm-suppress UndefinedClass - JWT is not currently included in the psalm checks... */
            return \Framework\Utility\JWT\JWT::encode($token, FW::AUTHKEY);
        }
/**
 * Setup for an edit
 *
 * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter
 */
        public function startEdit(Context $context, array $rest) : void
        {
            // nothing to do
        }
/**
 * Handle an edit form for this user
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
/**
 * Return the user's 2FA secret
 */
        public function secret() : string
        {
            return $this->bean->secret;
        }
    }
?>
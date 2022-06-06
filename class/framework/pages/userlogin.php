<?php
/**
 * Definition of Userlogin class
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2012-2021 Newcastle University
 * @package Framework
 * @subpackage SystemPages
 */
    namespace Framework\Pages;

    use \Config\Config;
    use \Config\Framework as FW;
    use \Framework\Local;
    use \R;
    use \Support\Context;
/**
 * A class to handle the /login, /logout, /register, /forgot and /resend actions
 */
    final class UserLogin extends \Framework\SiteAction
    {
        use \Support\Login;
/**
 * Find a user based on either a login or an email address
 *
 * @used-by \Support\Login
 *
 * @param $lg   A username or email address
 */
        public static function eorl(string $lg) : ?\RedBeanPHP\OODBBean
        {
            return R::findOne(FW::USER, (\filter_var($lg, FILTER_VALIDATE_EMAIL) !== FALSE ? 'email' : 'login').'=?', [$lg]);
        }
/**
 * Make a confirmation code and store it in the database
 */
        private function makeCode(Context $context, \RedBeanPHP\OODBBean $user, string $kind) : string
        {
            R::trashAll($user->all()->{'own'.\ucfirst(FW::CONFIRM).'List'});
            $code = \hash('sha256', $user->getID().$user->email.$user->login.\uniqid());
            $conf = R::dispense(FW::CONFIRM);
            $conf->code = $code;
            $conf->issued = $context->utcnow();
            $conf->kind = $kind;
            $conf->{FW::USER} = $user;
            R::store($conf);
            return $code;
        }
/**
 * Mail a confirmation code
 *
 * @internal
 */
        private function sendConfirm(Context $context, \RedBeanPHP\OODBBean $user) : void
        {
            $local = $context->local();
            $code = $this->makeCode($context, $user, 'C');
            $local->sendmail([$user->email],
                'Please confirm your email address for '.$local->configval('sitename'),
                "Please use this link to confirm your email address\n\n\n".
                $local->configval('siteurl').'/confirm/'.$code."\n\n\nThank you,\n\n The ".$local->configval('sitename')." Team\n\n",
                '',
                ['From' => $local->configval('noreply')]
            );
        }
/**
 * Mail a password reset code
 *
 * @internal
 */
        private function sendReset(Context $context, \RedBeanPHP\OODBBean $user) : void
        {
            $local = $context->local();
            $code = $this->makeCode($context, $user, 'P');
            $local->sendmail([$user->email], 'Reset your '.$local->configval('sitename').' password',
                "Please use this link to reset your password\n\n\n".
                $local->configval('siteurl').'/forgot/'.$code."\n\n\nThank you,\n\n The ".$local->configval('sitename')." Team\n\n",
                '',
                ['From' => $local->configval('sitenoreply')]
            );
        }
/**
 * Handle a login
 *
 * @uses \Support\Login
 */
        private function login(Context $context) : string
        {
            $local = $context->local();
            $local->addval('register', Config::REGISTER);
            if ($context->hasuser())
            { // already logged in
                $local->message(Local::WARNING, 'Please log out before trying to login');
            }
            else
            {
                $this->checkLogin($context);
            }
            return '@content/login.twig';
        }
/**
 * handle a registration
 *
 * @throws \Framework\Exception\BadOperation
 */
        private function register(Context $context) : string
        {
            if (!Config::REGISTER)
            {
                $context->divert('/');
                /* NOT REACHED */
            }
            if ($context->web()->isPost())
            {
                if ($context->hasUser())
                {
                    throw new \Framework\Exception\BadOperation('Cannot register while logged in');
                }
                $fdt = $context->formdata('post');
                $login = $fdt->fetch('login', '');
                if ($login !== '')
                {
                    $errmess = [];
                    $x = R::findOne(FW::USER, 'login=?', [$login]);
                    if (!\is_object($x))
                    {
                        $pw = $fdt->mustFetch('password');
                        $rpw = $fdt->mustFetch('repeat');
                        $email = $fdt->mustFetch('email');
                        if ($pw != $rpw)
                        {
                            $errmess[] = 'The passwords do not match';
                        }
                        $cmodel = FW::USERMCLASS;
                        if (!$cmodel::pwValid($pw))
                        {
                            $errmess[] = 'The password does not meet the specification';
                        }
                        if (\preg_match('/[^a-z0-9]/i', $login))
                        {
                            $errmess[] = 'Your username can only contain letters and numbers';
                        }
                        if (!\filter_var($email, FILTER_VALIDATE_EMAIL))
                        {
                            $errmess[] = 'Please provide a valid email address';
                        }
                        if (empty($errmess))
                        { // no errors
                            $x = R::dispense(FW::USER);
                            $x->login = $login;
                            $x->email = $email;
                            $x->confirm = 0;
                            $x->active = 1;
                            $x->joined = $context->utcnow();
                            R::store($x);
                            $x->setpw($pw);
                            $rerr = $x->register($context); // do any extra registration
                            if (empty($rerr))
                            {
                                $this->confmessage($context, $x);
                            }
                            else
                            { // extra registration failed
                                R::trash($x); // delete the user object
                                $errmess = \array_merge($errmess, $rerr);
                            }
                        }
                    }
                    else
                    {
                        $errmess[] = 'That login is not available';
                    }
                    if (!empty($errmess))
                    {
                        $context->local()->message(Local::ERROR, $errmess);
                        $context->local()->addval([
                            'login' => $login,
                            'email' => $email, // @phan-suppress-current-line PhanPossiblyUndeclaredVariable
                        ]);
                    }
                }
                else
                {
                    $context->local()->message(Local::ERROR, 'Please complete the registration form');
                }
            }
            return '@content/register.twig';
        }
/**
 * Handle confirmation
 *
 * @internal
 */
        private function confmessage(Context $context, \RedBeanPHP\OODBBean $user) : void
        {
            if (\Config\Framework::constant('CONFEMAIL', FALSE))
            {
                $this->sendconfirm($context, $user);
                $msg = 'A confirmation link has been sent to your email address';
            }
            else
            {
                $user->confirm = 1;
                R::store($user);
                $msg = 'You have successfully registered with the system';
            }
            $context->local()->message(Local::MESSAGE, $msg);
            $context->local()->addval('regok', TRUE);
        }
/**
 * Handle things to do with email address confirmation
 */
        private function confirm(Context $context) : string
        {
            if ($context->hasuser())
            { // logged in, so this stupid....
                $context->divert('/');
                /* NOT REACHED */
            }
            $local = $context->local();
            $tpl = '@users/reset.twig';
            $rest = $context->rest();
            if ($rest[0] === '' || $rest[0] == 'resend')
            { // asking for resend
                $lg = $context->formdata('post')->fetch('eorl', '');
                if ($lg === '')
                { // show the form
                    return '@users/resend.twig';
                }
                // now handle the form
                $user = self::eorl($lg);
                if (!\is_object($user))
                {
                    $local->message(Local::ERROR, 'Sorry, there is no user with that name or email address.');
                }
                elseif ($user->confirm)
                {
                    $local->message(Local::WARNING, 'Your email address has already been confirmed.');
                }
                else
                {
                    $this->sendconfirm($context, $user);
                    $local->message(Local::MESSAGE, 'A new confirmation link has been sent to your email address.');
                }
            }
            else
            { // confirming the email
                $x = R::findOne(FW::CONFIRM, 'code=? and kind=?', [$rest[0], 'C']);
                if (\is_object($x))
                {
                    $interval = (new \DateTime($context->utcnow()))->diff(new \DateTime($x->issued));
                    if ($interval->days <= 3)
                    {
                        $x->{FW::USER}->doconfirm();
                        R::trash($x);
                        $local->message(Local::MESSAGE, 'Thank you for confirming your email address. You can now login.');
                    }
                    else
                    {
                        $local->message(Local::ERROR, 'Sorry, that code has expired!');
                    }
                }
            }
            return $tpl;
        }
/**
 * Handle things to do with password reset
 *
 * @param Context   $context    The context object for the site
 */
        public function forgot(Context $context) : string
        {
            $local = $context->local();
            $tpl = '@users/reset.twig';
            if ($context->hasuser())
            { // logged in, so this stupid....
                $local->addval('done', TRUE);
                $local->message(Local::WARNING, 'You are already logged in');
                return $tpl;
            }
            $fdt = $context->formdata('post');
            $rest = $context->rest();
            if ($rest[0] === '')
            {
                $lg = $fdt->fetch('eorl', '');
                if ($lg !== '')
                {
                    $user = self::eorl($lg);
                    if (is_object($user))
                    {
                        $this->sendreset($context, $user);
                        $local->message(Local::MESSAGE, 'A password reset link has been sent to your email address.');
                        $local->addval('done', TRUE);
                    }
                    else
                    {
                        $local->message(Local::WARNING, 'Sorry, there is no user with that name or email address.');
                    }
                }
            }
            elseif ($rest[0] === 'reset')
            {
                $tpl = '@users/pwreset.twig';
                $user = $fdt->mustFetchBean('uid', FW::USER);
                $code = $fdt->mustFetch('code');
                $xc = R::findOne(FW::CONFIRM, 'code=? and kind=?', [$code, 'P']);
                if (is_object($xc) && $xc->{FW::USER}->equals($user))
                {
                    $interval = (new \DateTime($context->utcnow()))->diff(new \DateTime($xc->issued));
                    if ($interval->days <= 1)
                    {
                        $pw = $fdt->mustFetch('password');
                        if ($pw === $fdt->mustFetch('repeat'))
                        {
                            $xc->{FW::USER}->setpw($pw);
                            R::trash($xc);
                            $local->message(Local::MESSAGE, 'You have reset your password. You can now login.');
                            $local->addval('done', TRUE);
                        }
                        else
                        {
                            $local->message(Local::ERROR, 'Sorry, the passwords do not match!');
                        }
                    }
                    else
                    {
                        $local->message(Local::ERROR, 'Sorry, that code has expired!');
                    }
                }
                else
                {
                    $context->divert('/');
                    /* NOT REACHED */
                }
            }
            else
            {
                $x = R::findOne(FW::CONFIRM, 'code=? and kind=?', [$rest[0], 'P']);
                if (\is_object($x))
                {
                    $interval = (new \DateTime($context->utcnow()))->diff(new \DateTime($x->issued));
                    if ($interval->days <= 1)
                    {
                        $local->addval([
                            'pwuser' => $x->{FW::USER},
                            'code'   => $x->code,
                        ]);
                        $tpl = '@users/pwreset.twig';
                    }
                    else
                    {
                        $local->message(Local::ERROR, 'Sorry, that code has expired!');
                    }
                }
            }
            return $tpl;
        }
/**
 * Login success so set up session
 */
        private function loginSession(Context $context, \RedBeanPHP\OODBBean $user, string $page) : never
        {
            if (\session_status() !== \PHP_SESSION_ACTIVE)
            { // no session started yet
                \session_start(['name' => \Config\Config::SESSIONNAME, 'cookie_path' => $context->local()->base().'/']);
            }
            $_SESSION['userID'] = $user->getID();
            $dpage = $context->local()->config('defaultpage');
            $context->divert($page === '' ? ($dpage !== NULL ? $dpage->value : '/') : $page); // success - divert to default page or requested page
            /* NOT REACHED */
        }
/**
 * Handle TwoFA
 */
        private function twofa(Context $context) : string
        {

            if ($context->web()->isPost())
            {
                $fdt = $context->formdata('post');
                $user = R::findOne(FW::USER, 'code2fa=?', [$fdt->mustFetch('hash')]);
                if (!is_object($user))
                {
                    $context->divert('/login/');
                }
                if (\Framework\Support\Security::getInstance()->check2FA($user->secret(), $fdt->mustFetch('validator')))
                {
                    $user->code2fa = '';
                    R::store($user);
                    $this->loginSession($context, $user, $fdt->fetch('goto'));
                    /* NOT REACHED */
                }
                $context->local()->message(\Framework\Local::ERROR, 'Invalid code - please try again');
            }
            $fget = $context->formdata('get');
            $hash = $fget->fetch('xx');
            if ($hash === '' || !is_object(R::findOne(FW::USER, 'code2fa=?', [$hash])))
            {
                $context->divert('/login/');
                /* NOT REACHED */
            }
            $context->local()->addval([
                'hash' => $hash,
                'goto' => $fget->fetch('goto'),
            ]);
            return '@content/twofa.twig';
        }
/**
 * Handle /login /logout /register /forgot /confirm /twofa
 *
 * @param Context  $context    The context object for the site
 */
        public function handle(Context $context) : array|string
        {
            $action = $context->action(); // the validity of the action value has been checked before we get here
            \assert(\method_exists($this, $action));
            return $this->$action($context);
        }
    }
?>
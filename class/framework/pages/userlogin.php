<?php
/**
 * Definition of Userlogin class
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2012-2019 Newcastle University
 */
    namespace Framework\Pages;

    use \Config\Framework as FW;
    use \Config\Config as Config;
    use \Framework\Local as Local;
    use \Support\Context as Context;
    use \R as R;
/**
 * A class to handle the /login, /logout, /register, /forgot and /resend actions
 *
 */
    class UserLogin extends \Framework\SiteAction
    {
        use \Support\Login;
/**
 * Find a user based on either a login or an email address
 *
 * @param string	$lg     A username or email address
 *
 * @return ?\RedBeanPHP\OODBBean	The user or NULL
 */
        private static function eorl(string $lg) : ?\RedBeanPHP\OODBBean
        {
            return R::findOne(FW::USER, (filter_var($lg, FILTER_VALIDATE_EMAIL) !== FALSE ? 'email' : 'login').'=?', [$lg]);
        }
/**
 * Make a confirmation code and store it in the database
 *
 * @param \Support\Context	$context The context bean
 * @param object	$bn	 A User bean
 * @param string	$kind
 *
 * @return string
 */
        private function makecode(Context $context, $bn, string $kind) : string
        {
            R::trashAll(R::find(FW::CONFIRM, 'user_id=?', [$bn->getID()]));
            $code = hash('sha256', $bn->getID.$bn->email.$bn->login.uniqid());
            $conf = R::dispense(FW::CONFIRM);
            $conf->code = $code;
            $conf->issued = $context->utcnow();
            $conf->kind = $kind;
            $conf->user = $bn;
            R::store($conf);
            return $code;
        }
/**
 * Mail a confirmation code
 *
 * @param \Support\Context	$context The context object
 * @param object	$bn	 A User bean
 *
 * @return void
 */
        private function sendconfirm(Context $context, $bn) : void
        {
            $local = $context->local();
            $code = $this->makecode($context, $bn, 'C');
            mail($bn->email, 'Please confirm your email address for '.$local->configval('sitename'),
                "Please use this link to confirm your email address\n\n\n".
                $local->configval('siteurl').'/confirm/'.$code."\n\n\nThank you,\n\n The ".$local->configval('sitename')." Team\n\n",
                'From: '.$local->configval('noreply')
            );
        }
/**
 * Mail a password reset code
 *
 * @param \Support\Context	$context The context object
 * @param object	$bn	 A User bean
 *
 * @return void
 */
        private function sendreset(Context $context, $bn) : void
        {
            $local = $context->local();
            $code = $this->makecode($context, $bn, 'P');
            mail($bn->email, 'Reset your '.$local->configval('sitename').' password',
                "Please use this link to reset your password\n\n\n".
                $local->configval('siteurl').'/forgot/'.$code."\n\n\nThank you,\n\n The ".$local->configval('sitename')." Team\n\n",
                'From: '.$local->configval('sitenoreply')
            );
        }
/**
 * Handle a login
 *
 * @param \Support\Context	$context	The context object for the site
 *
 * @uses \Support\Login
 *
 * @return string	A template name
 */
        public function login(Context $context) : string
        {
            $local = $context->local();
            $local->addval('register', Config::REGISTER);
            if ($context->hasuser())
            { # already logged in
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
 * @param \Support\Context	$context	The context object for the site
 *
 * @return string	A template name
 */
        public function register(Context $context) : string
        {
            $fdt = $context->formdata();
            $login = $fdt->post('login', '');
            if ($login !== '')
            {
                $errmess = [];
                $x = R::findOne('user', 'login=?', [$login]);
                if (!is_object($x))
                {
                    $pw = $fdt->mustpost('password');
                    $rpw = $fdt->mustpost('repeat');
                    $email = $fdt->mustpost('email');
                    if ($pw != $rpw)
                    {
                        $errmess[] = 'The passwords do not match';
                    }
                    if (!\Model\User::pwValid($pw))
                    {
                        $errmess[] = 'The passwords do not match';
                    }
                    if (preg_match('/[^a-z0-9]/i', $login))
                    {
                        $errmess[] = 'Your username can only contain letters and numbers';
                    }
                    if (!filter_var($email, FILTER_VALIDATE_EMAIL))
                    {
                        $errmess[] = 'Please provide a valid email address';
                    }
                    if (empty($errmess))
                    {
                        $x = R::dispense('user');
                        $x->login = $login;
                        $x->email = $email;
                        $x->confirm = 0;
                        $x->active = 1;
                        $x->joined = $context->utcnow();
                        R::store($x);
                        $rerr = $x->register($context); // do any extra registration
                        if (empty($rerr))
                        {
                            if (!defined('\\Config\\Config::CONFEMAIL') || \constant('\\Config\\Config::CONFEMAIL'))
                            {
                                $this->sendconfirm($context, $x);
                                $context->local()->addval('regok', 'A confirmation link has been sent to your email address.');
                            }
                            else
                            {
                                $x->confirm = 1;
                                R::store($x);
                            }
                        }
                        else
                        { // extra registration failed
                            \R::trash($x); // delete the user object
                            $errmess = array_merge($errmess, $rerr);
                        }
                    }
                }
                else
                {
                    $errmess[] = 'That user name is already in use';
                }
                if (!empty($errmess))
                {
                    $context->local()->message(Local::ERROR, $errmess);
                }
            }
            return '@content/register.twig';
        }
/**
 * Handle things to do with email address confirmation
 *
 * @param \Support\Context	$context	The context object for the site
 *
 * @return string	A template name
 */
        public function confirm(Context $context) : string
        {
            if ($context->hasuser())
            { # logged in, so this stupid....
                $context->divert('/');
            }
            $local = $context->local();
            $tpl = 'index.twig';
            $rest = $context->rest();
            if ($rest[0] === '' || $rest[0] == 'resend')
            { # asking for resend
                $lg = $context->formdata()->post('eorl', '');
                if ($lg === '')
                { # show the form
                    $tpl = '@users/resend.twig';
                }
                else
                { # handle the form
                    $user = self::eorl($lg);
                    if (!is_object($user))
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
            }
            else
            { # confirming the email
                $x = R::findOne(FW::CONFIRM, 'code=? and kind=?', [$rest[0], 'C']);
                if (is_object($x))
                {
                    $interval = (new \DateTime($context->utcnow()))->diff(new \DateTime($x->issued));
                    if ($interval->days <= 3)
                    {
                        $x->user->doconfirm();
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
 * @param \Support\Context	$context	The context object for the site
 *
 * @return string	A template name
 */
        public function forgot(Context $context) : string
        {
            $local = $context->local();
            if ($context->hasuser())
            { # logged in, so this stupid....
                $local->addval('done', TRUE);
                $local->message(Local::WARNING, 'You are already logged in');
                return '@users/reset.twig';
            }
            $fdt = $context->formdata();
            $tpl = 'index.twig';
            $rest = $context->rest();
            if ($rest[0] === '')
            {
                $lg = $fdt->post('eorl', '');
                $tpl = '@users/reset.twig';
                if ($lg !== '')
                {
                    $user = self::eorl($lg);
                    $tpl = '@users/reset.twig';
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
                $user = $fdt->mustpostbean('uid', 'user');
                $code = $fdt->mustpost('code');
                $xc = R::findOne(FW::CONFIRM, 'code=? and kind=?', [$code, 'P']);
                if (is_object($xc) && $xc->user_id == $user->getID())
                {
                    $interval = (new \DateTime($context->utcnow()))->diff(new \DateTime($xc->issued));
                    if ($interval->days <= 1)
                    {
                        $pw = $fdt->mustpost('password');
                        if ($pw === $fdt->mustpost('repeat'))
                        {
                            $xc->user->setpw($pw);
                            R::trash($xc);
                            $local->message(Local::MESSAGE, 'You have reset your password. You can now login.');
                            $tpl = 'index.twig';
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
                }
            }
            else
            {
                $x = R::findOne(FW::CONFIRM, 'code=? and kind=?', [$rest[0], 'P']);
                if (is_object($x))
                {
                    $interval = (new \DateTime($context->utcnow()))->diff(new \DateTime($x->issued));
                    if ($interval->days <= 1)
                    {
                        $local->addval('pwuser', $x->user);
                        $local->addval('code', $x->code);
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
 * Handle /login /logout /register /forgot /confirm
 *
 * @param \Support\Context	$context	The context object for the site
 *
 * @return string	A template name
 */
        public function handle(Context $context)
        {
            $action = $context->action(); # the validity of the action value has been checked before we get here
            assert(method_exists($this, $action));
            return $this->$action($context);
        }
    }
?>

<?php
/**
 * A trait that allows extending the UserLogin class for different authentication processes
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2019-2020 Newcastle University
 * @package Framework
 */
    namespace Support;

/**
 * Allows developers to change the way logins and logouts are handled.
 */
    trait Login
    {
/**
 * Process the login form
 *
 * Different kinds of logins might be required. This is the default
 * that uses the local database table.
 *
 * @used-by \Framework\Pages\UserLogin
 *
 * @param Context $context
 *
 * @return bool
 */
        public function checkLogin(Context $context) : bool
        {
            $fdt = $context->formdata('post');
            if (($lg = $fdt->fetch('login', '')) !== '')
            {
                $page = $fdt->fetch('goto', '');
                $pw = $fdt->fetch('password', '');
                if ($pw !== '')
                {
                    $user = \Framework\Pages\UserLogin::eorl($lg); // use either a login name or the email address - see framework/pages/userlogin.php
                    if (is_object($user) && $user->pwok($pw) && $user->confirm)
                    {
                        if (session_status() !== PHP_SESSION_ACTIVE)
                        { // no session started yet
                            session_start(['name' => \Config\Config::SESSIONNAME, 'cookie_path' => $context->local()->base().'/']);
                        }
                        $_SESSION['user'] = $user;
                        $context->divert($page === '' ? '/' : $page); // success - divert to home page
                        /* NOT REACHED */
                    }
                }
                $context->local()->message(\Framework\Local::MESSAGE, 'Please try again.');
                return FALSE;
            }
            $context->local()->addval('goto', $context->formdata('get')->fetch('goto', ''));
            return TRUE;
        }
/**
 * Handle a logout
 *
 * Clear all the session material if any and then divert to the /login page
 *
 * Code taken directly from the PHP session_destroy manual page
 *
 * @link    http://php.net/manual/en/function.session-destroy.php
 *
 * @param Context   $context    The context object for the site
 *
 * @used-by \Framework\Pages\UserLogin
 *
 * @return void
 * @psalm-suppress PossiblyUnusedMethod
 */
        public function logout(Context $context) : void
        {
            $_SESSION = []; // Unset all the session variables.

            // If it's desired to kill the session, also delete the session cookie.
            // Note: This will destroy the session, and not just the session data!
            if (ini_get('session.use_cookies'))
            {
                $params = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000,
                    $params['path'], $params['domain'], $params['secure'], $params['httponly']
                );
            }
            if (session_status() === PHP_SESSION_ACTIVE)
            { // no session started yet
                session_destroy(); // Finally, destroy the -session.
            }
            $context->divert('/');
            /* NOT REACHED */
        }
    }
?>
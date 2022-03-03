<?php
/**
 * Profile page class
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2021-2022 Newcastle University
 * @package Framework
 * @subpackage UserPages
 */
    namespace Pages;

    use \Support\Context;
/**
 * A profile page class
 */
    class Profile extends \Framework\SiteAction
    {
/**
 * Handle various profile operations /
 *
 * @param Context $context    The context object for the site
 */
        public function handle(Context $context) : array|string
        {
            if ($context->web()->isPost())
            {
                $fdt = $context->formdata('post');
                $user = $context->user();
                $email = $fdt->fetch('email', '');
                $change = FALSE;
                if ($email !== '' && $email != $user->email)
                {
                    if (\filter_var($email, \FILTER_VALIDATE_EMAIL) !== FALSE)
                    {
                        $user->email = $email;
                        $change = TRUE;
                    }
                    else
                    {
                        $context->local()->message(\Framework\Local::ERROR, 'Please enter a valid email address');
                    }
                }
                $pw = $fdt->fetch('password', '');
                $rpw = $fdt->fetch('repeat', '');
                if ($pw !== '')
                {
                    if ($pw == $rpw)
                    {
                        $user->setpw($pw);
                        $change = TRUE;
                    }
                    else
                    {
                        $context->local()->message(\Framework\Local::ERROR, 'Passwords do not match');
                    }
                }
                if ($change)
                {
                    \R::store($user);
                    $context->local()->message(\Framework\Local::MESSAGE, 'Done');
                }
            }
            return '@content/profile.twig';
        }
    }
?>
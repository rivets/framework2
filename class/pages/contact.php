<?php
 /**
  * Class for handling contact messages
  *
  * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
  * @copyright 2012-2020 Newcastle University
  * @copyright 2012-2019 Newcastle University
  * @package Framework
  * @subpackage UserPages
  */
    namespace Pages;

    use \Config\Config;
    use \Framework\Local;
    use \Support\Context;
/**
 * A class that contains code to implement a contact page
 * @psalm-suppress UnusedClass
 */
    class Contact extends \Framework\SiteAction
    {
/**
 * Handle various contact operations /contact
 *
 * @param Context   $context    The context object for the site
 *
 * @return string   A template name
 */
        public function handle(Context $context)
        {
            $fd = $context->formdata('post');
            if (($msg = $fd->fetch('message', '')) !== '')
            { // there is a post
                $subj = $fd->fetch('subject', '');
                $sender = $fd->fetch('sender', '', FILTER_VALIDATE_EMAIL);
                if ($subj !== '' && $sender !== '' /* && $fd->recaptcha() */)
                {
                    $context->local()->sendmail(
                        [Config::SYSADMIN],
                        \Config\Config::SITENAME.': '.$subj,
                        $sender.PHP_EOL.PHP_EOL.$msg
                    );
                    $context->local()->message(Local::MESSAGE, 'Thank you. We will be in touch as soon as possible.');
                }
                else
                {
                    $context->local()->message(Local::ERROR, 'Please fill out the form with the requested information.');
                }
            }
            return '@content/contact.twig';
        }
    }
?>
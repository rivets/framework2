<?php
 /**
  * Class for handling contact messages
  *
  * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
  * @copyright 2012-2019 Newcastle University
  */
    namespace Pages;

    use \Config\Config as Config;
    use \Framework\Local as Local;
    use \Support\Context as Context;

/**
 * A class that contains code to implement a contact page
 */
    class Contact extends \Framework\SiteAction
    {
/**
 * Handle various contact operations /contact
 *
 * @param \Support\Context	$context	The context object for the site
 *
 * @return string	A template name
 */
        public function handle(Context $context)
        {
            $fd = $context->formdata();
            if (($msg = $fd->post('message', '')) !== '')
            { # there is a post
                $subj = $fd->post('subject', '');
                $sender = $fd->filterpost('subject', '', FILTER_VALIDATE_EMAIL);
                if ($subj !== '' && $sender !== '' /* && $fd->recaptcha() */)
                {
                    $mail = new \Framework\Utility\FMailer;
                    $mail->setFrom(Config::SITENOREPLY);
                    $mail->addReplyTo(Config::SITENOREPLY);
                    $mail->addAddress(Config::SYSADMIN);
                    /** @psalm-suppress PossiblyNullOperand PossiblyNullPropertyFetch **/
                    $mail->Subject = $context->local()->config('SITENAME')->value.': '.$subj;
                    /** @psalm-suppress UndefinedPropertyAssignment */
                    $mail->Body= $sender.PHP_EOL.PHP_EOL.$msg;
                    $mail->send();
    
                    //mail(Config::SYSADMIN, $context->local()->config('SITENAME').': '., $sender.PHP_EOL.PHP_EOL.$msg);
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

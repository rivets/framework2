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
                $mail = new \Framework\Utility\FMailer;
                $mail->setFrom(Config::SITENOREPLY);
                $mail->addReplyTo(Config::SITENOREPLY);
                $mail->addAddress(Config::SYSADMIN);

                $mail->Subject = $fd->post('subject', 'No Subject');
                $mail->Body= $msg;
                $mail->send();

                //mail(Config::SYSADMIN, $fd->post('subject', 'No Subject'), $fd->post('sender', 'No Sender').PHP_EOL.PHP_EOL.$msg);
                $context->local()->message(Local::MESSAGE, 'Thank you. We will be in touch as soon as possible.');
            }
            return '@content/contact.twig';
        }
    }
?>

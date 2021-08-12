<?php
/**
 * Contains definition of LocalBase class
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2012-2021 Newcastle University
 * @package Framework
 */
    namespace Framework\Support;

    use \Config\Config;
/**
 * This is a class that maintains values about the local environment and does error handling
 */
    class LocalBase
    {
        use \Framework\Utility\Singleton;

        protected $basepath         = ''; // The absolute path to the site directory
        protected string $basedname = ''; // The name of the site directory
        protected ?ErrorHandler $errorHandler     = NULL;
/**
 * Send mail if possible
 *
 * @param string[]   $to       An array of people to send to.
 * @param string     $subject  The subject
 * @param string     $msg      The message - if $alt is not empty then this is assumed to be HTML.
 * @param string     $alt      The alt message - plain text
 * @param string[]   $other    From, cc, bcc etc. etc.
 * @param string[]   $attach   Any Attachments
 *
 * @return string
 */
        public function sendmail(array $to, string $subject, string $msg, string $alt = '', array $other = [], array $attach = []) : string
        {
            /** @psalm-suppress RedundantCondition */
            if (Config::USEPHPM || ini_get('sendmail_path') !== '')
            {
                $mail = new \Framework\Utility\FMailer();
                try
                {
                    $mail->setFrom($other['from'] ?? Config::SITENOREPLY);
                    if (isset($other['replyto']))
                    {
                        $mail->addReplyTo($other['replyto']);
                    }
                    if (isset($other['cc']))
                    {
                        foreach ($other['cc'] as $cc)
                        {
                            $mail->addCC($cc);
                        }
                    }
                    if (isset($other['bcc']))
                    {
                        foreach ($other['bcc'] as $cc)
                        {
                            $mail->addBCC($cc);
                        }
                    }
                    foreach ($to as $em)
                    {
                        $mail->addAddress($em);
                    }
                    $mail->Subject = $subject;
                    if ($alt !== '')
                    {
                        $mail->AltBody= $alt;
                        $mail->isHTML(TRUE);
                    }
                    else
                    {
                        $mail->isHTML(FALSE);
                    }
                    $mail->msgHTML($msg);
                    foreach ($attach as $fl)
                    {
                        $mail->addAttachment($fl);
                    }
                    return $mail->send() ? '' : $mail->ErrorInfo;
                }
                catch (\Throwable $e)
                {
                    return $mail->ErrorInfo;
                }
            }
            return 'No mailer configured';
        }
/**
 * Allow system to ignore errors
 *
 * This always clears the wasignored flag
 *
 * @param bool    $ignore    If TRUE then ignore the error otherwise stop ignoring
 *
 * @return bool    The last value of the wasignored flag
 */
        public function eIgnore(bool $ignore) : bool
        {
            return $this->errorHandler->eIgnore($ignore);
        }
/**
 * Join the arguments with DIRECTORY_SEPARATOR to make a path name
 *
 * @return string
 */
        public function makepath()
        {
            return implode(DIRECTORY_SEPARATOR, func_get_args());
        }
/**
 * Join the arguments with DIRECTORY_SEPARATOR to make a path name and prepend the path to the base directory
 *
 * @return string
 */
        public function makebasepath()
        {
            return $this->basedir().DIRECTORY_SEPARATOR.implode(DIRECTORY_SEPARATOR, func_get_args());
        }
/**
 * Return a path to the assets directory suitable for use in links
 *
 * @return string
 */
        public function assets()
        {
            return $this->base().'/assets'; // for HTML so the / is OK to use here
        }
/**
 * Return a filesystem path to the assets directory
 *
 * @return string
 */
        public function assetsdir()
        {
            return $this->basedir().DIRECTORY_SEPARATOR.'assets';
        }
/**
 * Return the name of the directory for this site
 *
 * @return string
 */
        public function base()
        {
            return $this->basedname;
        }
/**
 * Return the path to the directory for this site
 *
 * @return string
 */
        public function basedir()
        {
            return $this->basepath;
        }
/**
 * Remove the base component from a URL
 *
 * Note that this will fail if the base name contains a '#' character!
 * The installer tests for this and issues an error when run.
 *
 * @param string        $url
 *
 * @return string
 */
        public function debase(string $url)
        {
            return $this->base() !== '' ? preg_replace('#^'.$this->base().'#', '', $url) : $url;
        }
/**
 * Check to see if non-admin users are being excluded
 *
 * @param bool $admin
 *
 * @return void
 */
        public function adminOnly(bool $admin) : void
        {
            $offl = $this->makebasepath('admin', 'adminonly');
            if (file_exists($offl) && !$admin)
            { // go offline before we try to do anything else as we are not an admin
                $this->errorHandler->earlyFail('OFFLINE', file_get_contents($offl), FALSE);
                /* NOT REACHED */
            }
        }
    }
?>
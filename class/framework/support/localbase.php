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

        protected string $basePath              = ''; // The absolute path to the site directory
        protected string $baseDName             = ''; // The name of the site directory
        protected ?ErrorHandler $errorHandler   = NULL;
/**
 * Send mail if possible
 *
 * @param string[]   $to       An array of people to send to.
 * @param string     $subject  The subject
 * @param string     $msg      The message - if $alt is not empty then this is assumed to be HTML.
 * @param string     $alt      The alt message - plain text
 * @param mixed[]    $other    From, cc, bcc etc. etc.
 * @param string[]   $attach   Any Attachments
 */
        public function sendmail(array $to, string $subject, string $msg, string $alt = '', array $other = [], array $attach = []) : string
        {
            $mail = NULL;
            /** @psalm-suppress RedundantCondition */
            if (Config::USEPHPM || \ini_get('sendmail_path') !== '')
            {
                try
                {
                    $mail = new \Framework\Utility\FMailer();
                    $mail->isSMTP();
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
                catch (\Throwable)
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
 */
        public function eIgnore(bool $ignore) : bool
        {
            return $this->errorHandler->eIgnore($ignore);
        }
/**
 * Join the arguments with DIRECTORY_SEPARATOR to make a path name
 */
        public function makePath(...$args) : string
        {
            return \implode(DIRECTORY_SEPARATOR, $args);
        }
/**
 * Join the arguments with DIRECTORY_SEPARATOR to make a path name and prepend the path to the base directory
 */
        public function makeBasePath(...$args) : string
        {
            return $this->baseDir().\DIRECTORY_SEPARATOR.\implode(\DIRECTORY_SEPARATOR, $args);
        }
/**
 * Return a path to the assets directory suitable for use in links
 */
        public function assets() : string
        {
            return $this->base().'/assets'; // for HTML so the / is OK to use here
        }
/**
 * Return a filesystem path to the assets directory
 */
        public function assetsDir() : string
        {
            return $this->baseDir().\DIRECTORY_SEPARATOR.'assets';
        }
/**
 * Return the name of the directory for this site
 */
        public function base() : string
        {
            return $this->baseDName;
        }
/**
 * Return the path to the directory for this site
 */
        public function baseDir() : string
        {
            return $this->basePath;
        }
/**
 * Remove the base component from a URL
 *
 * Note that this will fail if the base name contains a '#' character!
 * The installer tests for this and issues an error when run.
 *
 * @param string        $url
 */
        public function debase(string $url) : string
        {
            return $this->base() !== '' ? \preg_replace('#^'.$this->base().'#', '', $url) : $url;
        }
/**
 * Check to see if non-admin users are being excluded
 */
        public function adminOnly(bool $admin) : void
        {
            $offl = $this->makebasepath('admin', 'adminonly');
            if (\file_exists($offl) && !$admin)
            { // go offline before we try to do anything else as we are not an admin
                $this->errorHandler->earlyFail('OFFLINE', \file_get_contents($offl), FALSE);
                /* NOT REACHED */
            }
        }
    }
?>
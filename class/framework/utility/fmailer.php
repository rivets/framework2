<?php
/**
 * This provides a class that supports mailing using either the built in PHP mail() function
 * or using the SMTP part so f PHPMailer
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2017-2019 Newcastle University
 */
    namespace Framework\Utility;

    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;
    use Config\Config;
/**
 * The FMailer class
 */
    class FMailer extends PHPMailer
    {
/**
 * The constructor
 *
 * @psalm-suppress UndefinedConstant Some of the constants are not defined in some installations.
 *
 * @param bool          $exceptions    Passed to the PHPMailer constructor
 */
        public function __construct(bool $exceptions = TRUE)
        {
            parent::__construct($exceptions);
            /** @psalm-suppress TypeDoesNotContainType */
            if (Config::USEPHPM)
            {
                $this->isSMTP();
                $this->Host = Config::SMTPHOST;
                $this->Port = Config::SMTPPORT;
                if (Config::PROTOCOL !== '')
                {
                    $this->SMTPSecure = Config::PROTOCOL;
                }
                if (\Config\Config::SMTPUSER !== '')
                {
                    $this->SMTPAuth = TRUE;
                    $this->Username = Config::SMTPUSER;
                    $this->Password = Config::SMTPPW;
                }
            }
        }
    }
?>
<?php
    namespace Utility;

    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;
    use Config\Config;
/**
 * This provides a class that supports mailing using either the built in PHP mail() function
 * or using the SMTP part so f PHPMailer
 */
/**
 * The FMailer class
 */
    class FMailer extends PHPMailer
    {
        public function __construct($exceptions = TRUE)
        {
            parent::__construct($exceptions);
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
<?php
    namespace PHPMailer\PHPMailer;

    class PHPMailer
    {
/** @var string */
        public  $Host = '';
/** @var string */
        public  $Port = '';
/** @var string */
        public $SMTPSecure = '';
/** @var string */
        public $SMTPAuth = TRUE;
/** @var bool */
        public $Username = Config::SMTPUSER;
/** @var string */
        public $Password = '';
/**
 * @return bool
 */
        public function isSMTP() : boolval
        {
            return TRUE;
        }
    }
?>
<?php
    namespace PHPMailer\PHPMailer;

    class PHPMailer
    {
/** @var string */
        public $Host = '';
/** @var string */
        public $Port = '';
/** @var string */
        public $SMTPSecure = '';
/** @var bool */
        public $SMTPAuth = TRUE;
/** @var string */
        public $Username = '';
/** @var string */
        public $Password = '';
/** @var string */
        public $Subject = '';
/** @var string */
        public $AltBody = '';

        public function __construct(bool $x)
        {
        }
/**
 * @return bool
 */
        public function isSMTP() : bool
        {
            return TRUE;
        }

        public function addAddress(string $x) : void
        {
        }

        public function addReplyTo(string $x) : void
        {
        }

        public function setFrom(string $x) : void
        {
        }
        public function msgHTML(string $x) : void
        {
        }

        public function send() : void
        {
        }
    }
?>
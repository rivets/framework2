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
/** @psalm-suppress PossiblyUnusedParam */
        public function __construct(bool $_x)
        {
        }
        public function isSMTP() : bool
        {
            return TRUE;
        }
 /** @psalm-suppress PossiblyUnusedParam */
        public function addAddress(string $_x) : void
        {
        }
 /** @psalm-suppress PossiblyUnusedParam */
        public function addReplyTo(string $_x) : void
        {
        }
 /** @psalm-suppress PossiblyUnusedParam */
        public function addCC(string $_x) : void
        {
        }
 /** @psalm-suppress PossiblyUnusedParam */
        public function addBCC(string $_x) : void
        {
        }
 /** @psalm-suppress PossiblyUnusedParam */
        public function addAttachment(string $_x) : void
        {
        }
 /** @psalm-suppress PossiblyUnusedParam */
        public function isHTML(string $_x) : bool
        {
            return FALSE;
        }
 /** @psalm-suppress PossiblyUnusedParam */
        public function setFrom(string $_x) : void
        {
        }
 /** @psalm-suppress PossiblyUnusedParam */
        public function msgHTML(string $_x) : void
        {
        }
        public function send() : string
        {
            return '';
        }
    }
?>
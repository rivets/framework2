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
 /** @psalm-suppress PossiblyUnusedParam */
        public function addAddress(string $x) : void
        {
        }
 /** @psalm-suppress PossiblyUnusedParam */
        public function addReplyTo(string $x) : void
        {
        }
 /** @psalm-suppress PossiblyUnusedParam */
        public function setFrom(string $x) : void
        {
        }
 /** @psalm-suppress PossiblyUnusedParam */
        public function msgHTML(string $x) : void
        {
        }
 /** @psalm-suppress PossiblyUnusedParam */
        public function send() : void
        {
        }
    }
?>
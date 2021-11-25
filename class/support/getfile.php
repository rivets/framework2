<?php
/**
 * A trait that allows extending the GetFile page handler
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2021 Newcastle University
 * @package Framework\Support
 */
    namespace Support;

/**
 * Allows developers to handle missing files.
 */
    trait GetFile
    {
        public function noaccess() : string
        {
            throw new \Framework\Exception\Forbidden('No access');
        }

        public function missing(): string
        {
            throw new \Framework\Exception\BadValue('No such file');
        }


        public function other(string $msg): string
        {
            throw new \Framework\Exception\BadValue($msg);
        }
    }
?>
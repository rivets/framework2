<?php
/**
 * Contains the definition of Formdata COOKIE support class
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2020 Newcastle University
 * @package Framework
 * @subpackage FormData
 */
    namespace Framework\FormData;

/**
 * A class that provides helpers for accessing COOKIE data
 */
    class Cookie extends AccessBase
    {
        public function __construct()
        {
            parent::__construct(INPUT_COOKIE);
        }
    }
?>

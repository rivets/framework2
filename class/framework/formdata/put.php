<?php
/**
 * Contains the definition of Formdata PUT or PATCH support class
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2020 Newcastle University
 */
    namespace Framework\FormData;

/**
 * A class that provides helpers for accessing PUT OR PATCH form data 
 */
    class Put extends AccessBase
    {
/**
 * Constructor
 */
        public function _construct()
        {
            parent::__construct(NULL);
            /** @psalm-suppress NullArgument */
            parse_str(file_get_contents('php://input'), $this->super);
        }
    }
?>
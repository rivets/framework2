<?php
/**
 * A trait providing CSRF guard function
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2018-2022 Newcastle University
 * @package Framework\Framework\Support
 */
    namespace Framework\Support;

/**
 * User table stores info about users of the syste,
 */
    trait MakeGuard
    {
/**
 * Return the CSRFGuard inputs for inclusion in a form;
 *
 * @return string
 */
        public function guard() : string
        {
            return \Framework\Utility\CSRFGuard::getinstance()->inputs();
        }
    }
?>

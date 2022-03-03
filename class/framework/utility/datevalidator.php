<?php
/**
 * Contains definition of Date Validation class
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2019-2022 Newcastle University
 * @package Framework
 * @subpackage Utility
 */
    namespace Framework\Utility;

    class DateValidator
    {
/**
 * Check a date is valid
 *
 * @psalm-suppress PossiblyUnusedMethod
 */
        public static function check(string $date) : bool|string
        {
            $time = \strtotime($date);
            if ($time !== FALSE)
            {
                return (new \DateTime($time))->format('Y-m-d H:i:s');
            }
            return FALSE;
        }
    }
?>

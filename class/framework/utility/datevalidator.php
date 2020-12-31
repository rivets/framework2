<?php
/**
 * Contains definition of Date Validation class
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2019-2020 Newcastle University
 * @package Framework
 * @subpackage Utility
 */
    namespace Framework\Utility;

    class DateValidator
    {
/**
 * Check a date is valid
 *
 * @param string $date
 *
 * @return bool|string
 * @psalm-suppress PossiblyUnusedMethod
 */
        public static function check(string $date)
        {
            \strtotime($date);
            if ($time !== FALSE)
            {
                return DateTime($time)->format('Y-m-d H:i:s');
            }
            return FALSE;
        }
    }
?>

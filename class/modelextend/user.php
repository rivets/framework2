<?php
/**
 * A trait that allows extending the model class for the RedBean object User
 *
 * Add any new methods you want the User bean to have here.
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2018 Newcastle University
 *
 */
    namespace ModelExtend;
/**
 * User table stores info about users of the system
 */
    trait User
    {
/**
 * A function to ensure that any relevant password rules are applied when
 * setting a new password. Defaults to be not-empty. Modify this method if
 * you want to implement particular password rules. (Length is really the
 * only thing you should be testing though!)
 *
 * @param $string    $pw  The password
 *
 * @return boolean
 */
        public static function checkpw($pw)
        {
            return $pw !== '';
        }
    }
?>
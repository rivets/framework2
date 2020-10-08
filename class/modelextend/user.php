<?php
/**
 * A trait that allows extending the model class for the RedBean object User
 *
 * Add any new methods you want the User bean to have here.
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2018-2020 Newcastle University
 * @package Framework
 * @subpackage ModelExtend
 */
    namespace ModelExtend;

    use \Support\Context;
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
 * @param string    $pw  The password
 *
 * @throws \Framework\Exception\BadValue If a bad password is detected this could be thrown
 *
 * @return bool
 */
        public static function pwValid(string $pw) : bool
        {
            return $pw !== '';
        }
/**
 * Do any extra registration stuff
 *
 * Returns an array of error messages or an empty array if OK
 *
 * @param Context $context
 *
 * @return array<string>
 * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter
 */
        public function register($context) : array
        {
            return [];
        }
/**
 * Called from the "add" function when a new user is created.
 * This allows you to do any extra operations that you want to when a user is added
 *
 * @param Context $context
 *
 * @return void
 * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter
 */
        public function addData(Context $context) : void
        {
        }
/**
 * Function called when a user bean is updated - do error checking in here
 *
 * @throws \Framework\Exception\BadValue
 * @return void
 */
        public function update() : void
        {
            if (!preg_match('/^[a-z0-9]+/i', $this->bean->login))
            {
                throw new \Framework\Exception\BadValue('Invalid login name');
            }
            if (!filter_var($this->bean->email, FILTER_VALIDATE_EMAIL))
            {
                throw new \Framework\Exception\BadValue('Invalid email address');
            }
/**
 * @todo Validate the joined field. Correct date, not in the future
 */
        }
    }
?>

<?php
/**
 * A wrapper so that users dont need to edit the FWContext class in order to add features.
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2016-2020 Newcastle University
 * @package Framework
 */
    namespace Support;

/**
 * A wrapper for the real Context class that allows people to extend its functionality
 * in ways that are apporpriate for their particular website.
 */
    final class Context extends \Framework\Context
    {
/**
 * First some functions that are useful for access checking etc. Some of these are used by the Framework itself
 */
/**
 * Return TRUE if the user in the parameter is the same as the current user
 *
 * @param \RedBeanPHP\OODBBean    $user
 *
 * @return bool
 * @psalm-suppress PossiblyUnusedMethod
 */
        public function sameUser(\RedBeanPHP\OODBBean $user) : bool
        {
             /** @psalm-suppress PossiblyNullReference */
            return $this->hasuser() && $this->user()->equals($user);
        }
/**
 * Any functions that you need to be available through context.
 */
    }
?>

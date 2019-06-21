<?php
/**
 * A trait that allows extending the model class for the RedBean object Upload
 *
 * Add any new methods you want the Uploadbean to have here.
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2018-2019 Newcastle University
 *
 */
    namespace ModelExtend;
/**
 * Upload table stores info about files that have been uploaded...
 */
    trait Upload
    {
/**
 * Determine if a user can access the file
 *
 * At the moment it is either the user or any admin that is allowd. Rewrite the
 * method to add more complex access control schemes.
 *
 * @param object	$user	A user object
 *
 * @return bool
 */
        public function canaccess($user) : bool
        {
            return $this->bean->user->equals($user) || $user->isadmin();
        }
/**
 * Hook for adding extra data to a file save.
 *
 * @param int	$index	If you are reading data from an array fo files, this is the index
 *                      in the file. You may have paralleld data arrays and need this index.
 *
 * @return bool
 */
        public function addData(int $index) : void
        {
            /*
             * Your code
             */
        }
    }
?>
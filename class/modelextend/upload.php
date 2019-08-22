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
 * @param string    $op     r for read, u for update, d for delete
 *
 * @return bool
 */
        public function canaccess($user, $op = 'r') : bool
        {
            return $this->bean->user->equals($user) || $user->isadmin();
        }
/**
 * Hook for adding extra data to a file save.
 *
 * @param \Support\Context	$context	The context object for the site
 * @param int	$index	If you are reading data from an array fo files, this is the index
 *                      in the file. You may have paralleld data arrays and need this index.
 *
 * @return void
 */
        public function addData(\Support\Context $context, int $index) : void
        {
            /*
             * Your code
             */
        }
/**
 * Called when you try to trash to an upload. Do any cleanup in here
 *
 * @throws \Framework\Exceotion\Forbidden
 *
 * @return void
 */
        public function delete() : void
        {
/**** Do not change this code *****/
            $context = \Support\Context::getinstance();
            if (!$this->bean->canaccess($context->user(), 'd'))
            { // we cannot do this
                throw new \Framework\Exception\Forbidden;
            }
// Now delete the file
            unlink($context->local()->basedir().DIRECTORY_SEPARATOR.($this->bean->public ? 'public' : 'private').$this->fname); // fname starts with a /
/**** Put any cleanup code of yours after this line ****/
        }
    }
?>
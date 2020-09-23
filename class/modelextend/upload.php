<?php
/**
 * A trait that allows extending the model class for the RedBean object Upload
 *
 * Add any new methods you want the Upload bean to have here.
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2018-2020 Newcastle University
 * @package Framework
 */
    namespace ModelExtend;

    use \Support\Context;
/**
 * Upload table stores info about files that have been uploaded...
 */
    trait Upload
    {
/**
 * Determine if a user can access the file
 *
 * At the moment it is either the user or any admin that is allowed. Rewrite the
 * method to add more complex access control schemes.
 *
 * @param object   $user   A user object
 * @param string   $op     r for read, u for update, d for delete
 *
 * @return bool
 * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter
 */
        public function canaccess($user, string $op = 'r') : bool
        {
            return $this->bean->user->equals($user) || $user->isadmin();
        }
/**
 * Hook for adding extra data to a file save.
 *
 * @param Context   $context    The context object for the site
 * @param int       $index      If you are reading data from an array fo files, this is the index
 *                              in the file. You may have paralleld data arrays and need this index.
 *
 * @return void
 * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter
 */
        public function addData(Context $context, int $index) : void
        {
            /*
             * Your code goes here
             */
        }
/**
 * Hook for adding extra data to a file replace.
 *
 * @param Context    $context   The context object for the site
 * @param int        $index     If you are reading data from an array of files, this is the index
 *                              in the file. You may have parallel data arrays and need this index.
 *
 * @return void
 * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter
 */
        public function updateData(Context $context, int $index = 0) : void
        {
            /*
             * Your code goes here
             */
        }
/**
 * Hook for doing something when a file is downloaded
 *
 * @param Context    $context   The context object for the site
 *
 * @return void
 * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter
 */
        public function downloaded(Context $context) : void
        {
            /*
             * Your code goes here
             */
        }
/**
 * Automatically called by RedBean when you try to trash an upload. Do any cleanup in here
 *
 * @param Context $context
 *
 * @throws \Framework\Exception\Forbidden
 * @return void
 */
        public function delete() : void
        {
/* **** Do not change this code **** */
            $context = Context::getinstance();
            if (!$this->bean->canaccess($context->user(), 'd'))
            { // not allowed
                throw new \Framework\Exception\Forbidden('Permission Denied');
            }
// Now delete the associated file
            unlink($context->local()->basedir().$this->bean->fname);
/* **** Put any cleanup code of yours after this line **** */
            /*
             * Your code goes here
             */
        }
    }
?>

<?php
/**
 * A model class for the RedBean object Upload
 *
 * This is a Framework system class - do not edit!
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2015-2021 Newcastle University
 * @package Framework
 * @subpackage SystemModel
 */
    namespace Model;

    use \Support\Context;
/**
 * Upload table stores info about files that have been uploaded...
 * @psalm-suppress UnusedClass
 */
    final class Upload extends \RedBeanPHP\SimpleModel
    {
        use \ModelExtend\Upload;
/**
 * Return the owner of this uplaod
 */
        public function owner() : ?\RedBeanPHP\OODBBean
        {
            return $this->bean->user;
        }
/**
 * Return the value need for an HREF on a download <a> tag
 */
        public function link() : string
        {
            $base = Context::getinstance()->local()->base();
            if ($this->bean->public)
            { // the file is freely acessible by name in assets/public
                return $base.$this->bean->fname;
            }
            return $base.'/private/file/'.$this->bean->getID(); // needs access control as it is private.
        }
/**
 * Store a file
 *
 * This is the basic functionality assumed by the framework. You can adapt this by changing this function.
 * Best though if you only add functionality :-)
 *
 * @param Context               $context    The context object for the site
 * @param array                 $fileData   The relevant $_FILES element (or similar generated by FormData)
 * @param bool                  $public     If TRUE then store in the public directory
 * @param ?\RedBeanPHP\OODBBean $owner      The user who owns the upload. If NULL then  the currently logged in user
 * @param int                   $index      If there is an array of files possibly with other data, then this is the index in the array.
 *
 * @throws \Framework\Exception\InternalError
 */
        public function savefile(Context $context, array $fileData, bool $public, ?\RedBeanPHP\OODBBean $owner = NULL, int $index = 0) : bool
        {
            if ($fileData['size'] == 0 || $fileData['error'] != \UPLOAD_ERR_OK)
            { // 0 length file or there was an error so ignore
                return FALSE;
            }
            if (!$public && !\is_object($owner))
            {
                if (!$context->hasuser())
                { // no logged in user! This should never happen...
                    throw new \Framework\Exception\InternalError('No user');
                }
                $owner = $context->user();
            }
            [$dir, $pname, $fname] = $this->mkpath($context, $owner, $public, $fileData);
            $mimetype = \Framework\Support\Security::getinstance()->mimetype($fileData['tmp_name']);
            if (!\move_uploaded_file($fileData['tmp_name'], $fname))
            {
                \chdir($dir);
                throw new \Framework\Exception\InternalError('Cannot move uploaded file to '.$fname);
            }
            $this->bean->added = $context->utcnow();
            $pname[] = $fname;
            $this->bean->fname = \DIRECTORY_SEPARATOR.\implode(\DIRECTORY_SEPARATOR, $pname);
            $this->bean->filename = $fileData['name'];
            $this->bean->public = $public ? 1 : 0;
            $this->bean->user = $owner;
            $this->bean->mimetype = $mimetype;
            $this->addData($context, $index); // call the user extend function in the trait
            \R::store($this->bean);
            if (!@\chdir($dir))
            { // go back to where we were in the file system
                throw new \Framework\Exception\InternalError('Cannot chdir to '.$dir);
            }
            return TRUE;
        }
/**
 * Replace the existing uploaded file with another one
 *
 * @param Context    $context
 * @param array      $fileData  The file upload info array via FormData
 * @param int        $index     The index if this all part of an array of data
 *
 * @throws \Framework\Exception\InternalError
 */
        public function replace(Context $context, array $fileData, int $index = 0) : void
        {
            $oldfile = $this->bean->fname;
            [$dir, $pname, $fname] = $this->mkpath($context, $this->bean->user, $this->bean->public, $fileData);
            if (!\move_uploaded_file($fileData['tmp_name'], $fname))
            {
                \chdir($dir);
                throw new \Framework\Exception\InternalError('Cannot move uploaded file to '.$fname);
            }
            $this->bean->added = $context->utcnow();
            $pname[] = $fname;
            $this->bean->fname = DIRECTORY_SEPARATOR.implode(DIRECTORY_SEPARATOR, $pname);
            $this->bean->filename = $fileData['name'];
            $this->updateData($context, $index); // call the user extend function in the trait
            \R::store($this->bean);
            \unlink($context->local()->basedir().$oldfile);
            if (!\chdir($dir))
            { // go back to where we were in the file system
                throw new \Framework\Exception\InternalError('Cannot chdir to '.$dir);
            }
        }
/**
 * Make a path for a new file
 *
 * @internal
 */
        private function mkpath(Context $context, ?\RedBeanPHP\OODBBean $owner, bool $public, array $fileData) : array
        {
            $dir = \getcwd();
            \chdir($context->local()->basedir());
            $pname = \array_merge($public ? ['assets', 'public'] : ['private'], [\is_object($owner) ? $owner->getID() : '0', \date('Y'), \date('m')]);
            foreach ($pname as $pd)
            { // walk the path cding and making if needed
                $this->mkch($pd);
            }
            return [$dir, $pname, \uniqid('', TRUE).'.'.\pathinfo($fileData['name'], \PATHINFO_EXTENSION)];
        }
/**
 * Make a directory if necessary and cd into it
 *
 * @internal
 *
 * @throws \Framework\Exception\Forbidden
 */
        private static function mkch(string $directory) : void
        {
            if (!\file_exists($directory) && !\mkdir($directory, 0770))
            {
                throw new \Framework\Exception\Forbidden('Cannot mkdir '.$directory);
            }
            if (!\chdir($directory))
            {
                throw new \Framework\Exception\Forbidden('Cannot chdir to '.$directory);
            }
        }
/**
 * Generate an error message
 */
        public static function fail(Context $context, array $fileData) : void
        {
            if ($fileData['name'] !== '' && $fileData['name'] !== NULL)
            {
                if ($fileData['size'] === 0)
                {
                    $context->local()->message(\Framework\Local::ERROR, $fileData['name'].' is an empty file');
                }
                else
                {
                    switch ($fileData['error'])
                    {
                    case \UPLOAD_ERR_OK: // this shouldn't happen
                        throw new \Framework\Exception\InternalError('Should not be OK');

                    case \UPLOAD_ERR_NO_FILE:
                        $context->local()->message(\Framework\Local::ERROR, $fileData['name'].' No file sent');
                        break;

                    case \UPLOAD_ERR_INI_SIZE:
                    case \UPLOAD_ERR_FORM_SIZE:
                        $context->local()->message(\Framework\Local::ERROR, $fileData['name'].' File size exceeded');
                        break;

                    default:
                        throw new \Framework\Exception\InternalError($fileData['name'].' Unknown upload error');
                    }
                }
            }
        }
    }
?>
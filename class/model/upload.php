<?php
/**
 * A model class for the RedBean object Upload
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2015-2016 Newcastle University
 *
 */
    namespace Model;
    use Support\Context as Context;
/**
 * Upload table stores info about files that have been uploaded...
 */
    class Upload extends \RedBeanPHP\SimpleModel
    {
        use \ModelExtend\Upload;
/**
 * Return the owner of this uplaod
 *
 * @return ?object
 */
        public function owner() : ?object
        {
            return $this->bean->user;
        }
 /**
 * Make a directory if necessary and cd into it
 *
 * @param string    $dir The directory name
 *
 * @throws Cannot mkdir
 * @throws Cannot chdir
 *
 * @return void
 */
        private static function mkch(string $dir) : void
        {
            if (!file_exists($dir))
            {
                if (!@mkdir($dir, 0770))
                {
                    throw new \Exception('Cannot mkdir '.$dir);
                }
            }
            if (!@chdir($dir))
            {
                throw new \Exception('Cannot chdir '.$dir);
            }
        }
    }
?>
<?php
/**
 * A model class for the RedBean object PageRole
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2015 Newcastle University
 *
 */
    namespace Model;
/**
 * A class implementing a RedBean model for PageRole beans
 */
    class PageRole extends \RedBeanPHP\SimpleModel
    {
/**
 * Return rolenam object
 *
 * @return object
 */
        public function rolename()
        {
	        return $this->bean->rolename;
        }
/**
 * Return rolename object
 *
 * @return object
 */
        public function rolecontext()
        {
	        return $this->bean->rolecontext;
        }
    }
?>
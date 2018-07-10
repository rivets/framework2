<?php
/**
 * A model class for the RedBean object Role
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2015 Newcastle University
 *
 */
    namespace Model;

    use \Support\Context as Context;
/**
 * A class implementing a RedBean model for Role beans
 */
    class Role extends \RedBeanPHP\SimpleModel
    {
/**
 * Return rolename object
 *
 * @return object
 */
        public function rolename()
        {
	        return $this->bean->rolename;
        }
/**
 * Return rolecontext object
 *
 * @return object
 */
        public function rolecontext()
        {
	        return $this->bean->rolecontext;
        }
/**
 *  Fixes up start values
 *
 *  @param string   $start  The input value
 *
 *  @return string
 */
        private function checkstart($start)
        {
            return ($start === '' || strtolower($start) == 'now') ? Context::getinstance()->utcnow() : Context::getinstance()->utcdate($start);
        }
/**
 *  Fixes up end values
 *
 *  @param string   $end  The input value
 *
 *  @return string
 */
        private function checkend($end)
        {
            return ($end === '' || strtolower($end) == 'never') ? NULL : Context::getinstance()->utcdate($end);
        }
/**
 * Update - called when a rolename bean is stored
 */
        public function update()
        {
            $this->bean->start = $this->checkstart($this->bean->start);
            $this->bean->end = $this->checkend($this->bean->end);
        }
    }
?>

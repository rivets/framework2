<?php
/**
 * A model class for the RedBean object Role
 *
 * This is a Framework system class - do not edit!
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2015-2020 Newcastle University
 * @package Framework
 * @subpackage SystemModel
 */
    namespace Model;

    use \Support\Context;
/**
 * A class implementing a RedBean model for Role beans
 */
    class Role extends \RedBeanPHP\SimpleModel
    {
/**
 * Return rolename object
 *
 * @return \RedBeanPHP\OODBBean
 * @psalm-suppress PossiblyUnusedMethod
 */
        public function rolename() : \RedBeanPHP\OODBBean
        {
            return $this->bean->rolename;
        }
/**
 * Return rolecontext object
 *
 * @return \RedBeanPHP\OODBBean
 * @psalm-suppress PossiblyUnusedMethod
 */
        public function rolecontext() : \RedBeanPHP\OODBBean
        {
            return $this->bean->rolecontext;
        }
/**
 * Fixes up start values
 *
 * @param string   $start  The input value
 *
 * @return string
 */
        private function checkstart(string $start) : string
        {
            return $start === '' || strtolower($start) == 'now' ? Context::getinstance()->utcnow() : Context::getinstance()->utcdate($start);
        }
/**
 * Fixes up end values
 *
 * @param string   $end  The input value
 *
 * @return string
 */
        private function checkend(string $end) : ?string
        {
            return $end === '' || strtolower($end) == 'never' ? NULL : Context::getinstance()->utcdate($end);
        }
/**
 * Update - called when a rolename bean is stored
 *
 * @throws \Framework\Exception\BadValue
 * @return void
 * @psalm-suppress PossiblyUnusedMethod
 */
        public function update() : void
        {
            $this->bean->start = $this->checkstart($this->bean->start);
            $this->bean->end = $this->checkend($this->bean->end);
            if (!empty($this->bean->end) && $this->bean->start > $this->bean->end)
            {
                throw new \Framework\Exception\BadValue('Start > End ');
            }
        }
/**
 * Is this role currently valid?
 * i.e. start < now < end (if end has a value)
 *
 * @return bool
 * @psalm-suppress PossiblyUnusedMethod
 */
        public function valid() : bool
        {
            $now = Context::getinstance()->utcnow();
            return $this->bean->start <= $now && (!empty($this->bean->end) || $now <= $this->bean->end);
        }
    }
?>

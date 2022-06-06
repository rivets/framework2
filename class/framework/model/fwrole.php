<?php
/**
 * A model class for the RedBean object Role
 *
 * !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!! This is a Framework system class - do not edit !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2015-2021 Newcastle University
 * @package Framework\Model
 */
    namespace Framework\Model;

    use \Config\Framework as FW;
    use \Support\Context;
/**
 * A class implementing a RedBean model for Role beans
 */
    class FWRole extends \RedBeanPHP\SimpleModel
    {
/**
 * Return rolename object
 *
 * @psalm-suppress PossiblyUnusedMethod
 */
        final public function rolename() : \RedBeanPHP\OODBBean
        {
            return $this->bean->{FW::ROLENAME};
        }
/**
 * Return rolecontext object
 *
 * @psalm-suppress PossiblyUnusedMethod
 */
        final public function rolecontext() : \RedBeanPHP\OODBBean
        {
            return $this->bean->{FW::ROLECONTEXT};
        }
/**
 * Fixes up start values
 *
 * @param string   $start  The start date
 */
        private function checkstart(string $start) : string
        {
            return $start === '' || \strtolower($start) === 'now' ? Context::getinstance()->utcnow() : Context::getinstance()->utcdate($start);
        }
/**
 * Fixes up end values
 *
 * @param string   $end  The end date
 */
        private function checkend(string $end) : ?string
        {
            return $end === '' || \strtolower($end) === 'never' ? NULL : Context::getinstance()->utcdate($end);
        }
/**
 * Update - called by RedBean when a rolename bean is stored
 *
 * @throws \Framework\Exception\BadValue
 * @psalm-suppress PossiblyUnusedMethod
 */
        final public function update() : void
        {
            $this->bean->start = $this->checkstart($this->bean->start);
            $this->bean->end = $this->checkend($this->bean->end);
            if (!empty($this->bean->end) && $this->bean->start > $this->bean->end)
            {
                throw new \Framework\Exception\BadValue('Start > End ');
            }
        }
/**
 * Is this role currently valid? i.e. start < now < end (if end has a value)
 *
 * @psalm-suppress PossiblyUnusedMethod
 */
        final public function valid() : bool
        {
            $now = Context::getinstance()->utcnow();
            return $this->bean->start <= $now && (!empty($this->bean->end) || $now <= $this->bean->end);
        }
    }
?>

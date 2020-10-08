<?php
/**
 * A trait supporting classess that use roles
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2017-2020 Newcastle University
 * @package Framework
 * @subpackage SystemSupport
 */
    namespace Framework\Support;

    use \Config\Framework as FW;
/**
 * A trait that provides various role handling functions for beans that have associated roles.
 *
 * A role is a <rolecontext, rolename> tuple.
 */
    trait HandleRole
    {
/**
 * Check that user has one of a set of roles
 *
 * @todo support nested AND/OR
 *
 * @param array<array<string>>  $roles  [['context', 'role'],...]]
 * @param bool                  $or     If TRUE then the condition is OR
 *
 * @return array
 */
        public function checkrole(array $roles, bool $or = TRUE) : array
        {
            $res = [];
            foreach ($roles as $rc)
            {
                $res[] = $this->hasrole(...$rc);
            }
            $res = array_filter($res); // get rid of any null entries
            return $or ? $res : (count($res) === count($roles) ? $res : []); // for an "and" you have to have all of them.
        }
/**
 * Check for a role
 *
 * @param string    $contextname    The name of a context...
 * @param string    $rolename       The name of a role - if this is the empty string then having the context is enough
 *
 *
 * @return ?object
 */
        public function hasrole(string $contextname, string $rolename) : ?object
        {
            $cont = \Support\Context::getinstance();
            try
            {
                $rolecontextbean = $cont->rolecontext($contextname);
                $rolenamebean = $rolename === '' ? NULL : $cont->rolename($rolename); // if === '' then only checking for a context
            }
            catch (\Framework\Exception\InternalError $e)
            {
                return NULL;
            }
            return $this->hasRoleByBean($rolecontextbean, $rolenamebean);
        }
/**
 * Check for a role by bean
 *
 * @param \RedBeanPHP\OODBBean    $rolecontext  A rolecontext
 * @param ?\RedBeanPHP\OODBBean   $rolename     A rolename - if this is NULL having the context is enough
 *
 * @return ?object
 */
        public function hasRoleByBean(\RedBeanPHP\OODBBean $rolecontext, ?\RedBeanPHP\OODBBean $rolename) : ?object
        {
            return \R::findOne($this->roletype, FW::ROLECONTEXT.'_id=? and '.FW::USER.'_id=? '.
                (!is_null($rolename) ? 'and '.FW::ROLENAME.'_id=? ' : '').
                'and start <= UTC_TIMESTAMP() and (end is NULL or end >= UTC_TIMESTAMP())',
                [$rolecontext->getID(), $this->bean->getID(), is_null($rolename) ? '' : $rolename->getID()]);
        }
/**
 * Delete a role
 *
 * @param string    $contextname    The name of a context...
 * @param string    $rolename       The name of a role....
 *
 * @throws \Framework\Exception\BadValue
 * @return void
 */
        public function delrole(string $contextname, string $rolename) : void
        {
            $cont = \Support\Context::getinstance();
            $bn = \R::findOne($this->roletype, FW::ROLECONTEXT.'_id=? and '.FW::ROLENAME.'_id=? and '.
                FW::USER.'_id=? and start <= UTC_TIMESTAMP() and (end is NULL or end >= UTC_TIMESTAMP())',
                [
                    $cont->rolecontext($contextname)->getID(),
                    $cont->rolename($rolename)->getID(),
                    $this->bean->getID(),
                ]);
            if (is_object($bn))
            {
                \R::trash($bn);
            }
        }
/**
 *  Add a role
 *
 * @param string    $contextname    The name of a context...
 * @param string    $rolename       The name of a role....
 * @param string    $otherinfo      Any other info that is to be stored with the role
 * @param string    $start          A datetime
 * @param string    $end            A datetime or ''
 *
 * @throws \Framework\Exception\BadValue
 * @return \RedBeanPHP\OODBBean
 */
        public function addrole(string $contextname, string $rolename, string $otherinfo,
            string $start, string $end = '') : \RedBeanPHP\OODBBean
        {
            $cont = \Support\Context::getinstance();
            return $this->addRoleByBean($cont->rolecontext($contextname), $cont->rolename($rolename),
                $otherinfo, $start, $end);
        }
/**
 *  Add a role
 *
 * @param \RedBeanPHP\OODBBean  $rolecontext    Contextname
 * @param \RedBeanPHP\OODBBean  $rolename       Rolename
 * @param string                $otherinfo      Any other info that is to be stored with the role
 * @param string                $start          A datetime
 * @param string                $end            A datetime or ''
 *
 * @return \RedBeanPHP\OODBBean
 */
        public function addRoleByBean(\RedBeanPHP\OODBBean $rolecontext, \RedBeanPHP\OODBBean $rolename,
            string $otherinfo, string $start, string $end = '') : \RedBeanPHP\OODBBean
        {
            $r = \R::dispense($this->roletype);
            $r->{$this->bean->getmeta('type')} = $this->bean;
            $r->rolecontext = $rolecontext;
            $r->rolename = $rolename;
            $r->otherinfo = $otherinfo;
            $r->start = $start;
            $r->end = $end;
            \R::store($r);
            return $r;
        }
/**
 * Get all currently valid roles for this bean
 *
 * @param bool  $all    If TRUE then include expired roles
 *
 * @return array<\RedBeanPHP\OODBBean>
 */
        public function roles(bool $all = FALSE) : array
        {
            if ($all)
            {
                return $this->bean->with('order by start,end')->{'own'.ucfirst($this->roletype)};
            }
            $cond = 'start <= UTC_TIMESTAMP() and (end is null or end >= UTC_TIMESTAMP()) order by start, end';
            return $this->bean->withCondition($cond)->{'own'.ucfirst($this->roletype)};
        }
/**
 * Deal with the role selecting part of a form
 *
 * @param \Support\Context  $context    The context object
 *
 * @psalm-suppress UndefinedClass
 *
 * @return void
 */
        public function editroles(\Support\Context $context) : void
        {
            $fdt = $context->formdata('post');
            if ($fdt->exists('exist'))
            {
                foreach ($fdt->fetchArray('exist') as $ix => $rid)
                {
                    $rl = $context->load($this->roletype, $rid, TRUE);
                    $rl->start = $fdt->fetch(['xstart', $ix]);
                    $rl->end = $fdt->fetch(['xend', $ix]);
                    $rl->otherinfo = $fdt->fetch(['xotherinfo', $ix]);
                    \R::store($rl);
                }
            }
            foreach ($fdt->fetchArray('context') as $ix => $cn)
            {
                $rn = $fdt->fetch(['role', $ix]);
                if ($rn !== '' && $cn !== '')
                {
                    $end = $fdt->fetch(['end', $ix]);
                    $start = $fdt->fetch(['start', $ix]);
                    $info = $fdt->fetch(['otherinfo', $ix]);

                    $rcb = $context->load(FW::ROLECONTEXT, $cn);
                    $rnb = $context->load(FW::ROLENAME, $rn);
                    $prole = $this->hasRoleByBean($rcb, $rnb);
                    if (is_object($prole))
                    { // exists already...
                        $prole->start = $start;
                        $prole->end = $end;
                        $prole->otherinfo = $info;
                        \R::store($prole); // will call the update function to check values
                    }
                    else
                    {
                        $this->addRoleByBean($rcb, $rnb, $info, $start, $end);
                    }
                }
            }
        }
    }
?>
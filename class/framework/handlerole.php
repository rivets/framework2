<?php
/**
 * A trait supporting classess that use roles
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2017-2019 Newcastle University
 */
    namespace Framework;

    use Framework\Web\Web as Web;
    use Config\Framework as FW;
    use Support\Context as Context;
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
 * @param array   $roles  [['context', 'role'],...]]
 * @param boolean $or     If TRUE then the condition is OR
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
            if ($or)
            {
                return $res;
            }
            return count($res) == count($roles) ? $res : []; // for an and you have to have all of them.
        }
/**
 * Check for a role
 *
 * @param string    $rolecontextname    The name of a context...
 * @param string	$rolename           The name of a role....
 *
 * @return ?object
 */
        public function hasrole(string $rolecontextname, string $rolename) : ?object
        {
            $cname = \R::findOne(FW::ROLECONTEXT, 'name=?', [$rolecontextname]);
            if (!is_object($cname))
            {
                return NULL;
            }
            $rname = \R::findOne(FW::ROLENAME, 'name=?', [$rolename]);
            if (!is_object($rname))
            {
                return NULL;
            }
            return \R::findOne($this->roletype, FW::ROLECONTEXT.'_id=? and '.FW::ROLENAME.'_id=? and '.FW::USER.'_id=? and start <= UTC_TIMESTAMP() and (end is NULL or end >= UTC_TIMESTAMP())',
                [$cname->getID(), $rname->getID(), $this->bean->getID()]);
        }
/**
 * Check for a role by bean
 *
 * @param object    $rolecontext    A rolecontext
 * @param object    $rolename       A rolename
 *
 * @return ?object
 */
        public function hasrolebybean($rolecontext, $rolename) : ?object
        {
            return \R::findOne($this->roletype, FW::ROLECONTEXT.'_id=? and '.FW::ROLENAME.'_id=? and '.FW::USER.'_id=? and start <= UTC_TIMESTAMP() and (end is NULL or end >= UTC_TIMESTAMP())',
                [$rolecontext->getID(), $rolename->getID(), $this->bean->getID()]);
        }
/**
 * Delete a role
 *
 * @param string	$contextname    The name of a context...
 * @param string	$rolename       The name of a role....
 *
 * @throws \Framework\Exception\BadValue
 * @return void
 */
        public function delrole(string $contextname, string $rolename) : void
        {
            $cname = \R::findOne(FW::ROLECONTEXT, 'name=?', [$contextname]);
            if (!is_object($cname))
            {
                throw new \Framework\Exception\BadValue('No such context: '.$contextname);
            }
            $rname = \R::findOne(FW::ROLENAME, 'name=?', [$rolename]);
            if (!is_object($rname))
            {
                throw new \Framework\Exception\BadValue('No such role: '.$rolename);
            }
            $bn = \R::findOne($this->roletype, FW::ROLECONTEXT.'_id=? and '.FW::ROLENAME.'_id=? and '.FW::USER.'_id=? and start <= UTC_TIMESTAMP() and (end is NULL or end >= UTC_TIMESTAMP())',
                [$cname->getID(), $rname->getID(), $this->bean->getID()]);
            if (is_object($bn))
            {
                \R::trash($bn);
            }
        }
/**
 *  Add a role
 *
 * @param string	$contextname    The name of a context...
 * @param string	$rolename       The name of a role....
 * @param string	$otherinfo      Any other info that is to be stored with the role
 * @param string	$start		A datetime
 * @param string	$end		A datetime or ''
 *
 * @throws \Framework\Exception\BadValue
 *
 * @return \RedBeanPHP\OODBBean
 */
        public function addrole(string $contextname, string $rolename, string $otherinfo, string $start, string $end = '') : \RedBeanPHP\OODBBean
        {
            $cname = \R::findOne(FW::ROLECONTEXT, 'name=?', [$contextname]);
            if (!is_object($cname))
            {
                throw new \Framework\Exception\BadValue('No such context: '.$contextname);
            }
            $rname = \R::findOne(FW::ROLENAME, 'name=?', [$rolename]);
            if (!is_object($rname))
            {
                throw new \Framework\Exception\BadValue('No such context: '.$rolename);
            }
            return $this->addrolebybean($cname, $rname, $otherinfo, $start, $end);
        }
/**
 *  Add a role
 *
 * @param object	$rolecontext    Contextname
 * @param object	$rolename       Rolename
 * @param string	$otherinfo      Any other info that is to be stored with the role
 * @param string	$start		A datetime
 * @param string	$end		A datetime or ''
 *
 * @return \RedBeanPHP\OODBBean
 */
        public function addrolebybean($rolecontext, $rolename, string $otherinfo, string $start, string $end = '') : \RedBeanPHP\OODBBean
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
 * @param bool       	$all	If TRUE then include expired roles
 *
 * @return array
 */
        public function roles(bool $all = FALSE) : array
        {
            if ($all)
            {
                return $this->bean->with('order by start,end')->{'own'.ucfirst($this->roletype)};
            }
            return $this->bean->withCondition('start <= UTC_TIMESTAMP() and (end is null or end >= UTC_TIMESTAMP()) order by start, end')->{'own'.ucfirst($this->roletype)};
        }
/**
 * Deal with the role selecting part of a form
 *
 * @param \Support\Context    $context    The context object
 *
 * @psalm-suppress UndefinedClass
 *
 * @return void
 */
        public function editroles(Context $context) : void
        {
            $fdt = $context->formdata();
            $uroles = $this->roles();
            if ($fdt->haspost('exist'))
            {
                foreach ($fdt->posta('exist') as $ix => $rid)
                {
                    $rl = $context->load($this->roletype, $rid, TRUE);
                    $start = $fdt->post(['xstart', $ix]);
                    $end = $fdt->post(['xend', $ix]);
                    $other = $fdt->post(['xotherinfo', $ix]);
                    if ($start != $rl->start)
                    {
                        $rl->start = $start;
                    }
                    if ($end != $rl->end)
                    {
                         $rl->end = $end;
                    }
                    if ($other != $rl->otherinfo)
                    {
                        $rl->otherinfo = $other;
                    }
                    \R::store($rl);
                }
            }
            foreach ($fdt->posta('context') as $ix => $cn)
            {
                $rn = $fdt->post(['role', $ix]);
                if ($rn !== '' && $cn !== '')
                {
                    $rcb = $context->load(FW::ROLECONTEXT, $cn);
                    $rnb = $context->load(FW::ROLENAME, $rn);
                    $prole = $this->hasrolebybean($rcb, $rnb);

                    $end = $fdt->post(['end', $ix]);
                    $start = $fdt->post(['start', $ix]);
                    $info = $fdt->post(['otherinfo', $ix]);
                    if (is_object($prole))
                    { # exists already...
                        if ($prole->start != $start)
                        {
                            $prole->start = $start;
                        }
                        if ($prole->end != $end)
                        {
                            $prole->end = $end;
                        }
                        if ($prole->otherinfo != $info)
                        {
                            $prole->otherinfo = $info;
                        }
                        \R::store($prole); // will only talk to DB if anything has changed...
                    }
                    else
                    {
                        $x = $this->addrolebybean($rcb, $rnb, $info, $start, $end);
                    }
                }
            }
        }
    }
?>
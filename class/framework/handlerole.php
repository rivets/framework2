<?php
/**
 * A trait supporting classess that use roles
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2017-2018 Newcastle University
 */
    namespace Framework;

    use \Framework\Web\Web as Web;
    use Support\Context as Context;
/**
 * A trait that provides various role handling functions for beans that have associated roles.
 *
 * A role is a <rolecontext, rolename> tuple.
 */
    trait HandleRole
    {
/**
 * Check for a role
 *
 * @param string        $rolecontextname    The name of a context...
 * @param string	$rolename           The name of a role....
 *
 * @return object
 */
        public function hasrole(string $rolecontextname, string $rolename)
        {
            $cname = \R::findOne('rolecontext', 'name=?', [$rolecontextname]);
            if (!is_object($cname))
            {
                return FALSE;
            }
            $rname = \R::findOne('rolename', 'name=?', [$rolename]);
            if (!is_object($rname))
            {
                return FALSE;
            }
            return \R::findOne($this->roletype, 'rolecontext_id=? and rolename_id=? and user_id=? and start <= UTC_TIMESTAMP() and (end is NULL or end >= UTC_TIMESTAMP())',
                [$cname->getID(), $rname->getID(), $this->bean->getID()]);
        }
/**
 * Check for a role by bean
 *
 * @param object    $rolecontext    A rolecontext
 * @param object    $rolename       A rolename
 *
 * @return object
 */
        public function hasrolebybean($rolecontext, $rolename)
        {
            return \R::findOne($this->roletype, 'rolecontext_id=? and rolename_id=? and user_id=? and start <= UTC_TIMESTAMP() and (end is NULL or end >= UTC_TIMESTAMP())',
                [$rolecontext->getID(), $rolename->getID(), $this->bean->getID()]);
        }
/**
 * Delete a role
 *
 * @param string	$contextname    The name of a context...
 * @param string	$rolename       The name of a role....
 *
 * @return void
 */
        public function delrole(string $contextname, string $rolename)
        {
            $cname = \R::findOne('rolecontext', 'name=?', [$contextname]);
            $rname = \R::findOne('rolename', 'name=?', [$rolename]);
            if (is_object($cname) && is_object($rname))
            {
                $bn = \R::findOne($this->roletype, 'rolecontext_id=? and rolename_id=? and user_id=? and start <= UTC_TIMESTAMP() and (end is NULL or end >= UTC_TIMESTAMP())',
                    [$cname->getID(), $rname->getID(), $this->bean->getID()]);
                if (is_object($bn))
                {
                    \R::trash($bn);
                }
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
 * @return object
 */
        public function addrole(string $contextname, string $rolename, string $otherinfo, string $start, string $end = '')
        {
            $cname = \R::findOne('rolecontext', 'name=?', [$contextname]);
            if (!is_object($cname))
            {
                Web::getinstance()->bad();
            }
            $rname = \R::findOne('rolename', 'name=?', [$rolename]);
            if (!is_object($rname))
            {
                Web::getinstance()->bad();
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
 * @return object
 */
        public function addrolebybean($rolecontext, $rolename, string $otherinfo, string $start, string $end = '')
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
        public function roles(bool $all = FALSE)
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
 * @param object    $context    The context object
 *
 * @return void
 */
        public function editroles(Context $context)
        {
            $fdt = $context->formdata();
            $uroles = $this->roles();
            if ($fdt->haspost('exist'))
            {
                foreach ($fdt->posta('exist') as $ix => $rid)
                {
                    $rl = $context->load($this->roletype, $rid);
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
                    $rcb = $context->load('rolecontext', $cn);
                    $rnb = $context->load('rolename', $rn);
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
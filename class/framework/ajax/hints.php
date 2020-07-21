<?php
/**
 * Class to handle the Framework AJAX hints operation
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2020 Newcastle University
 */
    namespace Framework\Ajax;

    use \Framework\Exception\Forbidden;
/**
 * Get search hints for beans
 */
    class Hints extends Ajax
    {
/**
 * Return permission requirements
 *
 * @return array
 */
        public function requires()
        {
            return [FALSE, []]; // login not required
        }
/**
 * Get search hints for a bean
 *
 * @param Context    $context    The context object for the site
 *
 * @throws Forbidden
 * @return void
 */
        final public function handle() : void
        {
            $rest = $this->context->rest();
            $bean = $rest[1];
            $hints = $this->controller->permissions('hints');
            if (isset($hints[$bean]))
            { // hinting is allowed for this bean
                $this->access->checkPerms($this->context, $hints[$bean][2]); // make sure we are allowed
                $field = $hints[$bean][0];
                $tix = 2;
                if (is_array($field))
                {
                    $tix = 3;
                    if (!in_array($rest[2], $field))
                    {
                        throw new Forbidden('Acess denied');
                    }
                    $field = $rest[2];
                }
                elseif ($field == '*')
                { # the call must specify the field
                    $field = $rest[2];
                    $tix = 3;
                }
                $obj = TRUE;
                if (isset($rest[$tix]))
                {
                    switch ($rest[$tix])
                    {
                    case 'text':
                        $obj = FALSE;
                        break;
                    }
                }
                $this->access->fieldExists($bean, $field); // checks field exists - this implies the the field value is not dangerous to pass directly into the query,
                $ofield = $field;
                $field = '`'.$field.'`';
                $fdt = $this->context->formdata('get');
                $order = $fdt->fetch('order', $field);
                if ($order !== $field)
                { // strop the fieldname if it occurs in the order spec
                    $order = preg_replace('/\b'.$ofield.'\b/', $field, $order);
                }
                $limit = $fdt->fetch('limit', 10);
                $search = $fdt->fetch('search', '%');
                $res = [];
                foreach (\Support\SiteInfo::getinstance()->fetch($bean,
                    $field.' like ? group by '.$field.($order !== '' ? (' order by '.$order) : '').($limit !== '' ? (' limit '.$limit) : ''), [$search]) as $bn)
                {
                    $v = new \stdClass();
                    $v->value = $obj ? $bn->getID() : $bn->$ofield;
                    $v->text = $bn->$ofield;
                    $res[] = $v;
                }
                $this->context->web()->sendJSON($res);
            }
            else
            {
                throw new Forbidden('Permission denied');
            }
        }
    }
?>
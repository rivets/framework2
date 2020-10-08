<?php
/**
 * Class to handle the Framework AJAX hints operation
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2020 Newcastle University
 * @package Framework
 * @subpackage SystemAjax
 */
    namespace Framework\Ajax;

    use \Config\Framework as FW;
    use \Framework\Exception\Forbidden;
/**
 * Get search hints for beans
 */
    class Hints extends Ajax
    {
/**
 * @var array
 */
        private static $permissions = [
            FW::TEST        => [ TRUE, [[FW::FWCONTEXT, FW::DEVELROLE]], ['f1'] ], // table does not always exist
        ];
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
            [1 => $bean, 2 => $field] = $rest;
            $this->checkAccess($this->context->user(), $this->controller->permissions(static::class, self::$permissions), $bean, $field);
            $obj = ($rest[3] ?? '') != 'text'; // if there is a rest[3] and it has the value text then we don't want an object
            $this->fieldExists($bean, $field); // checks field exists - this implies the the field value is not dangerous to pass directly into the query,
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
    }
?>

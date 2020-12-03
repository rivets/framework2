<?php
/**
 * Class to handle the Framework AJAX tablesearch operation
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2020 Newcastle University
 * @package Framework
 * @subpackage SystemAjax
 */
    namespace Framework\Ajax;

    use \Config\Framework as FW;
/**
 * Search database tables
 */
    class TableSearch extends Ajax
    {
/**
 * @var array
 */
        private static $permissions = [
            FW::CONFIG      => [ TRUE, [[FW::FWCONTEXT, FW::ADMINROLE]], [] ],
            FW::FORM        => [ TRUE, [[FW::FWCONTEXT, FW::ADMINROLE]], [] ],
            FW::FORMFIELD   => [ TRUE, [[FW::FWCONTEXT, FW::ADMINROLE]], [] ],
            FW::PAGE        => [ TRUE, [[FW::FWCONTEXT, FW::ADMINROLE]], [] ],
            FW::PAGEROLE    => [ TRUE, [[FW::FWCONTEXT, FW::ADMINROLE]], [] ],
            FW::ROLECONTEXT => [ TRUE, [[FW::FWCONTEXT, FW::ADMINROLE]], [] ],
            FW::ROLENAME    => [ TRUE, [[FW::FWCONTEXT, FW::ADMINROLE]], [] ],
            FW::TABLE       => [ TRUE, [[FW::FWCONTEXT, FW::ADMINROLE]], [] ],
            FW::TEST        => [ TRUE, [[FW::FWCONTEXT, FW::DEVELROLE]], ['f1'] ],
            FW::USER        => [ TRUE, [[FW::FWCONTEXT, FW::ADMINROLE]], [] ],
        ];
/**
 * @var array<string> Search ops
 */
        private static $searchOps = ['', '=', '!=', 'like', 'contains', '>', '>=', '<', '<=', 'regexp', 'is NULL', 'is not NULL'];
/**
 * Return permission requirements
 *
 * @return array
 */
        public function requires()
        {
            return [FALSE, []]; // Permission check done in handle
        }
/**
 * Search a table
 *
 * @return void
 */
        final public function handle() : void
        {
            [$bean, $field, $op] = $this->restCheck(3);
            $this->checkAccess($this->context->user(), $this->controller->permissions(static::class, self::$permissions), $bean, $field, TRUE);
            $value = $this->context->formdata('get')->fetch('value', '');
            $incv = ' ?';
            if ($op == '4')
            {
                $value = '%'.$value.'%';
                $op = 'like';
            }
            else
            {
                if ($op == 10 || $op == 11)
                { // no value on a NULL test
                    $incv = '';
                }
                $op = self::$searchOps[$op];
            }
            $res = [];
            $fields = array_keys(\R::inspect($bean));
            foreach (\R::find($bean, $field.' '.$op.$incv, [$value]) as $bn)
            {
                $bv = new \stdClass();
                foreach ($fields as $f)
                {
                    $bv->$f = $bn->$f;
                }
                $res[] = $bv;
            }
            $this->context->web()->sendJSON($res);
        }
    }
?>
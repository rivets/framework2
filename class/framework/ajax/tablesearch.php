<?php
/**
 * Class to handle the Framework AJAX tablesearch operation
 *
 * @author Lindsay Marshall <lindsay.marshall@newcastle.ac.uk>
 * @copyright 2020-2024 Newcastle University
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
        private static array $permissions = [
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
        private static array $searchOps = ['', '=', '!=', 'like', 'contains', '>', '>=', '<', '<=', 'regexp', 'is NULL', 'is not NULL'];
/**
 * Return permission requirements
 */
        #[\Override]
        public function requires() : array
        {
            return [FALSE, []]; // Permission check done in handle
        }
/**
 * Search a table
 */
        final public function handle() : void
        {
            [$bean, $field, $op] = $this->restCheck(3);
            if (!$this->context->hasAdmin())
            { // not admin so check
                $this->checkAccess($this->context->user(), $this->controller->permissions(static::class, self::$permissions), $bean, $field, TRUE);
            }
            $value = $this->context->formdata('get')->fetch('value', '');
            $incv = ' ?';
            switch ($op)
            {
            case 4:
                $value = '%'.$value.'%';
                $op = 'like';
                break;
            case 10:
            case 11: // no value on a NULL test
                $incv = '';
                /*!!!!!!!!!!!! DROP THROUGH!!!! !!!!!!!!!!!!*/
            default:
                $op = self::$searchOps[$op];
                break;
            }
            $res = [];
            $fields = \array_keys(\R::inspect($bean));
            if (!\in_array($field, $fields))
            {
                throw new \Framework\Exception\Forbidden('Permission denied');
            }
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
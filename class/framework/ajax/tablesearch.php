<?php
/**
 * Class to handle the Framework AJAX tablesearch operation
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2020 Newcastle University
 */
    namespace Framework\Ajax;

/**
 * Search database tables
 */
    class TableSearch extends Ajax
    {
/**
 * @var array
 */
        private static $permissions = [
            [
                [[FW::FWCONTEXT, FW::ADMINROLE]],
                [
                    FW::CONFIG      => [],
                    FW::FORM        => [],
                    FW::FORMFIELD   => [],
                    FW::PAGE        => [],
                    FW::ROLECONTEXT => [],
                    FW::ROLENAME    => [],
                    FW::TABLE       => [],
                    FW::USER        => [],
                ],
            ],
//          [ [Roles], ['BeanName' => [FieldNames - all if empty]]]]
        ];
/**
 * @var array<string> Search ops
 */
        private static $searchops = ['', '=', '!=', 'like', 'contains', '>', '>=', '<', '<=', 'regexp', 'is NULL', 'is not NULL'];
/**
 * Return permission requirements
 *
 * @return array
 */
        public function requires()
        {
            return [TRUE, [[FW::FWCONTEXT, FW::ADMINROLE]]]; // require login, only allow Site Admins to do this
        }
/**
 * Search a table
 *
 * @return void
 */
        final public function handle() : void
        {
            [$bean, $field, $op] = $this->context->restcheck(3);
            $this->access->beanFindCheck($this->context, 'tablesearchperms', $bean, $field, TRUE); // make sure we are allowed to search this bean/field and that it exists
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
                $op = self::$searchops[$op];
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
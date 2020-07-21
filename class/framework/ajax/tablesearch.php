<?php
/**
 * Class to handle the Framework AJAX tablesearch operation
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2020 Newcastle University
 */
    namespace Framework\Ajax;

    use \Framework\Exception\BadValue;
    use \Framework\Exception\Forbidden;
    use \Support\Context;
/**
 * Search database tables
 */
    class TableSearch extends Ajax
    {
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
 * @param \Support\Context   $context The context object
 *
 * @return void
 */
        final public function handle(Context $context) : void
        {
            [$bean, $field, $op] = $context->restcheck(3);
            $this->access->beanFindCheck($context, 'tablesearchperms', $bean, $field, TRUE); // make sure we are allowed to search this bean/field and that it exists
            $value = $context->formdata('get')->fetch('value', '');
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
            $context->web()->sendJSON($res);
        }
    }
?>
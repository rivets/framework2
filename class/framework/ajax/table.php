<?php
/**
 * Class to handle the Framework AJAX table operation
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2020 Newcastle University
 */
    namespace Framework\Ajax;

    use \Framework\Exception\BadValue;
    use \Framework\Exception\Forbidden;
    use \Support\Context;
/**
 * Operations on database tables
 */
    class Table extends Ajax
    {
/**
 * Carry out operations on tables
 *
 * @internal
 * @param \Support\Context   $context The context object
 *
 * @throws \Framework\Exception\Forbidden
 * @throws \Framework\Exception\BadOperation
 *
 * @return void
 * @psalm-suppress UnusedMethod
 * @phpcsSuppress SlevomatCodingStandard.Classes.UnusedPrivateElements
 */
        final public function handle(Context $context) : void
        {
            if (!$context->hasadmin())
            {
                throw new Forbidden('Permission denied');
                /* NOT REACHED */
            }
            $rest = $context->rest();
            if (count($rest) < 2)
            {
                throw new BadValue('No table name');
                /* NOT REACHED */
            }
            $table = strtolower($rest[1]);
            $method = $context->web()->method();
            if ($method == 'POST')
            {
                if (\Support\SiteInfo::tableExists($table))
                {
                    throw new Forbidden('Table exists');
                    /* NOT REACHED */
                }
                if (!preg_match('/[a-z][a-z0-9]*/', $table))
                {
                    throw new BadValue('Table name should be alphanumeric');
                    /* NOT REACHED */
                }
                $fdt = $context->formdata('post');
                $bn = \R::dispense($table);
                foreach ($fdt->fetchArray('field') as $ix => $fname)
                {
                    $fname = strtolower($fname);
                    if (preg_match('/[a-z][a-z0-9]*/', $fname))
                    {
                        $bn->$fname = $fdt->fetch(['sample', $ix], '');
                    }
                }
                \R::store($bn);
                \R::trash($bn);
                \R::exec('truncate `'.$table.'`');
            }
            else
            {
                if (\Support\SiteInfo::isFWTable($table))
                {
                    throw new Forbidden('Permission Denied');
                    /* NOT REACHED */
                }
                switch ($method)
                {
                case 'DELETE':
                    try
                    {
                        \R::exec('drop table `'.$table.'`');
                    }
                    catch (\Exception $e)
                    {
                        throw new Forbidden($e->getMessage());
                        /* NOT REACHED */
                    }
                    break;
                case 'PATCH':
                case 'PUT': // change a field
                    $value = $context->formdata('put')->mustFetch('value');
                    $f1 = $rest[2];
                    if (\Support\SiteInfo::hasField($table, $f1))
                    {
                        throw new BadValue('Bad field name');
                        /* NOT REACHED */
                    }
                    switch ($rest[3])
                    {
                    case 'name':
                        if (\Support\SiteInfo::hasField($table, $value))
                        {
                            throw new BadValue('Field already exists');
                            /* NOT REACHED */
                        }
                        $f2 = $value;
                        $fields = \R::inspect($table);
                        $type = $fields[$f1];
                        break;
                    case 'type':
                        $f2 = $f1;
                        $type = $value;
                        break;
                    default:
                        throw new BadValue('No such change');
                        /* NOT REACHED */
                    }
                    \R::exec('alter table `'.$table.'` change `'.$f1.'` `'.$f2.'` '.$type);
                    break;
                case 'GET':
                default:
                    throw new \Framework\Exception\BadOperation('Operation not supported');
                    /* NOT REACHED */
                }
            }
        }
    }
?>
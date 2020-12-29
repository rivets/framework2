<?php
/**
 * Class to handle the Framework AJAX table operation
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2020 Newcastle University
 * @package Framework
 * @subpackage SystemAjax
 */
    namespace Framework\Ajax;

    use \Config\Framework as FW;
    use \Framework\Exception\BadValue;
    use \Framework\Exception\Forbidden;
/**
 * Operations on database tables
 */
    class Table extends Ajax
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
 *  make a new one
 *
 * @return void
 * @psalm-suppress UnusedMethod
 * @phpcsSuppress SlevomatCodingStandard.Classes.UnusedPrivateElements
 * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter
 */
        private function post(string $table, array $rest) : void
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
            $fdt = $this->context->formdata('post');
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
            $this->context->web()->created('');
        }
/**
 * update a field
 *
 * @return void
 * @psalm-suppress UnusedMethod
 * @phpcsSuppress SlevomatCodingStandard.Classes.UnusedPrivateElements
 */
        private function patch(string $table, array $rest) : void
        {
            if (\Support\SiteInfo::isFWTable($table))
            { // you can't alter framework tables
                throw new Forbidden('Permission Denied');
                /* NOT REACHED */
            }
            $value = $this->context->formdata('put')->mustFetch('value');
            $f1 = $rest[2];
            $this->fieldExists($table, $f1);
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
            try
            {
                \R::exec('alter table `'.$table.'` change `'.$f1.'` `'.$f2.'` '.$type);
            }
            catch (\Exception $e)
            {
                throw new \Framework\Exception\BadValue($e->getMessage());
            }
            $this->context->web()->noContent();
        }
/**
 * Map put onto patch
 *
 * @return void
 * @psalm-suppress UnusedMethod
 * @phpcsSuppress SlevomatCodingStandard.Classes.UnusedPrivateElements
 * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter
 */
        private function put(string $table, array $rest) : void
        {
            $this->patch($table, $rest);
        }
/**
 * DELETE
 *
 * @return void
 * @psalm-suppress UnusedMethod
 * @phpcsSuppress SlevomatCodingStandard.Classes.UnusedPrivateElements
 * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter
 */
        private function delete(string $table, array $rest) : void
        {
            if (\Support\SiteInfo::isFWTable($table))
            { // nobody can delete framework tables
                throw new Forbidden('Permission Denied');
                /* NOT REACHED */
            }
            try
            {
                \R::exec('drop table `'.$table.'`');
            }
            catch (\Exception $e)
            {
                throw new Forbidden($e->getMessage());
                /* NOT REACHED */
            }
            $this->context->web()->noContent();
        }
/**
 * Carry out operations on tables
 *
 *
 * @throws Forbidden
 * @throws BadOperation
 *
 * @return void
 */
        final public function handle() : void
        {
            $rest = $this->context->rest();
            if (count($rest) < 2)
            {
                throw new BadValue('No table name');
                /* NOT REACHED */
            }
            $table = strtolower($rest[1]);
            if (!$this->context->hasAdmin())
            { // not admin so check...
                $this->checkAccess($this->context->user(), $this->controller->permissions(static::class, self::$permissions), $table);
            }
            $method = strtolower($this->context->web()->method());
            if (!method_exists(self::class, $method))
            {
                throw new \Framework\Exception\BadOperation($method.' is not supported');
            }
            $this->{$method}($table, $rest);
        }
    }
?>
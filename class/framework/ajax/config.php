<?php
/**
 * Class to handle the Framework AJAX config operation
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2020 Newcastle University
 */
    namespace Framework\Ajax;

    use \Config\Framework as FW;
    use \Framework\Exception\BadValue;
    use \R;
/**
 * Manipulate config data via Ajax
 */
    class Config extends Ajax
    {
/**
 * Return permission requirements
 *
 * @return array
 */
        public function requires()
        {
            return [TRUE, [[FW::FWCONTEXT, FW::ADMINROLE]]]; // require login, only allow Site ADmins to do this
        }
/**
 * Config value operation
 *
 * @throws \Framework\Exception\BadOperation
 * @throws \Framework\Exception\BadValue
 *
 * @return void
 * @psalm-suppress UnusedMethod
 * @phpcsSuppress SlevomatCodingStandard.Classes.UnusedPrivateElements
 */
        final public function handle() : void
        {
            [$name] = $this->restCheck(1);
            $v = R::findOne(FW::CONFIG, 'name=?', [$name]);
            switch ($this->context->web()->method())
            {
            case 'POST':
                if (is_object($v))
                {
                    throw new BadValue('Item already exists');
                }
                $fdt = $this->context->formdata('post');
                $v = R::dispense(FW::CONFIG);
                $v->name = $name;
                $v->value = $fdt->mustFetch('value');
                $v->type = $fdt->mustFetch('type');
                R::store($v);
                break;

            case 'PATCH':
            case 'PUT':
                if (!is_object($v))
                {
                    throw new BadValue('No such item');
                }
                $fdt = $this->context->formdata('post');
                foreach (['value', 'type', 'name'] as $fld)
                {
                    if ($fdt->exists($fld))
                    {
                        $v->{$fld} = $fdt->mustFetch($fld);
                    }
                }
                R::store($v);
                break;

            case 'DELETE':
                if (!is_object($v))
                {
                    throw new BadValue('No such item');
                }
                R::trash($v);
                break;

            case 'GET':
                if (!is_object($v))
                {
                    throw new BadValue('No such item');
                }
                echo $v->value;
                break;

            default:
                throw new \Framework\Exception\BadOperation($this->context->web()->method().' is not supported');
            }
        }
    }
?>
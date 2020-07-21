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
    use \Support\Context;
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
 * @internal
 * @param \Support\Context    $context    The context object for the site
 *
 * @throws \Framework\Exception\BadOperation
 * @throws \Framework\Exception\BadValue
 *
 * @return void
 * @psalm-suppress UnusedMethod
 * @phpcsSuppress SlevomatCodingStandard.Classes.UnusedPrivateElements
 */
        final public function handle(Context $context) : void
        {
            [$name] = $context->restcheck(1);
            $v = R::findOne(FW::CONFIG, 'name=?', [$name]);
            switch ($context->web()->method())
            {
            case 'POST':
                if (is_object($v))
                {
                    throw new BadValue('Item already exists');
                }
                $fdt = $context->formdata('post');
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
                $v->value = $context->formdata('put')->mustFetch('value');
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
                throw new \Framework\Exception\BadOperation($context->web()->method().' is not supported');
            }
        }
    }
?>
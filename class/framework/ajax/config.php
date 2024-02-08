<?php
/**
 * Class to handle the Framework AJAX config operation
 *
 * @author Lindsay Marshall <lindsay.marshall@newcastle.ac.uk>
 * @copyright 2020-2024 Newcastle University
 * @package Framework\Framework\Ajax
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
 * First element is a bool indicating if login is required. The second element is a list of ['Context', 'Role']
 * that the user must have.
 */
        #[\Override]
        public function requires() : array
        {
            return [TRUE, [[FW::FWCONTEXT, FW::ADMINROLE]]]; // require login, only allow Site ADmins to do this
        }
/**
 * Handle POST
 *
 * @psalm-suppress UnusedMethod
 * @phpcsSuppress SlevomatCodingStandard.Classes.UnusedPrivateElements
 */
        private function post(?\RedBeanPHP\OODBBean $v, string $name) : void
        {
            if (is_object($v))
            {
                throw new BadValue('Item already exists');
            }
            $fdt = $this->context->formdata('post');
            $v = R::dispense(FW::CONFIG);
            $v->name = $name;
            $v->value = $fdt->mustFetch('value');
            $v->type = $fdt->mustFetch('type');
            $v->local = $fdt->fetch('local', 0);
            echo R::store($v); // send back the id of the new config bean
        }
/**
 * Handle PUT or PATCH
 *
 * @psalm-suppress UnusedMethod
 * @phpcsSuppress SlevomatCodingStandard.Classes.UnusedPrivateElements
 */
        private function patch(?\RedBeanPHP\OODBBean $v) : void
        {
            if (!is_object($v))
            {
                throw new BadValue('No such item');
            }
            $res = new \stdClass();
            $fdt = $this->context->formdata('put');
            foreach (['value', 'type', 'name'] as $fld)
            {
                if ($fdt->exists($fld))
                {
                    $res->{$fld} = $v->{$fld};
                    $v->{$fld} = $fdt->mustFetch($fld);
                }
            }
            R::store($v);
            $this->context->web()->sendJSON($res);
        }
/**
 * Map put onto patch
 *
 * @psalm-suppress UnusedMethod
 * @phpcsSuppress SlevomatCodingStandard.Classes.UnusedPrivateElements
 */
        private function put(?\RedBeanPHP\OODBBean $v) : void
        {
            $this->patch($v);
        }
/**
 * Handle DELETE
 *
 * @psalm-suppress UnusedMethod
 * @phpcsSuppress SlevomatCodingStandard.Classes.UnusedPrivateElements
 */
        private function delete(?\RedBeanPHP\OODBBean $v) : void
        {
            if (!is_object($v))
            {
                throw new BadValue('No such item');
            }
            R::trash($v);
            $this->context->web()->noContent();
        }
/**
 * Handle GET
 *
 * @param ?\RedBeanPHP\OODBBean $v
 *
 * @psalm-suppress UnusedMethod
 * @phpcsSuppress SlevomatCodingStandard.Classes.UnusedPrivateElements
 */
        private function get(?\RedBeanPHP\OODBBean $v) : void
        {
            if (!is_object($v))
            {
                throw new BadValue('No such item');
            }
            echo $v->value;
        }
/**
 * Config value operation
 *
 * @throws \Framework\Exception\BadOperation
 * @throws \Framework\Exception\BadValue
 */
        final public function handle() : void
        {
            [$name] = $this->restCheck(1);
            $v = R::findOne(FW::CONFIG, 'name=?', [$name]);
            $method = \strtolower($this->context->web()->method());
            if (!\method_exists(self::class, $method))
            {
                throw new \Framework\Exception\BadOperation($method.' is not supported');
            }
            $this->{$method}($v, $name);
        }
    }
?>
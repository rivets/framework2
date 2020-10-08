<?php
/**
 * A model class for the RedBean object FWConfig
 *
 * This is a Framework system class - do not edit!
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2018-2020 Newcastle University
 * @package Framework
 * @subpackage SystemModel
 */
    namespace Model;

    use \Config\Framework as FW;
    use \Support\Context;
/**
 * A class implementing a RedBean model for Page beans
 * @psalm-suppress UnusedClass
 */
    class FWConfig extends \RedBeanPHP\SimpleModel
    {
/**
 * @var array<array<bool>>   Key is name of field and the array contains flags for checks
 * @phpcsSuppress SlevomatCodingStandard.Classes.UnusedPrivateElements
 */
        private static $editfields = [
            'value'       => [TRUE, FALSE],         // [NOTEMPTY, CHECK/RADIO]
            'integrity'   => [FALSE, FALSE],
            'crossorigin' => [FALSE, FALSE],
            'defer'       => [FALSE, TRUE],
            'async'       => [FALSE, TRUE],
            'type'        => [TRUE, FALSE],
        ];

        use \ModelExtend\FWEdit;
        use \ModelExtend\MakeGuard;
/**
 * Check for a URL or // URL or a local filename - return value or throw
 *
 * @param string   $type   For error message
 *
 * @throws \Framework\Exception\BadValue
 * @return void
 */
        public function checkURL(string $type) : void
        {
            if (filter_var($this->bean->value, FILTER_VALIDATE_URL) === FALSE)
            { // not a straightforward URL
                if (!preg_match('#^(%BASE%/|//?).+#', $this->bean->value))
                { // not a canonical URL
                    throw new \Framework\Exception\BadValue('Invalid value for '.$type.' item');
                }
            }
        }
/**
 * Function called when a page bean is updated - do error checking in here
 *
 * @throws \Framework\Exception\BadValue
 * @return void
 */
        public function update() : void
        {
            $this->bean->name = strtolower($this->bean->name);
            if (!preg_match('/^[a-z][a-z0-9]*/', $this->bean->name))
            {
                throw new \Framework\Exception\BadValue('Invalid config item name');
            }
            switch (strtolower($this->bean->type))
            {
            case 'boolean':
                if (($x = filter_var($this->bean->value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)) === NULL)
                {
                    throw new \Framework\Exception\BadValue('Invalid value for boolean item');
                }
                $this->bean->value = $x ? 1 : 0;
                break;
            case 'css':
                $this->checkURL('CSS');
                break;
            case 'integer':
                if (filter_var($this->bean->value, FILTER_VALIDATE_URL) === FALSE)
                {
                    throw new \Framework\Exception\BadValue('Invalid value for integer item');
                }
                break;
            case 'js':
                $this->checkURL('JavaScript');
                break;
            case 'string':
                break;
            default:
                throw new \Framework\Exception\BadValue('Invalid config item type');
            }
        }
/**
 * Add a new FWConfig bean
 *
 * @see Framework\Ajax::bean
 *
 * @param \Support\Context    $context    The context object
 *
 * @throws \Framework\Exception\BadValue
 * @return \RedBeanPHP\OODBBean
 */
        public static function add(Context $context) : \RedBeanPHP\OODBBean
        {
            $fdt = $context->formdata('post');
            $name = $fdt->mustFetch('name');
            $bn = \R::findOne(FW::CONFIG, 'name=?', [$name]);
            if (is_object($bn))
            {
                throw new \Framework\Exception\BadValue('Config item exists');
            }
            $bn = \R::dispense(FW::CONFIG);
            $bn->name = $name;
            $bn->value = $fdt->mustFetch('value');
            $bn->local = $fdt->fetch('local', 0);
            $bn->fixed = 0;
            $bn->integrity = '';
            $bn->defer = 0;
            \R::store($bn);
            return $bn;
        }
/**
 * Setup for an edit
 *
 * @param \Support\Context    $context   The context object
 *
 * @return void
 * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter
 */
        public function startEdit(Context $context) : void
        {
        }
/**
 * Handle an edit form for this fwconfig item
 *
 * @param \Support\Context   $context    The context object
 *
 * @return  array   [TRUE if error, [error messages]]
 */
        public function edit(Context $context) : array
        {
            $emess = $this->dofields($context->formdata('post'));
            return [!empty($emess), $emess];
        }
/**
 * Handle an update from the page updater
 *
 * @param object $cdata  Update values from the json updater
 *
 * @return void
 */
        public function doupdate(object $cdata, string $base, bool $doit) : string
        {
            if ($this->bean->local == 0)
            { // update if not locally set and there is a new value
                $change = FALSE;
                foreach ($cdata as $k => $v)
                {
                    $v = preg_replace('/%BASE%/', $base, $v); // relocate to this base.
                    if ($this->bean->$k != $v)
                    {
                        if ($doit)
                        {
                            $this->bean->$k = $v;
                        }
                        $change = TRUE;
                    }
                }
                if ($change)
                {
                    if ($doit)
                    {
                        \R::store($this->bean);
                    }
                    return $cdata->value;
                }
            }
            return '';
        }
    }
?>
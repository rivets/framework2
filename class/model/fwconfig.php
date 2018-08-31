<?php
/**
 * A model class for the RedBean object FWConfig
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2018 Newcastle University
 *
 */
    namespace Model;
/**
 * A class implementing a RedBean model for Page beans
 */
    class FWConfig extends \RedBeanPHP\SimpleModel
    {
/**
 * @var Array   Key is name of field and the array contains flags for checks
 */
        private static $editfields = [
            'value'       => [TRUE, FALSE],         # [NOTEMPTY, CHECK/RADIO]
            'integrity'   => [FALSE, FALSE],
            'crossorigin' => [FALSE, FALSE],
            'defer'       => [FALSE, TRUE],
            'async'       => [FALSE, TRUE],
            'type'        => [TRUE, FALSE],
        ];

        use \ModelExtend\FWEdit;
/**
 * Add a new FWConfig bean
 *
 * @param object    $context    The context object
 *
 * @return void
 */
        public static function add($context)
        {
            $fdt = $context->formdata();
            $name = $fdt->mustpost('name');
            $bn = \R::findOne('fwconfig', 'name=?', [$name]);
            if (is_object($bn))
            {
                $context->web()->bad();
            }
            $bn = \R::dispense('fwconfig');
            $bn->name = $name;
            $bn->value = $fdt->mustpost('value');
            $bn->local = $fdt->post('local', 0);
            $bn->fixed = 0;
            $bn->integrity = '';
            $bn->defer = 0;
            echo \R::store($bn);
        }
/**
 * Setup for an edit
 *
 * @param object    $context   The context object
 * 
 * @return void
 */
        public function startEdit($context)
        {
        }
/**
 * Return the CSRFGuard inputs for inclusion in a form;
 * 
 * @return string
 */
        public function guard()
        {
            return \Framework\Utility\CSRFGuard::getinstance()->inputs();
        }
/**
 * Handle an edit form for this fwconfig item
 *
 * @param object   $context    The context object
 *
 * @return  array   [TRUE if error, [error messages]]
 */
        public function edit($context)
        {
            $emess = $this->dofields($context->formdata());
            return [!empty($emess), $emess];
        }
    }
?>
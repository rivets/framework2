<?php
/**
 * A model class for the RedBean object Form
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2016 Newcastle University
 *
 */
    namespace Model;
/**
 * A class implementing a RedBean model for Form beans
 */
    class Form extends \RedBeanPHP\SimpleModel
    {
/**
 * Return the forms fields
 *
 * @return object
 */
        public function fields()
        {
	    return $this->bean->ownFormfield;
        }
/**
 * Handle a form edit
 *
 * @return void
 */
        public function edit($context)
        {

        }
/**
 * Add a form
 *
 * @return void
 */
        public static function add($context)
        {
            $fdt = $context->formdata();
            $p = R::dispense('form');
            $p->name = $fdt->mustpost('name');
            $p->method = $fdt->mustpost('method');
            $p->multipart = $fdt->post('multipart', 0);
            echo R::store($p);
        }
/**
 * View a form
 *
 * @return void
 */
        public function view()
        {

        }
    }
?>

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
 * Return the form name
 *
 * @return object
 */
        public function name()
        {
	    return $this->bean->name;
        }
/**
 * Return the form's method
 *
 * @return object
 */
        public function method()
        {
	    return $this->bean->method;
        }
/**
 * Return the form's fields
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
            $fdt = $context->formdata();
            $this->bean->name = $fdt->mustpost('name');
            $this->bean->method = $fdt->mustpost('method');
            $this->bean->multipart = $fdt->post('multipart', 0);
            \R::store($this->bean);
            
            foreach ($fdt->posta('fldid') as $ix => $fid)
            {
                if ($fid == 'new')
                {
                    $fld = \R::dispense('formfield');
                    $fld->type = $fdt->post(['type', $ix], 'text');
                    $fld->label = $fdt->post(['label', $ix], '');
                    $fld->name = $fdt->post(['fname', $ix], '');
                    $fld->class = $fdt->post(['class', $ix], '');
                    $fld->idval = $fdt->post(['idval', $ix], 'text');
                    $fld->placeholder = $fdt->post(['placeholder', $ix], 'text');
                    \R::store($fld);
                }
                else
                {
                }
            }
        }
/**
 * Add a form
 *
 * @return void
 */
        public static function add($context)
        {
            $fdt = $context->formdata();
            $p = \R::dispense('form');
            $p->name = $fdt->mustpost('name');
            $p->method = $fdt->mustpost('method');
            $p->multipart = $fdt->post('multipart', 0);
            echo \R::store($p);
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

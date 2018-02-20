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
        private static $methods     = ['', 'GET', 'POST'];
        private static $attributes  = ['type', 'class', 'name', 'placeholder'];
        
        private $lcount             = 1;
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
            $this->bean->name = $fdt->mustpost('formname');
            $this->bean->method = $fdt->mustpost('action');
            $this->bean->method = $fdt->mustpost('formidval');
            $this->bean->method = $fdt->mustpost('formclass');
            $this->bean->method = $fdt->mustpost('method');
            $this->bean->multipart = $fdt->post('multipart', 0);
            
            foreach ($fdt->posta('new') as $ix => $fid)
            {
                if (($type = $fdt->post(['type', $ix], '')) !== '')
                {
                    $fld = \R::dispense('formfield');
                    $fld->type = $type;
                    foreach (['label', 'name', 'class', 'idval', 'placeholder', 'value', 'checked', 'required', 'readonly', 'disabled'] as $fname)
                    {
                        $fld->$fname = $fdt->post([$fname, $ix], '');
                    }
                    \R::store($fld);
                    $this->bean->xownForm[] = $fld;
                }
            }
            \R::store($this->bean);
        }
/**
 * View a form
 *
 * @return void
 */
        public function view()
        {
        }
/**
 * Handle a label
 *
 * @param object    $fld    The field
 *
 * @return string  The field idval might be updated also
 */
        private function doLabel($fld, $class = '')
        {
            $label = '';
            if ($fld->label !== '')
            {
                if ($fld->idval == '')
                {
                    $fld->idval = $this->bean->name.$this->lcount;
                    $this->lcount += 1;
                }
                return '<label for="'.$fld->idval.'"'.($class !== '' ? (' class="'.$class.'"') : '').'>'.$fld->label.'</label>';
            }
            return '';
        }
/**
 * Render a field's attributes
 *
 * @param object    $fld
 *
 * @return string
 */
        private function fieldAttr($fld, $class, $doValue = TRUE)
        {
            $attrs = self::$attributes;
            if ($doValue)
            { // include the value in the attributes
                $attrs[] = 'value';
            }
            $res = ['']; // ensures a space at the start of the result
            if ($fld->idval !== '')
            {
                $res[] = 'id="'.$fld->idval.'"';
            }
            if ($class !== '')
            { // add a standard class
                $fld->class = trim($class.' '.$fld->class);
            }
            foreach ($attrs as $atr)
            {
                if ($fld->$atr !== '')
                {
                    $res[] = $atr.'="'.$fld->$atr.'"';
                }
            }
            foreach (['checked', 'selected', 'required', 'readonly', 'disabled'] as $atr)
            {
                if ($fld->$atr)
                {
                    $res[] = $atr.'="'.$atr.'"';
                }
            }
            return implode(' ', $res);
        }
/**
 * Render a form
 *
 * @return string
 */
        public function render()
        {
            $this->lcount = 1;
            $form = '<form action="'.
                ($this->bean->action === '' ? '#' : $this->bean->action).'" '.
                ($this->bean->class !== '' ? (' class="'.$this->bean->class.'"') : '').'" '.
                ($this->bean->idval !== '' ? (' id="'.$this->bean->idval.'"') : '').'" '.
                'method="'.self::$methods[$this->bean->method].'"'.
                ($this->multipart ? ' enctype="multipart/form-data"' : '').
                '>';
            foreach ($this->fields() as $fld)
            {
                switch ($fld->type)
                {
                case 'checkbox':
                case 'radio':
                    $form .= '<div class="form-check"><input'.$this->fieldAttr($fld, 'form-check-input', FALSE).'>'.$this->doLabel($fld, 'form-check-label').'</div>';
                    break;
                case 'select':
                    $form .= '<div class="form-group">'.$this->doLabel($fld).'<select'.$this->fieldAttr($fld, 'form-control', FALSE).'>';
                    $form .= '</select></div>';
                    break;
                case 'textarea':
                    $form .= '<div class="form-group">'.$this->doLabel($fld).'<textarea'.$this->fieldAttr($fld, 'form-control', FALSE).'>'.$this->value.'</textarea></div>';
                    break;
                default: // all the other types are very much the same.
                    $form .= '<div class="form-group">'.$this->doLabel($fld).'<input'.$this->fieldAttr($fld, 'form-control', TRUE).'/></div>';
                    break;
                }
            }
            return $form.'</form>';
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
            $p->action = $fdt->mustpost('action');
            $p->class = $fdt->mustpost('class');
            $p->idval = $fdt->mustpost('idval');
            $p->method = $fdt->mustpost('method');
            $p->multipart = $fdt->post('multipart', 0);
            echo \R::store($p);
        }
    }
?>

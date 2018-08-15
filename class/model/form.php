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
 * @var Array   Key is name of field and the array contains flags for checks
 */
        private static $editfields = [
            'name'            => [TRUE, FALSE],         # [NOTEMPTY, CHECK/RADIO]
            'action'          => [TRUE, FALSE],
            'method'          => [TRUE, FALSE],
            'idval'           => [FALSE, FALSE],
            'formclass'       => [FALSE, FALSE],
            'multipart'       => [FALSE, TRUE],
        ];
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
 * @param object    $context
 *
 * @return void
 */
        public function edit($context)
        {
            $emess = [];
            $fdt = $context->formdata();
            foreach (self::$editfields as $fld => $flags)
            {
                if ($flags[1])
                { // this is a checkbox - they can't be required
                    $val = $fdt->post($fld, 0);
                }
                else
                {
                    $val = $fdt->post($fld, '');
                    if ($flags[0] && $val === '')
                    { // this is an error as this is a required field
                        $emess[] = $fld.' is required';
                        continue;
                    }
                }
                if ($val != $this->bean->$fld)
                {
                    $this->bean->$fld = $val;
                }
            }
            if (empty($emess))
            {
                \R::store($this->bean);
            }
            
            foreach ($fdt->posta('new') as $ix => $fid)
            {
                if (($type = $fdt->post(['type', $ix], '')) !== '')
                {
                    $fld = \R::dispense('formfield');
                    $fld->type = $type;
                    foreach (['label', 'name', 'class', 'idval', 'placeholder', 'value', 'other', 'flags'] as $fname)
                    {
                        $fld->$fname = $fdt->post([$fname, $ix], '');
                    }
                    \R::store($fld);
                    $this->bean->xownForm[] = $fld;
                }
            }
            \R::store($this->bean);
            return [!empty($emess), $emess];
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
 * @param string    $class  The class name
 *
 * @return string   The field idval might be updated also
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
 * @param string    $class  The class name
 * @param boolean   $doValue  The class name
 *
 * @return string
 */
        private function fieldAttr($fld, $class, $doValue = TRUE)
        {
            $attrs = self::$attributes;
            if ($doValue)
            { # include the value in the attributes
                $attrs[] = 'value';
            }
            $res = ['']; # ensures a space at the start of the result
            if ($fld->idval !== '')
            {
                $res[] = 'id="'.$fld->idval.'"';
            }
            if ($class !== '')
            { # add a standard class
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
        public function render($values = [])
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
                if (isset($fld->done))
                { # if we group radio buttons then some may get marked as done.
                    continue;
                }
                switch ($fld->type)
                {
                case 'checkbox':
                    if (isset($values[$fld->name]) && $fld->value == $values[$fld->name])
                    {
                        $fld->checked = 1;
                    }
                    $label = $this->doLabel($fld, 'form-check-label'); # need to do this first as it might set the label field in $fld
                    $form .= '<div class="form-check"><input'.$this->fieldAttr($fld, 'form-check-input', FALSE, $values).'>'.$label.'</div>';
                    break;
                    case 'radio':
                    if (isset($values[$fld->name]) && $fld->value == $values[$fld->name])
                    {
                        $fld->checked = 1;
                    }
                    $label = $this->doLabel($fld, 'form-check-label'); # need to do this first as it might set the label field in $fld
                    $form .= '<div class="form-check"><input'.$this->fieldAttr($fld, 'form-check-input', FALSE, $values).'>'.$label.'</div>';
                    break;
                case 'select':
                    $form .= '<div class="form-group">'.$this->doLabel($fld).'<select'.$this->fieldAttr($fld, 'form-control', FALSE).'>';
                    $optgroup = FALSE;
                    foreach ($values[$fld->name] as $option)
                    {
                        if (is_object($option))
                        {
                            if (isset($option->optgroup))
                            {
                                if ($optgroup)
                                { # one open already so close it
                                    $form .= '</optgroup>';
                                }
                                if ($option->optgroup !== '') # If the name is empty then we want to close an open optgroup without startng a new one
                                { 
                                    $form .= '<optgroup label="'.$option->optgroup.'"'.(isset($option->disabled) ? ' disabled="disabled"' : '').'>';
                                    $optgroup == TRUE;
                                }
                            }
                            else
                            {
                                $form .= '<option value="'.$option->value.'">'.$option->text.'</option>';
                            }
                        }
                        elseif (is_array($option))
                        {
                            if ($option[0] === NULL)
                            {
                                if ($optgroup)
                                { # one open already so close it
                                    $form .= '</optgroup>';
                                }
                                if ($option[1] !== NULL) # If the name is also NULL then we want to close an open optgroup without startng a new one
                                { 
                                    $form .= '<optgroup label="'.$option[1].'"'.(isset($option[2]) ? ' disabled="disabled"' : '').'>';
                                    $optgroup == TRUE;
                                }
                            }
                            else
                            {
                                $form .= '<option value="'.$option[0].'">'.$option[1].'</option>';
                            }
                        }
                        else
                        {
                            $form .= '<option value="'.$option.'">'.$option.'</option>';
                        }
                    }
                    if ($optgroup)
                    { # close any open optgroup
                        $form .= '</optgroup>';
                    }
                    $form .= '</select></div>';
                    break;
                case 'textarea':
                    $form .= '<div class="form-group">'.$this->doLabel($fld).'<textarea'.$this->fieldAttr($fld, 'form-control', FALSE).'>'.($values[$fld->name] ?? $this->value).'</textarea></div>';
                    break;
                default: # all the other types are very much the same.
                    if (isset($values[$fld->name]))
                    {
                        $fld->value = $values[$fld->name];
                    }
                    $form .= '<div class="form-group">'.$this->doLabel($fld).'<input'.$this->fieldAttr($fld, 'form-control', TRUE, $values).'/></div>';
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

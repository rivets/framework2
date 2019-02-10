<?php
/**
 * A model class for the RedBean object Form
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2016 Newcastle University
 *
 */
    namespace Model;
    use Support\Context as Context;
/**
 * A class implementing a RedBean model for Form beans
 */
    class Form extends \RedBeanPHP\SimpleModel
    {
/**
 * @var METHOD options for forms
 */
        private static $methods     = ['', 'GET', 'POST'];
/**
 * @var Attributes for inputs
 */
        private static $attributes  = ['type', 'class', 'name', 'placeholder'];
/**
 * @var array   Key is name of field and the array contains flags for checks
 */
        private static $editfields = [
            'name'            => [TRUE, FALSE],         # [NOTEMPTY, CHECK/RADIO]
            'action'          => [TRUE, FALSE],
            'method'          => [TRUE, FALSE],
            'idval'           => [FALSE, FALSE],
            'class'           => [FALSE, FALSE],
            'multipart'       => [FALSE, TRUE],
        ];
/**
 * @var array The kinds of flags that fields can have
 */
        private static $flags = [
            'checked'       => ['Checked', TRUE, 0x01],
            'disabled'      => ['Disabled', FALSE, 0x02],
            'multiple'      => ['Multiple', TRUE, 0x04],
            'readonly'      => ['Readonly', FALSE, 0x08],
            'required'      => ['Required', FALSE, 0x10],
        ];

        use \ModelExtend\FWEdit;
        use \ModelExtend\MakeGuard;
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
	    return $this->bean->with('order by seqn,name')->ownFormfield;
        }
/**
 * Return the form's fields by sequence
 *
 * Some fields deliberately share sequence numbers (e.g. checkboxes in a row)
 *
 * @return object
 */
        public function sequence()
        {
            $res = [];
            foreach ($this->bean->fields() as $fld)
            {
                $sqn = explode('/', $fld->seqn);
                if (count($sqn) > 1)
                { // there are sub orderings in here
                    $res[$sqn[0]][$sqn[1]] = $fld;
                }
                else
                {
                    $res[$sqn[0]][] = $fld;
                }
            }
	    return $res;
        }
/**
 * Resequence the fields so that they are all multiples of 10
 *
 * Remember that some items deliberatley share sequence numbers!
 *
 * @todo support resequencing of sub-orderings
 *
 * @return void
 */
        public function resequence()
        {
            $seqn = 10;
            foreach ($this->bean->sequence() as $flds)
            {
                foreach ($flds as $fld)
                {
                    $sqn = explode('/', $fld->seqn);
                    $sqn[0] = $seqn;
                    $fld->seqn = implode('/', $sqn);
                    \R::store($fld);
                }
                $seqn += 10;
            }
        }
/**
 * Setup for an edit
 *
 * @param object    $context The context object
 * 
 * @return void
 */
        public function startEdit(Context $context, array $rest)
        {
            $context->local()->addval('flags', self::$flags);
        }
/**
 * Handle a form edit
 *
 * @param object    $context  The context object
 *
 * @return void
 */
        public function edit(Context $context)
        {
            $fdt = $context->formdata();
            $emess = $this->dofields($fdt);

            foreach ($fdt->posta('new') as $ix => $fid)
            {
                if (($type = $fdt->post(['type', $ix], '')) !== '')
                {
                    $fld = \R::dispense('formfield');
                    $fld->type = $type;
                    foreach (['label', 'name', 'class', 'idval', 'placeholder', 'value', 'other', 'seqn'] as $fname)
                    {
                        $fld->$fname = $fdt->post(['fld'.$fname, $ix], '');
                    }
                    $fld->flags = 0;
                    foreach (self::$flags as $fn => $fv)
                    {
                        $fld->flags |= $fdt->post(['fld'.$fn, $ix], 0);
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
        public function view(Context $context, array $rest)
        {
        }
/**
 * Render a form
 *
 * @param array    $values Values to enter into form
 * @param boolean  $noform If TRUE then do not put out the <form> and </form> tags - useful when building forms in parts
 *
 * @return string
 */
        public function render($values = [], $noform = FALSE)
        {
            if (!$noform || $this->bean->method == 0)
            {
                $form = '<form action="'.
                    ($this->bean->action === '' ? '#' : $this->bean->action).'" '.
                    ($this->bean->class !== '' ? (' class="'.$this->bean->class.'"') : '').'" '.
                    ($this->bean->idval !== '' ? (' id="'.$this->bean->idval.'"') : '').'" '.
                    'method="'.self::$methods[$this->bean->method].'"'.
                    ($this->multipart ? ' enctype="multipart/form-data"' : '').
                    ' role="form">'.PHP_EOL;
            }
            else
            {
                $form = '';
            }
            $fset = FALSE;
            foreach ($this->sequence() as $flds)
            {
                $fld = reset($flds);
                $crlabel = '';
                switch ($fld->type)
                {
                case 'fieldset':
                    $form .= ($fset ? '</fieldset>' : '').'<fieldset'.$fld->fieldAttr('', TRUE).'>'.($fld->label !== '' ? '<legend>'.$fld->label.'</legend>' : '');
                    $fset = TRUE;
                    break;
                case 'endfset':
                    if ($fset)
                    {
                        $form .= '</fieldset>';
                        $fset = FALSE;
                    }
                    break;

                case 'label': // labelling for checkbox and radio groupings
                    $crlabel = '<label'.$fld->attr('', TRUE).'>'.$fld->label.'</label>'; // make the label
                    array_shift($flds); // pop off the label- the rest will be checkboxes or radios
                case 'checkbox':
                case 'radio':
                    
                    $form .= '<div class="form-group">'.$crlabel.'<div class="form-check form-check-inline">';
                    foreach ($flds as $fld)
                    {
                        if (isset($values[$fld->name]) && $fld->value == $values[$fld->name])
                        {
                            $fld->checked = 1;
                        }
                        $input = '<input'.$fld->fieldAttr('', TRUE).'/> ';
                        $form .= $fld->doLabel(FALSE, 'form-check-label mr-2', $input); # need to do this first as it might set the label field in $fld
                    }
                    $form .= '</div></div>';
                    break;
                case 'select':
                    $form .= '<div class="form-group">'.$fld->doLabel(TRUE).'<select'.$fld->fieldAttr('form-control', FALSE).'>';
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
                    $form .= '<div class="form-group">'.$fld->doLabel(TRUE).'<textarea'.$fld->fieldAttr('form-control', FALSE).'>'.($values[$fld->name] ?? $this->value).'</textarea></div>';
                    break;
                case 'submit' :
                    $form .= '<div class="form-group"><button type="submit"'.$fld->fieldAttr('', FALSE).'>'.$fld->value.'</button></div>';
                    break;
                case 'button' :
                    $form .= '<div class="form-group"><button'.$fld->fieldAttr('', FALSE).'>'.$fld->value.'</button></div>';
                    break;
                default: # all the other types are very much the same.
                    if (isset($values[$fld->name]))
                    {
                        $fld->value = $values[$fld->name];
                    }
                    $form .= '<div class="form-group">'.$fld->doLabel(TRUE).'<input'.$fld->fieldAttr('form-control', TRUE, $values).'/></div>';
                    break;
                }
                $form .= PHP_EOL;
            }
            if ($fset)
            {
                $form .= '</fieldset>';
            }
            return ($noform || $this->bean->method == 0) ? $form : ($form.'</form>');
        }
/**
 * Add a form
 *
 * @param object    $context  The context object
 *
 * @return void
 */
        public static function add(Context $context)
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

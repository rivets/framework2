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
    use \Config\Framework as FW;
/**
 * A class implementing a RedBean model for Form beans
 */
    class Form extends \RedBeanPHP\SimpleModel
    {
/**
 * @var string[] METHOD options for forms
 */
        private static $methods     = ['', 'GET', 'POST'];
/**
 * @var string[] Attributes for inputs
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
/**
 * @var bool flag to indicate inside optgroup.
 * @todo can we have nested optgroups? Maybe this needs to be a count rather than a flag.
 */
        private $optgroup = FALSE;

        use \ModelExtend\FWEdit;
        use \ModelExtend\MakeGuard;
/**
 * Return the form name
 *
 * @return string
 */
        public function name() : string
        {
            return $this->bean->name;
        }
/**
 * Return the form's method
 *
 * @return string
 */
        public function method() : string
        {
            return $this->bean->method;
        }
/**
 * Return the form's fields
 *
 * @return array
 */
        public function fields() : array
        {
            return $this->bean->with('order by seqn,name')->ownFormfield;
        }
/**
 * Return the form's fields by sequence
 *
 * Some fields deliberately share sequence numbers (e.g. checkboxes in a row)
 *
 * @return array
 */
        public function sequence() : array
        {
            $res = [];
            foreach ($this->fields() as $fld)
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
        public function resequence() : void
        {
            $seqn = 10;
            foreach ($this->sequence() as $flds)
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
 * @param \Support\Context    $context The context object
 * @param array    $rest     Not used here at the moment
 * 
 * @return void
 */
        public function startEdit(Context $context, array $rest) : void
        {
            $context->local()->addval('flags', self::$flags);
        }
/**
 * Handle a form edit
 *
 * @see Framework\Pages\Admin
 *
 * @param \Support\Context    $context  The context object
 *
 * @return array
 */
        public function edit(Context $context) : array
        {
            $fdt = $context->formdata();
            $emess = $this->dofields($fdt);

            foreach ($fdt->posta('new') as $ix => $fid)
            {
                if (($type = $fdt->post(['type', $ix], '')) !== '')
                {
                    $fld = \R::dispense(FW::FORMFIELD);
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
 * @param \Support\Context $context
 * @param array $rest
 *
 * @return void
 */
        public function view(Context $context, array $rest) : void
        {
        }
/**
 * Render a form
 *
 * @param array    $values Values to enter into form
 * @param bool  $noform If TRUE then do not put out the <form> and </form> tags - useful when building forms in parts
 *
 * @return string
 */
        public function render($values = [], bool $noform = FALSE) : string
        {
            if (!$noform || $this->bean->method == 0)
            {
                $form = '<form action="'.
                    ($this->bean->action === '' ? '#' : $this->bean->action).'" '.
                    ($this->bean->class !== '' ? (' class="'.$this->bean->class.'"') : '').' '.
                    ($this->bean->idval !== '' ? (' id="'.$this->bean->idval.'"') : '').' '.
                    'method="'.self::$methods[$this->bean->method].'"'.
                    ($this->bean->multipart ? ' enctype="multipart/form-data"' : '').
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
                    $crlabel = '<label'.$fld->fieldAttr('', FALSE).'>'.$fld->label.'</label>'; // make the label
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
                    $this->optgroup = FALSE;
                    foreach ($values[$fld->name] as $option)
                    {
                        $form .= $this->doOption($option);
                    }
                    /** @psalm-suppress TypeDoesNotContainType */
                    if ($this->optgroup)
                    { # close any open optgroup
                        $form .= '</optgroup>';
                    }
                    $form .= '</select></div>';
                    break;
                case 'textarea':
                    $form .= '<div class="form-group">'.$fld->doLabel(TRUE).'<textarea'.$fld->fieldAttr('form-control', FALSE).'>'.($values[$fld->name] ?? $fld->value).'</textarea></div>';
                    break;
                case 'submit' :
                    $form .= '<div class="form-group"><button'.$fld->fieldAttr('', FALSE).'>'.$fld->value.'</button></div>';
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
 * handle an option
 *
 * @param mixed $option
 *
 * @return string
 */
        private function doOption($option) : string
        {
            $form = '';
            if (is_object($option))
            {
                if (isset($option->optgroup))
                {
                    if ($this->optgroup)
                    { # one open already so close it
                        $form = '</optgroup>';
                    }
                    if ($option->optgroup !== '') # If the name is empty then we want to close an open optgroup without startng a new one
                    {
                        $this->optgroup = TRUE;
                        return $form.'<optgroup label="'.$option->optgroup.'"'.(isset($option->disabled) ? ' disabled="disabled"' : '').'>';
                    }
                }
                else
                {
                    return $this->mkoption($option->value, $option->text, isset($option->selected), isset($option->disabled));
                }
            }
            elseif (is_array($option))
            {
                if ($option[0] === NULL)
                {
                    if ($this->optgroup)
                    { # one open already so close it
                        $form = '</optgroup>';
                    }
                    if ($option[1] !== NULL) # If the name is also NULL then we want to close an open optgroup without startng a new one
                    {
                        $this->optgroup = TRUE;
                        return $form.'<optgroup label="'.$option[1].'"'.(isset($option[2]) ? ' disabled="disabled"' : '').'>';
                    }
                }
                else
                {
                    return $this->mkoption($option[0], $option[1], isset($option[2]), isset($option[3]));
                }
            }
            return $this->mkoption($option, $option, FALSE, FALSE);
        }
/**
 * Make an option tag
 *
 * @param string $value
 * @param string $text
 * @param boolean $selected
 * @param boolean $disabled
 *
 * @return string
 */
        private function mkOption($value, $text, $selected, $disabled) : string
        {
            return '<option value="'.$value.'"'.($disabled ? ' disabled="disabled"' : '').($selected? ' selected="selected"' : '').'>'.$text.'</option>';
        }
/**
 * Add a new form, called when adding a new form via ajax
 *
 * @see Framework\Ajax::bean
 *
 * @param \Support\Context    $context  The context object
 *
 * @return \RedBeanPHP\OODBBean
 */
        public static function add(Context $context) : \RedBeanPHP\OODBBean
        {
            $fdt = $context->formdata();
            $p = \R::dispense(FW::FORM);
            $p->name = $fdt->mustpost('name');
            $p->action = $fdt->mustpost('action');
            $p->class = $fdt->mustpost('class');
            $p->idval = $fdt->mustpost('idval');
            $p->method = $fdt->mustpost('method');
            $p->multipart = $fdt->post('multipart', 0);
            \R::store($p);
            return $p;
        }
    }
?>

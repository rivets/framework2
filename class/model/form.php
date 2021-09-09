<?php
/**
 * A model class for the RedBean object Form
 *
 * !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!! This is a Framework system class - do not edit !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2016-2021 Newcastle University
 * @package Framework\Model
 */
    namespace Model;

    use \Config\Config;
    use \Config\Framework as FW;
    use \Support\Context;
/**
 * A class implementing a RedBean model for Form beans
 * @psalm-suppress UnusedClass
 */
    final class Form extends \RedBeanPHP\SimpleModel
    {
/**
 * @var array<string> METHOD options for forms
 */
        private static array $methods     = ['', 'GET', 'POST'];
/**
 * @var array<string> Attributes for inputs
 * @phpcsSuppress SlevomatCodingStandard.Classes.UnusedPrivateElements
 */
        private static array $attributes  = ['type', 'class', 'name', 'placeholder'];
/**
 * @var array<array<bool>>   Key is name of field and the array contains flags for checks
 * @phpcsSuppress SlevomatCodingStandard.Classes.UnusedPrivateElements
 */
        private static array $editfields = [
            'name'            => [TRUE, FALSE],         // [NOTEMPTY, CHECK/RADIO]
            'action'          => [TRUE, FALSE],
            'method'          => [TRUE, FALSE],
            'idval'           => [FALSE, FALSE],
            'class'           => [FALSE, FALSE],
            'multipart'       => [FALSE, TRUE],
        ];
/**
 * @var array The kinds of flags that fields can have
 */
        private static array $flags = [
            'checked'       => ['Checked', TRUE, 0x01],
            'disabled'      => ['Disabled', FALSE, 0x02],
            'multiple'      => ['Multiple', TRUE, 0x04],
            'readonly'      => ['Readonly', FALSE, 0x08],
            'required'      => ['Required', FALSE, 0x10],
        ];
/**
 * @var bool flag to indicate inside optgroup - nested optgroups are NOT supported (at the moment)
 */
        private bool $optgroup = FALSE;

        use \ModelExtend\FWEdit;
        use \ModelExtend\MakeGuard;
/**
 * Return the form name
 */
        public function name() : string
        {
            return $this->bean->name;
        }
/**
 * Return the form's method
 */
        public function method() : string
        {
            return $this->bean->method;
        }
/**
 * Return the form's fields
 */
        public function fields() : array
        {
            return $this->bean->with('order by seqn,name')->ownFormfield;
        }
/**
 * Return the form's fields by sequence
 *
 * Some fields deliberately share sequence numbers (e.g. checkboxes in a row)
 */
        public function sequence() : array
        {
            $res = [];
            array_walk($this->fields(), static function ($fld) use ($res) {
                $sqn = \explode('/', $fld->seqn);
                if (\count($sqn) > 1)
                { // there are sub orderings in here
                    $res[$sqn[0]][$sqn[1]] = $fld;
                }
                else
                {
                    $res[$sqn[0]][] = $fld;
                }
            });
            return $res;
        }
/**
 * Resequence the fields so that they are all multiples of 10
 *
 * Remember that some items deliberatley share sequence numbers!
 *
 * @todo support resequencing of sub-orderings
 */
        public function resequence() : void
        {
            $seqn = 10;
            array_walk($this->sequence(), static function($flds) use (&$seqn) {
                array_walk($flds, static function($fld) use ($seqn) {
                    $sqn = \explode('/', $fld->seqn);
                    $sqn[0] = $seqn;
                    $fld->seqn = \implode('/', $sqn);
                    \R::store($fld);
                });
                $seqn += 10;
            });
            //foreach ($this->sequence() as $flds)
            //{
            //    foreach ($flds as $fld)
            //    {
            //        $sqn = \explode('/', $fld->seqn);
            //        $sqn[0] = $seqn;
            //        $fld->seqn = \implode('/', $sqn);
            //        \R::store($fld);
            //    }
            //    $seqn += 10;
            //}
        }
/**
 * Setup for an edit
 *
 * @see Framework\Pages\Admin
 * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter
 */
        public function startEdit(Context $context, array $rest) : void
        {
            $context->local()->addval('flags', self::$flags);
        }
/**
 * Handle a form edit
 *
 * @see Framework\Pages\Admin
 */
        public function edit(Context $context) : array
        {
            $fdt = $context->formdata('post');
            $emess = $this->dofields($fdt);

            foreach ($fdt->fetchArray('new') as $ix => $fid) // @phan-suppress-current-line PhanUnusedVariableValueOfForeachWithKey
            {
                if (($type = $fdt->fetch(['type', $ix], '')) !== '')
                {
                    $fld = \R::dispense(FW::FORMFIELD);
                    $fld->type = $type;
                    foreach (['label', 'name', 'class', 'idval', 'placeholder', 'value', 'other', 'seqn'] as $fname)
                    {
                        $fld->$fname = $fdt->fetch(['fld'.$fname, $ix], '');
                    }
                    $fld->flags = 0;
                    foreach (self::$flags as $fn => $fv) // @phan-suppress-current-line PhanUnusedVariableValueOfForeachWithKey
                    {
                        $fld->flags |= $fdt->fetch(['fld'.$fn, $ix], 0);
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
 * @psalm-suppress PossiblyUnusedParameter
 * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter
 */
        public function view(Context $context, array $rest) : void
        {
        }
/**
 * Render a form
 *
 * @param $values Values to enter into form
 * @param $noform If TRUE then do not put out the <form> and </form> tags - useful when building forms in parts
 */
        public function render(array $values = [], bool $noform = FALSE) : string
        {
            if (!$noform || $this->bean->method == 0)
            {
                $form = '<form action="'.
                    ($this->bean->action === '' ? '#' : $this->bean->action).'" '.
                    ($this->bean->class !== '' ? (' class="'.$this->bean->class.'"') : '').
                    ($this->bean->idval !== '' ? (' id="'.$this->bean->idval.'"') : '').
                    ' method="'.self::$methods[$this->bean->method].'"'.
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
                $fld = \reset($flds);
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
                    \array_shift($flds); // pop off the label- the rest will be checkboxes or radios
                    // no break
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
                        $form .= $fld->doLabel(FALSE, 'form-check-label mr-2', $input); // need to do this first as it might set the label field in $fld
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
                    { // close any open optgroup
                        $form .= '</optgroup>';
                    }
                    $form .= '</select></div>';
                    break;
                case 'textarea':
                    $form .= '<div class="form-group">'.$fld->doLabel(TRUE).'<textarea'.$fld->fieldAttr('form-control', FALSE).'>'.($values[$fld->name] ?? $fld->value).'</textarea></div>';
                    break;
                case 'recaptcha':
                    /** @psalm-suppress UndefinedConstant */
                    if (Config::RECAPTCHA != 0)
                    {
                        $form .= '<div class="form-group"><button '.$fld->fieldAttr('', FALSE).' data-sitekey="'.Config::RECAPTCHAKEY.'">'.$fld->value.'</button>';
                        break;
                    }
                    // no break
                case 'submit':
                case 'button':
                    $form .= '<div class="form-group"><button'.$fld->fieldAttr('', FALSE).'>'.$fld->value.'</button></div>';
                    break;
                default: // all the other types are very much the same.
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
            return $noform || $this->bean->method == 0 ? $form : $form.'</form>';
        }
/**
 * Handle making an option. Deals with optgroups
 */
        private function doOption(object|array $option) : string
        {
            $form = '';
            if (\is_object($option))
            {
                if (isset($option->optgroup))
                {
                    if ($this->optgroup)
                    { // one open already so close it
                        $form = '</optgroup>';
                    }
                    if ($option->optgroup !== '') // If the name is not empty then we want to close an open optgroup and start a new one
                    {
                        $this->optgroup = TRUE;
                        return $form.'<optgroup label="'.$option->optgroup.'"'.(isset($option->disabled) ? ' disabled="disabled"' : '').'>';
                    }
                }
                return $this->mkoption($option->value, $option->text, isset($option->selected), isset($option->disabled));
            }
            assert(\is_array($option)); // $options must be an array if we get here
            if ($option[0] === NULL)
            {
                if ($this->optgroup)
                { // one open already so close it
                    $form = '</optgroup>';
                }
                if ($option[1] !== NULL) // If the name is also NULL then we want to close an open optgroup without startng a new one
                {
                    $this->optgroup = TRUE;
                    return $form.'<optgroup label="'.$option[1].'"'.(isset($option[2]) ? ' disabled="disabled"' : '').'>';
                }
            }
            return $this->mkoption($option[0], $option[1], isset($option[2]), isset($option[3]));
        }
/**
 * Make an option tag
 */
        private function mkOption(string $value, string $text, bool $selected, bool $disabled) : string
        {
            return '<option value="'.$value.'"'.($disabled ? ' disabled="disabled"' : '').($selected ? ' selected="selected"' : '').'>'.$text.'</option>';
        }
/**
 * Add a new form, called when adding a new form via ajax
 *
 * @see Framework\Ajax::bean
 */
        public static function add(Context $context) : \RedBeanPHP\OODBBean
        {
            $fdt = $context->formdata('post');
            $p = \R::dispense(FW::FORM);
            foreach (['name', 'action', 'class', 'idval', 'method'] as $fld)
            {
                $p->{$fld} = $fdt->mustFetch($fld);
            }
            $p->multipart = $fdt->fetch('multipart', 0);
            \R::store($p);
            return $p;
        }
    }
?>
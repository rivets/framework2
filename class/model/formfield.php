<?php
/**
 * A model class for the RedBean object FormField
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2016 Newcastle University
 *
 */
    namespace Model;
/**
 * A class implementing a RedBean model for Form beans
 */
    class FormField extends \RedBeanPHP\SimpleModel
    {
        private static $attributes  = ['type', 'class', 'name', 'placeholder'];
        
        private $lcount             = 1;
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
 * Handle a label
 *
 * @param object    $fld    The field
 * @param string    $class  The class name
 *
 * @return string   The field idval might be updated also
 */
        private function doLabel($class = '', $inp = '')
        {
            $label = '';
            if ($this->bean->label !== '')
            {
                if ($this->bean->idval == '')
                {
                    $this->bean->idval = $this->bean->name.$this->lcount;
                    $this->lcount += 1;
                }
                return '<label for="'.$this->bean->idval.'"'.($class !== '' ? (' class="'.$class.'"') : '').'>'.$inp.$this->bean->label.'</label>';
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
        private function fieldAttr($class, $doValue = TRUE)
        {
            $attrs = self::$attributes;
            if ($doValue)
            { # include the value in the attributes
                $attrs[] = 'value';
            }
            $res = ['']; # ensures a space at the start of the result
            if ($fthis->bean->idval !== '')
            {
                $res[] = 'id="'.$this->bean->idval.'"';
            }
            if ($class !== '')
            { # add a standard class
                $this->bean->class = trim($class.' '.$this->bean->class);
            }
            foreach ($attrs as $atr)
            {
                if ($this->bean->$atr !== '')
                {
                    $res[] = $atr.'="'.$fld->$atr.'"';
                }
            }
            foreach (self::$flags as $atr)
            {
                if ($this->bean->flags & $atr[2])
                {
                    $res[] = $atr[0].'="'.$atr[0].'"';
                }
            }
            return implode(' ', $res);
        }
    }
?>
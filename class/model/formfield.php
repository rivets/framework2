<?php
/**
 * A model class for the RedBean object FormField
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2016-2019 Newcastle University
 */
    namespace Model;
/**
 * A class implementing a RedBean model for Form beans
 */
    class FormField extends \RedBeanPHP\SimpleModel
    {
/**
 * @var string[] Attributes that this supports
 */
        private static $attributes  = ['class', 'name', 'placeholder'];
 /**
  * @var int Counter used for generating new IDs
  */
        private static $lcount             = 1;
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
 * @param bool       $makefor    If TRUE then make a for attribute
 * @param string     $class      The class name
 * @param string     $inp        The input HTML
 *
 * @return string   The field idval might be updated also
 */
        public function doLabel(bool $makefor = TRUE, string $class = '', string $inp = '') : string
        {
            if ($this->bean->label !== '')
            {
                if ($makefor && $this->bean->idval === '')
                {
                    $this->bean->idval = 'xxid'.self::$lcount;
                    self::$lcount += 1;
                }
                return '<label'.($this->bean->idval !== '' ? ' for="'.$this->bean->idval.'"' : '').
                    ($class !== '' ? (' class="'.$class.'"') : '').'>'.$inp.$this->bean->label.'</label>';
            }
            return '';
        }
/**
 * Render a field's attributes
 *
 * @param string    $class    The class name
 * @param bool      $doValue  If TRUE Then add a value attribute
 *
 * @return string
 */
        public function fieldAttr(string $class, bool $doValue = TRUE) : string
        {
            $attrs = self::$attributes;
            if ($doValue)
            { # include the value in the attributes
                $attrs[] = 'value';
            }
            switch ($this->bean->type)
            {
            case 'textarea':
                break;
            default:
                $attrs[] = 'type';
                break;
            }
            $res = ['']; # ensures a space at the start of the result
            if ($this->bean->idval !== '')
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
                    $res[] = $atr.'="'.$this->bean->$atr.'"';
                }
            }
            foreach (self::$flags as $atr)
            {
                if ($this->bean->flags & $atr[2])
                {
                    $res[] = $atr[0].'="'.$atr[0].'"';
                }
            }
            if ($this->bean->other !== '')
            {
                $res[] = $this->bean->other;
            }
            return implode(' ', $res);
        }
    }
?>
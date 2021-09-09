<?php
/**
 * A trait that allows extending the model class for edit functionality for
 *
 * Add any new methods you want the User bean to have here.
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2018-2021 Newcastle University
 * @package Framework\ModelExtend
 */
    namespace ModelExtend;

/**
 * User table stores info about users of the syste,
 */
    trait FWEdit
    {
/**
 * Handle editing of beans
 *
 * @return array<string>
 */
        private function dofields(\Framework\FormData\AccessBase $fdt) : array
        {
            $emess = [];
            foreach (self::$editfields as $fld => $flags) // @phan-suppress-current-line PhanUndeclaredStaticProperty
            {
                if ($flags[1])
                { // this is a checkbox - they can't be required
                    $val = $fdt->fetch($fld, 0);
                }
                else
                {
                    $val = $fdt->fetch($fld, '');
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
            return $emess;
        }
    }
?>
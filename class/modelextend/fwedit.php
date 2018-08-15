<?php
/**
 * A trait that allows extending the model class for edit functionality for 
 *
 * Add any new methods you want the User bean to have here.
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2018 Newcastle University
 *
 */
    namespace ModelExtend;
/**
 * User table stores info about users of the syste,
 */
    trait FWEdit
    {
        private function dofields($fdt)
        {
            $emess = [];
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
            return $emess;
        }
    }
?>
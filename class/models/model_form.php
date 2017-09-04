<?php
/**
 * A model class for the RedBean object Form
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2016 Newcastle University
 *
 */
/**
 * A class implementing a RedBean model for Form beans
 */
    class Model_Form extends \RedBeanPHP\SimpleModel
    {
/**
 * Return the forms fields
 *
 * @return object
 */
        public function fields()
        {
	    return $this->bean->ownFormfield;
        }
    }
?>

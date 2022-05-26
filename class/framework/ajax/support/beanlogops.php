<?php
/**
 * An enum implementing BeanLog operation names
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2022 Newcastle University
 * @package Framework\Framework\Ajax\Support
 */
    namespace Framework\Ajax\Support;

/**
 * Enumeration for beanlog constants
 */
    enum BeanLogOps : int
    {
        case CREATE = 0;
        case UPDATE = 1;
        case DELETE = 2;

        public function label() : string
        {
            return match($this) {
                static::CREATE => 'Create',
                static::UPDATE => 'Update',
                static::DELETE => 'Delete',
            };
        }
    }
?>
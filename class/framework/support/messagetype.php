<?php
/**
 * An enum implementing Framework message type values
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2022 Newcastle University
 * @package Framework\Framework\Ajax\Support
 */
    namespace Framework\Support;

/**
 * Enumeration for beanlog constants
 */
    enum MessageType : int
    {
        case ERROR   = 0;
        case WARNING = 1;
        case MESSAGE = 2;
    }
?>
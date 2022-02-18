<?php
/**
 * An enum implementing dispatch control operation names
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2022 Newcastle University
 * @package Framework\Framework\Ajax\Support
 */
    namespace Framework\Support;
/**
 * Enumeration for beanlog constants
 */
    enum DispatchOps : int
    {
        case OBJECT     = 1; // Indicates that there is an Object that handles the call
        case TEMPLATE   = 2; // Indicates that there is only a template for this URL.
        case REDIRECT   = 3; // Indicates that the URL should be temporarily redirected - 302
        case REHOME     = 4; // Indicates that the URL should be permanent redirected - 301
        case XREDIRECT  = 5; // Indicates that the URL should be permanently redirected - 302
        case XREHOME    = 6; // Indicates that the URL should be temporarily redirected -301
        case REDIRECT3  = 7; // Indicates that the URL should be temporarily redirected - 303
        case XREDIRECT3 = 8; // Indicates that the URL should be temporarily redirected - 303
        case REDIRECT7  = 9; // Indicates that the URL should be temporarily redirected - 307
        case XREDIRECT7 = 10; // Indicates that the URL should be temporarily redirected - 307
        case REHOME8    = 11; // Indicates that the URL should be permanently redirected - 308
        case XREHOME8   = 12; // Indicates that the URL should be permanently redirected - 308
    }
?>
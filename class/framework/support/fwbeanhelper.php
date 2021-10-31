<?php
/**
 * Class for extending Model searching for redBean
 *
 * @link https://gist.github.com/Lynesth/e6641e2809b549bd95c79affe7535da3
 *
 * @author https://gist.github.com/Lynesth
 * Modified by Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 *
 * @package Framework\Support
 */
    namespace Framework\Support;

    use \Config\Framework as FW;
/**
 * Find models for FW beans
 */
    class FWBeanHelper extends \RedBeanPHP\BeanHelper\SimpleFacadeBeanHelper
    {
        static $path = '\\Framework\\Model\\';
        static $beans = [
            FW::CONFIG      => '',
            FW::CSP         => '',
            FW::FORM        => '',
            FW::FORMFIELD   => '',
            FW::PAGE        => '',
            FW::PAGEROLE    => '',
            FW::ROLE        => '',
            FW::ROLECONTEXT => '',
            FW::ROLENAME    => '',
            //FW::TABLE       => '',
            //FW::TEST        => '',
            FW::UPLOAD      => '',
            FW::USER        => '',
        ];

        final public function getModelForBean(\RedBeanPHP\OODBBean $bean)
        {
            $type = $bean->getMeta('type');
            if (!empty(self::$paths[$type]))
            {
                $modelName = $path.$type;
                $obj = self::factory($modelName);
                $obj->loadBean($bean);
                return $obj;
            }
            return parent::getModelForBean($bean);
        }
    }
?>
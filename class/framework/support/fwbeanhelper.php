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
        final public function getModelForBean(\RedBeanPHP\OODBBean $bean)
        {
            $type = $bean->getMeta('type');
            if (FW::isFWBean($type))
            {
                if (\file_exists(\Support\Context::getInstance()->local()->makeBasePath('class', 'framework', 'model', $type.'.php')))
                {
                    $obj = self::factory(FW::MODELPATH.$type);
                    $obj->loadBean($bean);
                    return $obj;
                }
                return NULL;
            }
            return parent::getModelForBean($bean);
        }
    }
?>
<?php
/**
 * Class to handle the Framework AJAX upload operation
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2021-2024 Newcastle University
 * @package Framework
 * @subpackage SystemAjax
 */
    namespace Framework\Ajax;

    use \Config\Framework as FW;
/**
 * Parsely unique check that does require a login.
 */
    class Upload extends Ajax
    {
/**
 * @var array<mixed>
 * @phpcsSuppress SlevomatCodingStandard.Classes.UnusedPrivateElements
 */
        private static $permissions = [];
/**
 * Upload files and optionally attch them to a set of beans
 *
 * /ajax/upload/{bean type}/{bean id}/...
 */
        final public function handle() : void
        {
            $context = $this->context;
            if (!$context->web()->isPost())
            {
                throw new \Framework\Exception\BadOperation('Operation not supported');
            }

            $rest = $context->rest();
            $beans = [];
            $stable = [];
            if (\count($rest) > 1)
            { // there are beans to attach too.
                $ix = 1;
                while (isset($rest[$ix]))
                {
                    $beanType = $rest[$ix];
                    if (FW::isFWTable($beanType))
                    {
                        throw new \Framework\Exception\BadValue('Cannot attach to framework beans');
                    }
                    $stable[] = $beanType < FW::UPLOAD ? $beanType.'_'.FW::UPLOAD : FW::UPLOAD.'_'.$beanType;
                    $ix += 1;
                    if (!isset($rest[$ix]))
                    {
                        throw new \Framework\Exception\ParameterCount('Missing field');
                    }
                    $beans[] = $this->context->load($beanType, (int) $rest[$ix]);
                    $ix += 1;
                }
            }
            $uploads = [];
            $uplid = [];
            $emess = '';
            $fdt = $context->formData('file');
            try
            {
                foreach ($fdt->fileArray('file') as $file) // @phan-suppress-current-line PhanUndeclaredMethod
                {
                    $upl = \R::dispense(FW::UPLOAD);
                    if (!$upl->savefile($context, $file, FALSE, $context->user(), 0))
                    {
                        $emess = 'Upload failed '.$file['name'].' '.$file['size'].' '.$file['error'];
                        break;
                    }
                    $uploads[] = $upl;
                    $uplid[] = $upl->getID();
                }
                if ($emess != '')
                {
                    \R::trashAll($uploads); //get rid of any loaded successfully.
                    throw new \Framework\Exception\BadValue($emess);
                }
                $data = NULL;
                $fdp = $context->formData('post');
                if ($fdp->hasForm())
                { // there is link data to be added
                    $pdt = $fdp->fetchRaw();
                    $data = \array_fill(0, \count($pdt), []);
                    $ucount = \count($uploads);
                    foreach ($pdt as $key => $value)
                    { // get all the link data
                        if (\is_array($value))
                        { // this one is an array so it must be one item for each Upload
                            if (\count($value) != $ucount)
                            {
                                \R::trashAll($uploads); //get rid of any loaded successfully.
                                throw new \Framework\Exception\BadValue($key.': wrong number of values');
                            }
                            foreach ($value as $ix => $v)
                            {
                                $data[$ix][$key] = $v;
                            }
                        }
                        else
                        { // not array so same value for all uploads.
                            foreach (\range(0, $ucount-1) as $ix)
                            {
                                $data[$ix][$key] = $value;
                            }
                        }
                    }
                }
                foreach ($beans as $ix => $bean)
                { // attach the uploads to the beans
                    foreach ($uploads as $ix => $upl)
                    { // attach the uploads to the beans and add any link data
                        if ($data !== NULL)
                        {
                            $bean->link($stable[$ix], $data[$ix])->{FW::UPLOAD} = $upl;
                        }
                        else
                        {
                            $bean->noload()->{'shared'.\ucfirst(FW::UPLOAD).'List'}[] = $upl;
                        }
                    }
                    \R::store($bean);
                }
            }
            catch (\Framework\Exception\BadValue $e)
            {
                throw $e;
            }
            catch (\Throwable $e)
            {
                throw new \Framework\Exception\InternalError($e->getMessage());
            }
            $context->web()->sendJSON($uplid);
        }
    }
?>
<?php
/**
 * A class that handles the Attach AJAX operation
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2020-2021 Newcastle University
 * @package Framework\Ajax
 */
    namespace Ajax;

    use \Config\Framework as FW;
/**
 * Attach operation
 *
 * It expects a URL of the form /ajax/attach/BEAN_TYPE/BEAN_ID and to have an array of files
 * identified by the name file[] on the inputs (or input if you are using multiple)
 */
    final class Attach extends \Framework\Ajax\Ajax
    {
/**
 * @var array<mixed> If you want to use the permission checking functions. If you just want to control access
 *                   then just put the list of contextname/rolename pairs in the result of requires.
 * @phpcsSuppress SlevomatCodingStandard.Classes.UnusedPrivateElements
 */
         private static array $permissions = [];
/**
 * Return permission requirements.
 *
 * The version in the base class requires logins and adds nothing else.
 * If that is what you need then you can remove this method. This function is called from the base
 * class constructor when it does some permission checking. The first element in the returned array
 * is a boolean indicating whether or not login is required, the second element is a list of ['Context', 'Role']
 * pairs that the user must have.
 */
//        public function requires() : array
//        {
//            return [TRUE, []];
//        }
/**
 *  files and associate them with a bean
 *
 * The AJAX call is of the form /ajax/attach/<bean type>/<bean id>/ and the file data is passed
 * in through the usual file mechanism. This code assume sthat there is also a POST value called
 * descr associated with each file - if you don't need this then make the change documented below
 *
 * @see \Framework\Model\FWUpload
 */
        public function handle() : void
        {
            $context = $this->context;
            $rest = $context->rest();
            $type = \strtolower($rest[1]);
            $bean = $context->load($type, (int) $rest[2]);
            $fdt = $this->context->formdata('file');
            $table = $type < FW::UPLOAD ? $type.'_'.FW::UPLOAD : FW::UPLOAD.'_'.$type; // get names in right order for RedBean
            foreach ($fdt->fileArray('file') as $file) // @phan-suppress-current-line PhanUndeclaredMethod
            {
                $upl = \R::dispense(FW::UPLOAD);
                if (!$upl->savefile($context, $file, FALSE, $context->user(), 0))
                {
                    throw new \Framework\Exception\BadValue(' failed '.$file['name'].' '.$file['size'].' '.$file['error']);
                }
                $bean->link($table, ['descr' => $context->formdata('post')->mustfetch('descr')])->{FW::UPLOAD} = $upl; // if you want the descr field
                //$bean->sharedUploadList[] = $upl; // if you haven't got anything to add
            }

            \R::store($bean);
        }
    }
?>
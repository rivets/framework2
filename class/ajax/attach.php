<?php
/**
 * A class that handles the Attach AJAX operation
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2020 Newcastle University
 */
    namespace Ajax;

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
 *            then just put the list of contextname/rolename pairs in the result of requires.
 * @phpcsSuppress SlevomatCodingStandard.Classes.UnusedPrivateElements
 */
         private static $permissions = [];
/**
 * Return permission requirements. The version in the base class requires logins and adds nothing else.
 * If that is what you need then you can remove this method. This function is called from the base
 * class constructor when it does some permission checking.
 *
 * @return array<mixed>
 */
        public function requires() : array
        {
            return [TRUE, []];
        }
/**
 * Upload a file for a note
 *
 * @return void
 */
        public function handle() : void
        {
            $context = $this->context;
            $rest = $context->rest();
            $type = strtolower($rest[1]);
            $bean = $context->load($type, $rest[2]);
            $fdt = $this->context->formdata('file');
            $table = $type < 'upload' ? $type.'_upload' : 'upload_'.$type;
            foreach ($fdt->fileArray('file') as $file)
            {
                $upl = \R::dispense('upload');
                if (!$upl->savefile($context, $file, FALSE, $context->user(), 0))
                {
                    throw new \Framework\Exception\BadValue('upload failed '.$file['name'].' '.$file['size'].' '.$file['error']);
                }
                $bean->link($table, ['descr' => $context->formdata('post')->mustfetch('descr')])->upload = $upl;
                //$bean->sharedUploadList[] = $upl; // if you haven't got anything to add
            }

            \R::store($bean);
        }
    }
?>
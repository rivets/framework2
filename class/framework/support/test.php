<?php
/**
 * Contains definition of Test class
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2020 Newcastle University
 */
    namespace Framework\Support;

    use \Config\Framework as FW;
    use \Support\Context;
/**
 * A class that handles various site testing related things
 */
    class Test
    {
        private $local = NULL;
        private $fdt   = NULL;
/**
 * Test AJAX functions
 *
 * @param Context $context  The site context object
 *
 * @return string
 */
        public function ajax(Context $context) : string
        {
            $context->local()->addval('bean', \R::findOrCreate(FW::TEST, ['f1' => 'a string', 'tog' => 1]));
            return '@devel/testajax.twig';
        }
/**
 * Test failed assertion handling
 *
 * @param Context $context  The site context object
 *
 * @return void
 */
        public function assert(Context $context) : string
        {
            assert(TRUE == FALSE);
            $context->local()->message(\Framework\Local::ERROR, 'Assertion test : this should not be reached');
            return '@devel/devel.twig';
        }
/**
 * Test run time error handling
 *
 * @param Context $context  The site context object
 *
 * @return string
 */
        public function fail(Context $context) : string
        {
            2 / 0;
            $context->local()->message(\Framework\Local::ERROR, 'Failure test : this should not be reached');
            return '@devel/devel.twig';
        }
/**
 * Test the FormData Get functions
 *
 * @param Context $context  The site context object
 *
 * @return string
 */
        public function get(Context $context) : string
        {
            $test = (new \Framework\Support\TestSupport($context, 'GET'))->run([
                ['hasget', ['exist'], TRUE, TRUE],
                ['hasget', ['notexist'], FALSE, FALSE],
                ['get', ['exist', 0], '42', TRUE],
                ['get', ['notexist', 0], 0, FALSE],
                ['mustget', ['exist', 0], '42', TRUE],
                ['mustget', ['notexist', 0], '42', FALSE],
                ['get', [['aexist', 0], 0], '42', TRUE],
                ['get', [['aexist', 3], 0], 0, FALSE],
                ['mustget', [['aexist', 1], 0],'42', TRUE],
                ['mustget', [['aexist', 3], 0], '42', FALSE],  
            ]);
            return '@devel/get.twig';
        }
/**
 * Test the FormData Post functions
 *
 * @param Context $context  The site context object
 *
 * @return string
 */
        public function post(Context $context) : string
        {
            return '@devel/post.twig';
        }
/**
 * Test the FormData Put functions
 *
 * @param Context $context  The site context object
 *
 * @return string
 */
        public function put(Context $context) : string
        {
            return '@devel/devel.twig';
        }
/**
 * Test the FormData Cookie functions
 *
 * @param Context $context  The site context object
 *
 * @return string
 */
        public function cookie(Context $context) : string
        {
            return '@devel/devel.twig';
        }
/**
 * Test the FormData File functions
 *
 * @param Context $context  The site context object
 *
 * @return string
 */
        public function file(Context $context) : string
        {
            return '@devel/devel.twig';
        }

/**
 * Test mail
 *
 * @param Context $context  The site context object
 *
 * @return string
 */
        public function mail(Context $context) : string
        {
/**
 * @psalm-suppress PossiblyNullPropertyFetch
 * @psalm-suppress PossiblyNullArgument
 */
            $msg = $context->local()->sendmail([$context->user()->email], 'test', 'test');
            if ($msg === '')
            {
                $context->local()->message(\Framework\Local::MESSAGE, 'sent');
            }
            else
            {
                $context->local()->message(\Framework\Local::ERROR, $msg);
            }
            return '@devel/devel.twig';
        }
/**
 * Generate a test page. This tests various twig macros etc.
 *
 * @param Context $context  The site context object
 *
 * @return string
 */
        public function page(Context $context) : string
        {
            $context->local()->message(\Framework\Local::ERROR, 'Error 1');
            $context->local()->message(\Framework\Local::ERROR, 'Error 2');
            $context->local()->message(\Framework\Local::WARNING, 'Warning 1');
            $context->local()->message(\Framework\Local::WARNING, 'Warning 2');
            $context->local()->message(\Framework\Local::MESSAGE, 'Message 1');
            $context->local()->message(\Framework\Local::MESSAGE, 'Message 2');
            return '@devel/test.twig';
        }
/**
 * Throw an unhandled exception. Tests exception handling.
 *
 * @param Context $context  The site context object
 *
 * @throws \Exception
 * @return void
 */
        public function toss(Context $context) : string
        {
            throw new \Exception('Unhandled Exception Test');
            $context->local()->message(\Framework\Local::ERROR, 'Throw test : this should not be reached');
            return '@devel/test.twig';
        }
/**
 * Test the upload features
 *
 * @param Context $context  The site context object
 *
 * @return string
 */
        public function upload(Context $context) : string
        {
            $fd = $context->formdata();
            try
            {
                if ($fd->hasfile('upload'))
                {
                    $upl = \R::dispense('upload');
                    $upl->savefile($context, $fd->filedata('upload'), FALSE, $context->user(), 0);
                    $context->local()->addval('download', $upl->getID());
                }
                $rest = $context->rest();
                if (count($rest) == 4)
                {
                    $id = (int) $rest[3];
                    switch ($rest[2])
                    {
                    case 'get':
                        $context->local()->addval('download', $id);
                        break;

                    case'delete':
                        \R::trash($context->load('upload', $id));
                        $context->local()->message(\Framework\Local::MESSAGE, 'Deleted');
                        break;
                    }
                }
            }
            catch (\Exception $e)
            {
                $context->local()->message(\Framework\Local::ERROR, $e->getmessage());
            }
            return '@devel/testupload.twig';
        }
    }
?>
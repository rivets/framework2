<?php
    namespace Framework\Pages;
/**
 * A class that contains code to handle file data fetching requests related requests.
 *
 * This assumes that access control is needed for the files - if it isn't then the files
 * should be stored in a sub-directory of the assets directory (or directories) in the root of the site and
 * the web server will deal with things like range requests etc.
 *
 * As written it assumes that there is a directory in the root of the site whose
 * name is set in the constant DATADIR. It also assumes that there are subdirectories
 * in DATADIR that provide the structure /user_id/year/month/filename
 *
 * This code provides a very simple access control scheme whereby there is an upload database table
 * that relates a filename with a user so that you can check
 * that only the owner (or the admin) can access the file. The table
 * should also contain the original filename that the user used when uploading the file, as this is returned
 * as part of Content-Disposition. Allowing sharing with specified other users, groups of users or users with particular roles
 * would not be hard to add.
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2012-2016 Newcastle University
 *
 */
/**
 * The Getfile class
 *
 * This returns a file requested from the upload area
 */
    class Getfile extends \Framework\Siteaction
    {
	const DATADIR	= 'private';
/**
 * @var string	The name of the file we are working on
 */
	private $file;
/**
 * @var string	The last modified time for the file
 */
	private $mtime;

/**
 * Return data files as requested
 *
 * @param object	$context	The context object for the site
 * @param object	$local		The local object for the site
 *
 * @return string	A template name
 */
	public function handle($context)
	{
	    $web = $context->web(); # it's used all over the place so grab it once

            chdir($context->local()->basedir());
            $fpt = $context->rest();

	    if (count($fpt) == 2 && $fpt[0] == 'file')
	    { # this is access by upload ID
		$file = \R::load('upload', $fpt[1]);
		if ($file->getID() == 0)
		{
		    $web->notfound();
		    /* NOT REACHED */
		}
		$this->file = substr($file->fname, 1); // drop the separator at the start....
	    }
	    else
	    {
		chdir(self::DATADIR);
/**
 * Depending on how you construct the URL, it's possible to do some sanity checks on the
 * values passed in. The structure assumed here is /user_id/year/month/filename so
 * the regexp test following makes sense.
 * This all depends on your application and how you want to treat files and filenames and access of course!
 *
 * ALways be careful that filenames do not have .. in them of course.
 *
 */
		$this->file = implode(DIRECTORY_SEPARATOR, $fpt);
		if (!preg_match('#^[0-9]+/[0-9]+/[0-9]+/[^/]+$#', implode('/', $fpt)))
		{ # filename constructed is not the right format
		    $web->bad();
		    /* NOT REACHED */
		}

    # Now do an access control check
		$file = \R::findOne('upload', 'fname=?',
		    [DIRECTORY_SEPARATOR . self::DATADIR . DIRECTORY_SEPARATOR . $this->file]);
		if (!is_object($file))
		{ # not recorded in the database so 404 it
		    $web->notfound();
		    /* NOT REACHED */
		}
	    }
            if (!$file->canaccess($context->user()))
            { # caurrent user cannot access the file
                $web->noaccess();
		/* NOT REACHED */
            }

	    if (($this->mtime = filemtime($this->file)) === FALSE)
	    {
                $web->internal('Lost File: '.$this->file);
		/* NOT REACHED */
	    }

	    $this->ifmodcheck(); # check to see if we actually need to send anything

	    $web->addheader([
		'Last-Modified'	=> $this->mtime,
		'Etag'		=> '"'.$this->makeetag().'"',
	    ]);
	    $web->sendfile($this->file, $file->filename);
            return '';
	}
/**
 * Make an etag for an item
 *
 * This needs to be overridden by pages that can generate etags
 *
 * @return string
 */
	public function makeetag()
	{
	    return $this->mtime;
	}
/**
 * Get a last modified time for the page
 *
 * By default this returns the current time. For pages that need to use this in anger,
 * then this function needs to be overridden.
 *
 * @return integer
 */
	public function lastmodified()
	{
	    return $this->mtime;
	}
/**
 * Check a timestamp to see if we need to send the page again or not.
 *
 * This always returns FALSE, indicating that we need to send the page again.
 * The assumption is that pages that implement etags will override this function
 * appropriately to do actual value checking.
 *
 * @param integer	$time	The time value to check
 *
 * @return boolean
 */
	public function checkmodtime($time)
	{
	    return $time == $this->mtime;
	}
/**
 * Check an etag to see if we need to send the page again or not.
 *
 * This always returns FALSE, indicating that we need to send the page again.
 * The assumption is that pages that implement etags will override this function
 * appropriately to do actual value checking.
 *
 * @param string	$tag	The etag value to check
 *
 * @return boolean
 */
	public function checketag($tag)
	{
	    return $tag == $this->mtime;
	}
    }
?>

<?php
/**
 * A class that contains code to handle file data fetching requests related requests.
 *
 * This assumes that access control is needed for the files - if it isn't then the files
 * should be stored in a sub-directory of the assets directory (or directories) in the root of the site and
 * the web server will deal with things like range requests etc.
 *
 * As written it assumes that there is a directory in the root of the site whose
 * name is set in the constant DATADIR. It also assumes that there are subdirectories
 * in DATADIR that provide the structure type/user/year/month/filename
 *
 * This code provides a very simple access control scheme whereby there is a database table
 * for each of the file types that relates a filename with a user so that you can check
 * that only the owner (or the admin) can access the file. The table is named by prefixing
 * the type with DBPREFIX. The type is found as the first part of the rest of the URL. The table
 * should also contain the original filename that the user used when uploading the file, as this is returned
 * as part of Content-Disposition.
 *
 * If you want to implement some kind of cache control/expiry then you should generate the headers in the
 * sendheaders method.
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2012-2016 Newcastle University
 *
 */
    class GetFile extends Siteaction
    {
	const DATADIR	= 'data';
	const DBPREFIX	= 'dd';
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
            chdir(implode(DIRECTORY_SEPARATOR, array($_SERVER['DOCUMENT_ROOT'], self::DATADIR)));
            $fpt = $context->rest();
/**
 * Depending on how you construct the URL, it's possible to do some sanity checks on the
 * values passed in. The structure assumed here is type/user_id/year/month/filename so
 * the regexp test following makes sense.
 * This all depends on your application and how you want to treat files and filenames and access of course!
 *
 * ALways be careful that filenames do not have .. in them of course.
 * 
 */
            $this->file = implode(DIRECTORY_SEPARATOR, $fpt);
	    if (!preg_match('#^[a-z]+/[0-9]+/[0-9]+/[0-9]+/[^/]+#i', $this->file))
	    { # filename constructed is not the right format
                Web::getinstance()->bad();		
	    }
	    $this->mtime = filemtime($this->file);

# Now do an access control check
            $type = $fpt[0];
            $user = $context->load('user', $fpt[1]);
            $file = R::findOne(self::DBPREFIX . $type, 'fname=?',
		array(DIRECTORY_SEPARATOR . self::DATADIR . DIRECTORY_SEPARATOR . $this->file));
            if (!is_object($file))
            { # not recorded in the database so 404 it
                Web::getinstance()->notfound();
            }
            if (!$context->sameuser($file->user) && !$context->hasadmin())
            { # not owned by current user and we are not admin so return an error
                Web::getinstance()->noaccess();
            }

            if (!file_exists($this->file))
            { # no such file - but it was in the database! System error
                Web::getinstance()->internal();
            }
 
	    $this->ifmodcheck(); # check to see if we actually need to send anything
 
	    $gz = Web::getinstance()->acceptgzip();
	    $sz = filesize($this->file);

            if (isset($_SERVER['HTTP_RANGE']))
            { # handle range requests. Media players ask for the file in chunks.,
                if (preg_match('/=([0-9]+)-([0-9]*)\s*$/', $_SERVER['HTTP_RANGE'], $m))
                { # split the range request
		    if ($m[1] > $sz)
		    { # start is after end of file
			Web::getinstance()->notsatisfiable();
		    }
                    if (!isset($m[2]) || $m[2] === '')
                    { # no top value specified, so use the filesize (-1 of course!!)
                        $m[2] = $sz - 1;
                    }
		    elseif ($m[2] > $sz-1)
		    { # end is after end of file
			Web::getinstance()->notsatisfiable();			
		    }
		    $this->sendheaders(StatusCodes::HTTP_PARTIAL_CONTENT, $gz ? '' : $m[2] - $m[1] + 1);
                    header('Content-Range: bytes '.$m[1].'-'.$m[2].'/'.$sz);
                    $fd = fopen($this->file, 'r'); # open the file, seek to the required place and read and return the required amount.
                    fseek($fd, $m[1]);
		    if ($gz)
		    {
			ob_start('ob_gzhandler');
		    }
                    echo fread($fd, $m[2]-$m[1]+1);
		    if ($gz)
		    {
			ob_end_flush();
		    }
                    fclose($fd);
                }
                else
                {
                    Web::getinstance()->notsatisfiable();	
                }
            }
	    else
	    {
		$this->sendheaders(StatusCodes::HTTP_OK, $gz ? '' : $sz, $file->filename);
		if ($gz)
		{
		    ob_start('ob_gzhandler');
		}
		readfile($this->file);
		if ($gz)
		{
		    ob_end_flush();
		}
	    }
            return '';
	}
/**
 * Send headers for the file
 *
 * @param integer	$code		The HTTP status code
 * @param mixed		$length		The content length or ''
 * @param string	$name		The file name or ''
 *
 * @return void
 */
	private function sendheaders($code, $length, $name = '')
	{
            Web::getinstance()->sendheaders($code);
	    header('Last-Modified: '.$this->mtime);
	    header('ETag: "'.$this->makeetag().'"');
	    $finfo = finfo_open(FILEINFO_MIME_TYPE);
	    header('Content-Type: '.finfo_file($finfo, $this->file));
	    finfo_close($finfo);
	    if ($length !== '')
	    { # send the length if we are not compressing
		header('Content-Length: '.$length);
	    }
	    if ($name != '')
	    {
		header('Content-Disposition: attachment; filename="'.$file->name.'"');
	    }
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
	    return $filemtime;
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

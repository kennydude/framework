<?php
/**
 * Contains definition of ther Web class
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2012-2015 Newcastle University
 */
/**
 * A class that handles various web related things.
 */
    class Web
    {
        use Singleton;
/**
 * @var array   Holds values for headers that are required. Keyed by the name of the header
 */
        private $headers    = array();
/**
 * Generate a Location header
 *
 * @param string		$where		The URL to divert to
 * @param boolean		$temporary	TRUE if this is a temporary redirect
 * @param string		$msg		A message to add to the reply	
 * @param boolean		$nochange	If TRUE then reply status codes 307 and 308 will be used rather than 301 and 302
 */
	public function relocate($where, $temporary = TRUE, $msg = '', $nochange = FALSE)
	{
	    if ($temporary)
	    {
		$code = $nochange ? StatusCodes::HTTP_TEMPORARY_REDIRECT : StatusCodes::HTTP_FOUND;
	    }
	    else
	    {
/**
 * @todo Check status of 308 code which should be used if nochage is TRUE. May not yet be official.
 */
		$code = StatusCodes::HTTP_MOVED_PERMANENTLY;		
	    }
	    $this->addheader('Location', $where);
	    $this->sendstring($code, $msg, 'text/html');
	    exit;
	}
/**
 * output a header and msg - this never returns
 *
 * @param number	$code	The return code
 * @param string	$msg	The message (or '')
 * @param boolean       $divert If this TRUE and there is a context stored, divert to page /error/XXX
 */
	private function sendhead($code, $msg, $divert = FALSE)
	{
            if ($divert)
            { # divert to an error page, passing the message as a parameter
                Context::getinstance()->divert('/error/'.$code);
		/* NOT REACHED */
            }
	    $this->sendheaders(StatusCodes::httpHeaderFor($code));
	    if ($msg != '')
	    {
		echo '<p>'.$msg.'</p>';
	    }
	    exit;
	}
/**
 * Make a header sequence for a particular return code and add some other useful headers
 *
 * @param integer	$code	The HTTP return code
 * @param string	$mtype	The mime-type of the file
 * @param string 	$debug	A debug message to include in the headers.
 *
 * @return void
 */
	public function sendheaders($code, $mtype = '', $name = '')
	{
	    header(StatusCodes::httpHeaderFor($code));
	    $this->putheaders();
	    if ($mtype != '')
	    {
		header('Content-Type: '.$mtype);
	    }
	    if ($name != '')
	    {
		header('Content-Disposition: attachment; filename="'.$name.'"');
	    }
	}
/**
 * Generate a 400 Bad Request error return
 *
 * @param string		$msg	A message to be sent
 */
	public function bad($msg = '')
	{
	    $this->sendhead(400, $msg);
	}
/**
 * Generate a 403 Access Denied error return
 *
 * @param string	$msg	A message to be sent
 */
	public function noaccess($msg = '')
	{
	    $this->sendhead(403, $msg);
	}
/**
 * Generate a 404 Not Found error return
 *
 * @param string	$msg	A message to be sent
 */
	public function notfound($msg = '')
	{
	    $this->sendhead(404, $msg);
	}
/**
 * Generate a 416 Not Satisfiable error return
 *
 * @param string	$msg	A message to be sent
 */
	public function notsatisfiable($msg = '')
	{
	    $this->sendhead(416, $msg);
	}
/**
 * Generate a 500 Internal Error error return
 *
 * @param string		$msg	A message to be sent
 */
	public function internal($msg = '')
	{
	    $this->sendhead(500, $msg);
	}
/**
 * Generate a 304 header (NOT_MODIFIED) and possibly some other headers and exit
 *
 * @param string	$etag	An etag for this item
 * @param integer	$maxage	A maximum age for the item for use by caches.
 *
 * @return void
 */
    function send304($etag = '', $maxage = '')
    {
	$this->sendheaders(StatusCodes::HTTP_NOT_MODIFIED);
	exit;
    }
/**
 * Deliver a file as a response.
 *
 * @param string	$path	The path to the file
 * @param string	$name	The name of the file as told to the downloader
 * @param string	$mime	The mime type of the file
 * @param array		$range	Specifies if a sub-range of the file is wanted. Used mainly for video.
 *
 * @return void
 */
	public function sendfile($path, $name = '', $mime = '', $range = [])
	{
	    if ($mime === '')
	    {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
		if (($mime = finfo_file($finfo, $path)) === FALSE)
                { # there was an error of some kind.
                    $mime = '';
                }
                finfo_close($finfo);
	    }
	    $this->sendheaders(StatusCodes::HTTP_OK, $mime);
            header('Content-Description: File Transfer');
	    if ($name !== '')
	    {
                header('Content-Disposition: attachment; filename="'.$name.'"');
	    }
            $this->debuffer();
	    $gz = $this->acceptgzip();
	    if ($gz)
	    {
		ob_start('ob_gzhandler');
	    }
	    else
	    {
		header('Content-Length: '.filesize($path));
	    }
	    if (!empty($range))
	    {
                    header('Content-Range: bytes '.$m[1].'-'.$m[2].'/'.$sz);
                    $fd = fopen($this->file, 'r'); # open the file, seek to the required place and read and return the required amount.
                    fseek($fd, $m[1]);
		    $this->debuffer(); # get rid of any buffering that we might be in
                    echo fread($fd, $m[2]-$m[1]+1);
                    fclose($fd); 
	    }
	    else
	    {
		readfile($path);
	    }
	    if ($gz)
	    {
		ob_end_flush();
	    }
	}
/**
 * Deliver a file as a response.
 *
 * @param integer	$code	The HTTP return code to use
 * @param string	$value	The data to send
 * @param string	$mime	The mime type of the file
 *
 * @return void
 */
	public function sendstring($code, $value, $mime = '')
	{
	    $this->sendheaders($code, $mime);
            $this->debuffer();
	    $gz = $this->acceptgzip();
	    if ($gz)
	    {
		ob_start('ob_gzhandler');
	    }
	    else
	    {
		header('Content-Length: '.strlen($value));
	    }
	    echo $value;
	    if ($gz)
	    {
		ob_end_flush();
	    }
	}
/**
 * Add a header to the header list.
 *
 * This supports having more than one header with the same name.
 *
 * @param string        $name
 * @param string        $value
 *
 * @return void
 */
        public function addheader($key, $value = '')
        {
	    if (is_array($key))
	    {
		foreach ($key as $k => $val)
		{
		    $this->headers[$k][] = $val;
		}
	    }
	    else
	    {
		$this->headers[$key][] = $value;
	    }
        }
/**
 * Output the headers
 *
 * @return void
 **/
        public function putheaders()
        {
            foreach ($this->headers as $name => $vals)
            {
                foreach ($vals as $v)
                {
                    header($name.': '.$v);
                }
            }
        }
/**
 * Check to see if the client accepts gzip encoding
 *
 * @return boolean
 */
        public function acceptgzip()
        {
            return filter_has_var(INPUT_SERVER, 'HTTP_ACCEPT_ENCOIDNG') &&
	        substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') > 0;
        }
/**
 * What kind of request was this?
 *
 * @return string
 */
        public function method()
        {
            return $_SERVER['REQUEST_METHOD'];
        }
/**
 * Is this a POST?
 *
 * @return boolean
 */
        public function ispost()
        {
            return $this->method() == 'POST';
        }
/*************************************
 * Functions to send things to the user
 */
/**
 * Debuffer - sometimes when we need to do output we are inside buffering. This seems
 * to be a problem with some LAMP stack systems.
 *
 * @return void
 */
	public function debuffer()
	{
            while (ob_get_length() > 0)
            { # just in case we are inside some buffering
                ob_end_clean();
            }
	}
/**
 * Deliver JSON response.
 *
 * @param object    $res
 *
 * @return void
 */
        public function sendJSON($res)
        {
	    $this->sendstring(StatusCode::HTTP_OK, json_encode($res, JSON_UNESCAPED_SLASHES), 'application/json');
        }
/**
 * Render a template as a response
 *
 * @param string	$template	The template file name
 * @param integer	$code		The response code
 * @param string	$mime		The mimetype of the response
 * @param array		$vals		Values to pass to the twig.
 *
 * @return void
 */
	public function sendtemplate($template, $code, $mime, $vals = [])
	{
	    if ($template != '')
	    {
		$this->sendheaders($code, $mime);
		$gz = $this->acceptgzip();
		if ($gz)
		{
		    ob_start('ob_gzhandler');
		}
		Local::getinstance()->render($template, $vals);
		if ($gz)
		{
		    ob_end_flush();
		}
	    }
	}
    }
?>

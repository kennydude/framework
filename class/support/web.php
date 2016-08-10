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
 * @var object  Holds reference to current context object
 */
        private $context;
/**
 * @var array   Holds values for headers that are required. Keyed by the name of the header
 */
        private $headers    = array();
/**
 * Constructor - if you pass in a context object then thw web functions can divert to error pages
 * if they have been implemented.
 *
 * @param object   $ctxt   The current context object
 */
        public function __construct($ctxt = NULL)
        {
            $this->context = $ctxt;
        }
/**
 * Generate a Location header
 *
 * @param string		$where	The URL to divert to
 */
	public function relocate($where)
	{
	    header('Location: '.$where);
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
            if ($divert && is_object(self::$context))
            { # divert to an error page, passing the message as a parameter
                $context->divert('/error/'.$code);
            }
	    header(StatusCodes::httpHeaderFor($code));
	    if ($msg != '')
	    {
		echo '<p>'.$msg.'</p>';
	    }
	    exit;
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
	heads(304, 'Not Modified');
	if ($etag != '')
	{
	    header('ETag: "'.$etag.'"');
	}
	if ($maxage != '')
	{
	    header('Expires: '.gmdate('D, d M Y H:i:s', time()+$mag) . ' GMT');
	    header('Cache-Control: max-age='.$mag);
	}
	exit;
    }
/**
 * Make a header sequence for a particualr return code and add some other useful headers
 *
 * @param integer	$code	The HTTP return code
 *
 * @return void
 */
	public function sendheaders($code, $debug = '')
	{
	    header(StatusCodes::httpHeaderFor($code));
	    header('Date: '.gmstrftime('%b %d %Y %H:%M:%S', time()));
	    header('Server: Framework');	# don't reveal server info
	    header('Window-target: _top');	# deframes things
	    header('X-Frame-Options: DENY');	# deframes things
	    header('Content-Language: en');
	    header('Vary: Accept-Encoding');
	    if ($debug != '')
	    {
		header('X-Debug-Info: '.$debug);
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
        public function addheader($key, $value)
        {
            $this->headers[$key][] = $value;
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
            return substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') > 0;
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
    }
?>

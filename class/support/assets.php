<?php
/**
 * Handle assets access
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2016 Newcastle University
 *
 */
/**
 * Handle all the cacheing stuff and maybe return a file
 */
    Class Assets extends Siteaction
    {
/**
 * @var string	The file name
 */
	private $file;
/**
 * @var integer	Last modified time for the file
 */
	private $mtime;
/**
 * @var array Mime type values
 */
	static private $mtypes = [
	    'css'	=> 'text/css',
	    'js'	=> 'text/javascript',
	    'png'	=> 'image/png',
	    'jpg'	=> 'image/jpeg',
	    'gif'	=> 'image/gif',
	    'ico'	=> 'image/x-icon',
	];
/**
 * Handle access to things in assets
 *
 * You can rely on Apache to deal with these things nromally, however if
 * you really want to get cacheability to complete you either have to go through hoops
 * in the APache config or you code it in here!
 *
 * @param object	$context	The context object for the site
 *
 * @return string	A template name
 */
	public function handle($context)
	{
	    chdir($context->local()->assetsdir());
	    
	    $rest = $context->rest();
	    $this->file = implode(DIRECTORY_SEPARATOR, $rest);
	    $this->mtime = filemtime($this->file);
	    $fname = array_pop($rest);
	    $ext = $extension = substr(strrchr($fname, "."), 1);
	    $mime = self::$mtypes[$ext];
	    $mag = $this->makemaxage();
	    $web = $context->web();
	    $web->addheader([
		'Last-Modified'	=> $this->makemod($this->mtime),
		'Etag'		=> '"'.$this->makeetag().'"',
		'Expires'	=> $this->makemod(time()+$mag),
		'Cache-Control'	=> 'max-age='.$mag,
	    ]);
	    $this->ifmodcheck();
	    $web->sendfile($this->file, $fname, $mime);
	    return '';
	}
/**
 * Make an etag - overrides the function in SiteAction
 *
 * @return string
 */
	public function makeetag()
	{
	    return sprintf("%u", crc32($this->file)).'-'.$this->mtime.'-'.(Web::getinstance()->acceptgzip() ? 1 : 0);
	}
/**
 * Make a maximum age - overrides function in SiteAction
 *
 * An hour for the most recent volume and a year for everything else
 *
 * @return integer
 */
	public function makemaxage()
	{
	    return 3600*24*365; # make it a year
	}
/**
 * Check a timestamp to see if we need to send the page again or not - overriding method in SiteAction
 *
 * @param string	$time	The time value to check
 *
 * @return boolean
 */
	public function checkmodtime($time)
	{
	    return $this->mtime > $time;
	}
    }
?>

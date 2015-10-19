<?php
/**
 * A class that contains code to handle robots.txt requests
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2012-2013 Newcastle University
 *
 */
    class Robot extends Siteaction
    {
        const MINIMAL = 'User-agent: *'.PHP_EOL.'Disallow:'.PHP_EOL;
/**
 * Handle /robots.txt requests
 *
 * @param object	$context	The context object for the site
 * @param object	$local		The local object for the site
 *
 * @return string	A template name
 */
	public function handle($context, $local)
	{
	    header('Content-Type: text/plain');
	    echo self::MINIMAL;
	    return '';
	}
    }
?>

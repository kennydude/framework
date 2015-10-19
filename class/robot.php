<?php
/**
 * A class that contains code to handle robots.txt requests
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2012-2013 Newcastle University
 *
 */
/**
 * Generate a robots.txt for the site
 */
    class Robot extends Siteaction
    {
/**
 * Handle /robots.txt requests
 *
 * @param object	$context	The context object for the site
 *
 * @return string	A template name
 */
	public function handle($context)
	{
	    header('Content-Type: text/plain');
	    return 'robot.twig';
	}
    }
?>

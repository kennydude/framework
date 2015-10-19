<?php
/**
 * A class that contains code to handle sitemap.xml requests
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2012-2013 Newcastle University
 *
 */
/**
 * Generate a sitemap.xml for the site
 */
    class Sitemap extends Siteaction
    {
/**
 * Handle /sitemap.xml requests
 *
 * @param object	$context	The context object for the site
 *
 * @return string	A template name
 */
	public function handle($context)
	{
	    header('Content-Type: application/xml');
            $context->local()->addval('url', Config::SITEURL);
	    return 'sitemap.twig';
	}
    }
?>

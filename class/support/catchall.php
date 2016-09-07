<?php
/**
 * A class that contains a last resort handler for pages that are not found through the normal
 * mechanisms. Users should derive their own class from this to handle non-object or template
 * request.
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2016 Newcastle University
 *
 */
/**
 * The default behaviour when a page does not exist.
 */
    class CatchAll extends Siteaction
    {
/**
 * Handle non-object or template page requests
 *
 * This just diverts to a /error page but it could also just render a 404 template here.
 * Which might be better. Needs thought.
 *
 * @param object	$context	The context object for the site
 *
 * @return string	A template name
 */
	public function handle($context)
	{
	    $tpl = '';
	    switch ($context->action())
	    {
	    case 'favicon.ico':
		$context->web()->sendfile($context->local()->assetsdir().'/favicons/favicon.ico', 'favicon.ico', 'image/x-icon');
		break;

	    case 'robots.txt':
		$context->web()->sendheaders(StatusCodes::HTTP_OK, 'text/plain; charset="utf-8"');
		return ['robot.twig', 'text/plain', StatusCodes::HTTP_OK];

	    case 'sitemap.xml':
		$context->web()->sendheaders(StatusCodes::HTTP_OK, 'application/xml; charset="utf-8"');
		$context->local()->addval('url', Config::SITEURL);
		return ['sitemap.twig', 'application/xml', StatusCodes::HTTP_OK];

	    default:
		$context->local()->addval('page', urlencode($_SERVER['REQUEST_URI']));
		return ['error/404.twig', StatusCodes::HTTP_NOT_FOUND, 'text/html; charset="utf-8"'];
	    }
	    return $tpl;
	}
    }
?>

<?php
/**
 * A class that contains a last resort handler for pages that are not found through the normal
 * mechanisms. Users should derive their own class from this to handle non-object or template
 * request.
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2015 Newcastle University
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
		$context->web()->sendfile($context->local()->assets().'/favicon/favicon.ico', 'favicon.ico', 'image/x-icon');
		break;

	    case 'robots.txt':
		Web::getinstance()->sendheaders(StatusCodes::HTTP_OK, 'text/plain');
		return ['robot.twig', 'text/plain', StatusCodes::HTTP_OK];
		break;

	    case 'sitemap.xml':
		Web::getinstance()->sendheaders(StatusCodes::HTTP_OK, 'application/xml');
		$context->local()->addval('url', Config::SITEURL);
		return ['sitemap.twig', 'application/xml', StatusCodes::HTTP_OK];
		break;

	    default:
	        $context->divert('/error/404?page='.urlencode($_SERVER['REQUEST_URI']));
		/* NOT REACHED */
	    }
	    return $tpl;
	}
    }
?>

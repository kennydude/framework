<?php
/**
 * Contains definition of Error class
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2012-2015 Newcastle University
 */
/**
 * A class that contains code to handle any /error related requests.
 */
    class Error extends Siteaction
    {
/**
 * Handle various error operations /error/xxxx
 *
 * @param object	$context	The context object for the site
 * @param object	$local		The local object for the site
 *
 * @return string	A template name
 */
	public function handle($context, $local)
	{
	    $tpl = 'error/error.twig';
	    $rest = $context->rest();
	    switch ($rest[0])
	    {
            case '404':
                $tpl = 'error/404.twig';
                $local->addval('page', $context->getpar('page', ''));
                break;

	    default :
                $local->addval('code', $rest[0]);
                $local->addval('message', StatusCodes::getMessage($rest[0]));
		break;
	    }
	    header(StatusCodes::httpHeaderFor($rest[0]));
	    return $tpl;
	}
    }
?>

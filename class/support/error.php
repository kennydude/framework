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
 *
 * @return string	A template name
 */
	public function handle($context)
	{
	    $rest = $context->rest();
	    switch ($rest[0])
	    {
            case '404':
                $tpl = 'error/404.twig';
                $context->local()->addval('page', $context->formdata()->get('page', ''));
                break;

	    default :
                $tpl = 'error/error.twig';
                $context->local()->addval(array(
                    'code'      => $rest[0],
                    'message'   => StatusCodes::getMessage($rest[0])
                ));
		break;
	    }
	    $context->web()->addheader('Cache-Control', 'no-cache');
	    return [$tpl, 'text/html; charset=utf-8', $rest[0]];
	}
    }
?>

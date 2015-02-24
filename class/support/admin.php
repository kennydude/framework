<?php
/**
 * Contaisn definition of Admin class
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2012-2015 Newcastle University
 */
/**
 * A class that contains code to handle any /admin related requests.
 *
 * Admin status is checked in index.php so does not need to be done here.
 */
    class Admin extends Siteaction
    {
/**
 * Handle various admin operations /admin/xxxx
 *
 * @param object	$context	The context object for the site
 * @param object	$local		The local object for the site
 *
 * @return string	A template name
 */
	public function handle($context, $local)
	{
	    $tpl = 'support/admin.twig';
	    $rest = $context->rest();
	    switch ($rest[0])
	    {
	    case 'pages':
		$tpl = 'support/pages.twig';
		break;

	    case 'contexts':
		$tpl = 'support/contexts.twig';
		break;

	    case 'roles':
		$tpl = 'support/roles.twig';
		break;

	    case 'users':
		$tpl = 'support/users.twig';
		break;

	    case 'info':
		$_SERVER['PHP_AUTH_PW'] = '*************'; # hide the password in case it is showing.
	        phpinfo();
		exit;

	    default :
		break;
	    }
	    return $tpl;
	}
    }
?>

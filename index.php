<?php
/**
 * Main entry point of the system
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2012-2015 Newcastle University
 */
/**
 * The framework assumes a self contained directory structure for a site like this :
 *
 * DOCUMENT_ROOT
 *    /sitename         This can be omitted if the site is the only one present and at the root
 *        /assets
 *            /css      CSS files
 *	      /favicons Favicon files
 *            /i18n     Any internationalisation files you may need
 *            /images   Image files
 *            /js       JavaScript
 *            /...      Any other stuff that can be accessed without intermediation through PHP
 *        /class        PHP class definition files named "classname.php"
 *        /class/support  PHP class files for the administrative functions provided by the framework
 *        /class/models	RedBean Model class files
 *        /errors       Files used for generating error pages.
 *        /lib          PHP files containing non-class definitions
 *        /twigcache    If twig cacheing is on this is where it stores the files
 *        /twigs        TWIG template files go in here
 *        /twigs/admin  Twig files for the admin support of the framework
 *        /vendor       If you are using composer then it puts stuff in here.
 *
 * The .htaccess file directs
 *         anything in /assets to be served by Apache.
 *         anything beginning "ajax" to be called directly i.e. ajax.php (this may or may not be useful - remove it if not)
 *         everything else gets passed into this script where it treats the URL thus:
 *                 /                        =>        /home and then
 *                 /action/r/e/st/          =>        Broken down in Context class. An action and an array of parameters.
 *
 *         Query strings and/or post fields are in the $_ arrays as normal but please use the access functions provided
 *         to get at the values whenever appropriate!
 */
    include 'class/support/framework.php';
    Framework::initialise();
    Config::setup(); # add default headers etc. - anything that the user choses to add to the code.

    $local = Local::getinstance()->setup(__DIR__, FALSE, TRUE, TRUE, TRUE); # Not Ajax, debug on, load twig, load RB
    $context = Context::getinstance()->setup();

    $mfl = $local->makepath($local->basedir(), 'maintenance'); # maintenance mode indicator file
    if (file_exists($mfl) && !$context->hasadmin())
    { # only let administrators in as we are doing maintenance. Could have a similar feature for other roles
	$context->web()->sendtemplate('support/maintenance.twig', StatusCodes::HTTP_OK, 'text/html',
	    ['msg' => file_get_contents($mfl)]);
	exit;
    }
    $action = $context->action();
    if ($action === '')
    { # default to home if there is nothing
        $action = 'home';
    }
/*
 * Look in the database for what to do based on the first part of the URL. DBOP is either = or regexp
 */
    $page = R::findOne('page', 'name'.Config::DBOP.'? and active=?', [$action, 1]);
    if (!is_object($page))
    { # No such page or it is marked as inactive
       $page = new stdClass;
       $page->kind = Siteaction::OBJECT;
       $page->source = 'NoPage';
    }
    else
    {
	if (($page->needlogin) && !$context->hasuser())
	{ # not logged in or not an admin
	    $context->divert('/login?page='.urlencode($local->debase($_SERVER['REQUEST_URI'])));
	}
    
	if ($page->admin && !$context->hasadmin())
	{ # not logged in or not an admin
	    $context->divert('/error/403');
	}
    
	if ($page->devel && !$context->hasdeveloper())
	{ # not logged in or not a developer
	    $context->divert('/error/403');
	}
    
	if ($page->mobileonly && !$context->hastoken())
	{
	    $context->divert('/error/403');
	}
    }

    $local->addval('context', $context);
    $local->addval('page', $action);
    $local->addval('siteinfo', new Siteinfo($local));

    $mime = 'text/html; charset=utf-8';
    $code = StatusCodes::HTTP_OK;
    switch ($page->kind)
    {
    case Siteaction::OBJECT: # fire up the object to handle the request
        $tpl = (new $page->source)->handle($context);
	if (is_array($tpl))
	{
	    list($tpl, $mime, $code) = $tpl;
	}
        break;

    case Siteaction::TEMPLATE: # render a template
        $tpl = $page->source;
        break;

    case Siteaction::REDIRECT: # redirect to somewhere else on the this site (temporary)
        $context->divert($page->source, TRUE);
        /* NOT REACHED */

    case Siteaction::REHOME: # redirect to somewhere else on the this site (permanent)
        $context->divert($page->source, FALSE);
        /* NOT REACHED */

    case Siteaction::XREDIRECT: # redirect to an external URL (temporary)
        $context->web()->relocate($page->source, TRUE);
        /* NOT REACHED */

    case Siteaction::XREHOME: # redirect to an external URL (permanent)
        $context->web()->relocate($page->source, FALSE);
        /* NOT REACHED */

    default :
        $context->web()->internal('Weird error');
        /* NOT REACHED */
    }

    $context->web()->sendtemplate($tpl, $code, $mime);

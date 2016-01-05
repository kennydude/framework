<?php
/**
 * A class that contains code to implement a contact page
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2012-2015 Newcastle University
 *
 */
/**
 * Support the /contact page
 */
    class Contact extends Siteaction
    {
/**
 * Handle contact operations /contact/xxxx
 *
 * @param object	$context	The context object for the site
 *
 * @return string	A template name
 */
        public function handle($context)
        {
            $fdt = $context->formdata();
            if (($msg = $fdt->post('message', '')) !== '')
            { # there is a post
                mail(Config::SYSADMIN, $fdt->post('subject', 'No Subject'), $msg);
                $context->local()->addval('done', TRUE);
            }
            return 'contact.twig';
        }
    }
?>

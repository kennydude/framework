<?php
/**
 * Ajax entry point of the system
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2012-2015 Newcastle University
 */
/**
 * The real work is all done in the Ajax class.
 */
    include 'class/support/framework.php';
    Framework::initialise();

    $local = new Local(__DIR__, TRUE, TRUE, TRUE); # Ajax, debug on, load twig
    (new Ajax)->handle(new Context($local), $local);
?>

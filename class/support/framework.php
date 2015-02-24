<?php
/**
 * Contains definition of Framework class
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2015 Newcastle University
 */
/**
 * Class for doing initial setup of the Framework.
 */
    class Framework
    {
/**
 * Carry out some global initialisations
 *
 * This is called in index.php and ajax.php. It does mean that it thefile to be included directly as
 * it is setting up the autoloader and stuff. But it does keep things DRY - only one place to
 * add any new autoload places etc.
 *
 * Note this only has a single static function. This is not great for unit testing, but
 * it seems to make more sense than having to create an instance that is never going to
 * be used again. (Equally this could just be code in a file rather than being a function
 * or a class, but that just seems nasty)
 *
 * @return void
 */
        public static function initialise()
        {
            error_reporting(E_ALL|E_STRICT);
/*
 * Setup the autoloader
 */
            $dir = dirname(dirname(__DIR__));
            set_include_path(
                implode(PATH_SEPARATOR, array(
                    implode(DIRECTORY_SEPARATOR, array($dir, '/class')),
                    implode(DIRECTORY_SEPARATOR, array($dir, '/class/support')),
                    implode(DIRECTORY_SEPARATOR, array($dir, '/class/models')),
                    implode(DIRECTORY_SEPARATOR, array($dir, '/lib')),
                    get_include_path()
                ))
            );
            spl_autoload_extensions('.php');
            spl_autoload_register();
        }
    }
?>
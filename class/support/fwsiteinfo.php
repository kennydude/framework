<?php
/**
 * A class that contains code to return info needed in various places on the site
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2016 Newcastle University
 *
 */
/**
 * Utility class that returns generally useful information about parts of the site
 */
    class FWSiteInfo
    {
/**
 * Get all the user beans
 *
 * @return array
 */
        public function users()
        {
            return R::findAll('user', 'order by login');
        }
/**
 * Get all the page beans
 *
 * @return array
 */
        public function pages()
        {
            return R::findAll('page', 'order by name');
        }
/**
 * Get all the Rolename beans
 *
 * @return array
 */
        public function roles()
        {
            return R::findAll('rolename', 'order by name');
        }
/**
 * Get all the Rolecontext beans
 *
 * @return array
 */
        public function contexts()
        {
            return R::findAll('rolecontext', 'order by name');
        }
/**
 * Get all the site config information
 *
 * @return array
 */
        public function siteconfig()
        {
            return R::findAll('fwconfig');
        }
/**
 * Get all the form beans
 *
 * @return array
 */
        public function forms()
        {
            return R::findAll('form', 'order by name');
        }
    }
?>

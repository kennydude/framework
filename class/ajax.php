<?php
/**
 * Class for handling AJAX calls invoked from ajax.php. You could integrate the
 * AJAX handling calls into the normal index.php RESTful route, but sometimes
 * keeping them separate is a good thing to do.
 *
 * It assumes that ajax calls are made to {{base}}/ajax.php via a POST and that
 * they have at least a parameter called 'op' that defines what is to be done.
 *
 * Of course, this is entirely arbitrary and you can do whatever you want!
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2014-2015 Newcastle University
 */
/**
 * Handle Ajax operations in this class
 */
    class Ajax
    {
        use Singleton;
/**
 * @var array Allowed operation codes. Values indicate : [needs login, needs admin privileges, needs developer privileges]
 */
        private static $ops = array(
            'addcontext'    => array(TRUE, TRUE, FALSE),
            'addpage'       => array(TRUE, TRUE, FALSE),
            'addrole'       => array(TRUE, TRUE, FALSE),
            'adduser'       => array(TRUE, TRUE, FALSE),
            'delbean'       => array(TRUE, TRUE, FALSE),
            'deluser'       => array(TRUE, TRUE, FALSE),
            'toggle'        => array(TRUE, TRUE, FALSE),
        );
/**
 * Add a User
 *
 * @param object	$context	The context object for the site
 *
 * @return void
 */
        private function adduser($context)
        {
            $now = $context->utcnow(); # make sure time is in UTC
            $u = R::dispense('user');
            $u->login = $context->mustpostpar('login');
            $u->email = $context->mustpostpar('email');
            $u->active = 1;
            $u->confirm = 1;
            $u->joined = $now;
            R::store($u);
            $u->setpw($context->mustpostpar('password'));
            if ($context->postpar('admin', 0) == 1)
            {
                $u->addrole('Site', 'Admin', '', $now);
            }
            if ($context->postpar('devel', 0) == 1)
            {
                $u->addrole('Site', 'Developer', '', $now);
            }
            echo $u->getID();
        }
/**
 * Add a Page
 *
 * @param object	$context	The context object for the site
 *
 * @return void
 */
        private function addpage($context)
        {
            $p = R::dispense('page');
            $p->name = $context->mustpostpar('name');
            $p->kind = $context->mustpostpar('kind');
            $p->source = $context->mustpostpar('source');
            $p->active = $context->mustpostpar('active');
            $p->admin = $context->mustpostpar('admin');
            $p->needlogin = $context->mustpostpar('login');
            $p->mobileonly = $context->mustpostpar('mobile');
            $p->devel = $context->mustpostpar('devel');
            R::store($p);
            echo $p->getID();
        }
/**
 * Add a Rolename
 *
 * @param object	$context	The context object for the site
 *
 * @return void
 */
        private function addrole($context)
        {
            $p = R::dispense('rolename');
            $p->name = $context->mustpostpar('name');
            $p->fixed = 0;
            R::store($p);
            echo $p->getID();
        }
/**
 * Add a Rolecontext
 *
 * @param object	$context	The context object for the site
 *
 * @return void
 */
        private function addcontext($context)
        {
            $p = R::dispense('rolecontext');
            $p->name = $context->mustpostpar('name');
            $p->fixed = 0;
            R::store($p);
            echo $p->getID();
        }
/**
 * Delete a bean
 *
 * The type of bean to be deleted is part of the message
 *
 * @param object	$context	The context object for the site
 *
 * @return void
 */
        private function delbean($context)
        {
            R::trash($context->load($context->mustpostpar('bean'), $context->mustpostpar('id')));
        }
/**
 * Delete a User
 *
 * @param object	$context	The context object for the site
 *
 * @return void
 */
        private function deluser($context)
        {
            R::trash($context->load('user', $context->mustpostpar('id')));
        }
/**
 * Toggle a flag field in a bean
 *
 * Note that for Roles the toggling is more complex and involves role removal/addition rather than
 * simply changing a value.
 *
 * @param object	$context	The context object for the site
 *
 * @return void
 */
        private function toggle($context)
        {
            $type = $context->mustpostpar('bean');
            $field = $context->mustpostpar('field');

            $bn = $context->load($type, $context->mustpostpar('id'));
            if ($type === 'user' && ctype_upper($field[0]))
            { # not simple toggling...
                if (is_object($bn->hasrole('Site', $field)))
                {
                    $bn->delrole('Site', $field);
                }
                else
                {
                    $bn->addrole('Site', $field, '', $context->utcnow());
                }
            }
            else
            {
                $bn->$field = $bn->$field == 1 ? 0 : 1;
                R::store($bn);
            }
        }
/**
 * Handle AJAX operations
 *
 * @param object	$context	The context object for the site
 *
 * @return void
 */
        public function handle($context)
        {
            if (($lg = $context->getpar('login', '')) != '')
            { # this is a parsley generated username check call
                if (R::count('user', 'login=?', array($lg)) > 0)
                {
                    return (new Web)->notfound(); // error if it exists....
                }
            }
            else
            {
                $op = $context->mustpostpar('op');
                if (isset(self::$ops[$op]))
                { # a valid operation
                    if (self::$ops[$op][0])
                    { # this operation requires a logged in user
                        $context->mustbeuser();
                    }
                    if (self::$ops[$op][1])
                    { # this operation needs admin privileges
                        $context->mustbeadmin();
                    }
                    if (self::$ops[$op][2])
                    { # this operation needs developer privileges
                        $context->mustbedeveloper();
                    }
                    $this->{$op}($context);
                }
                else
                { # return a 400
                    (new Web)->bad();
                }
            }
            exit;
        }
    }
?>

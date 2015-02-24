<?php
/**
 * Contains the definition of the Model class for User beans
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2013-2015 Newcastle University
 */
/**
 * A model class for the RedBean object User
 */
    class Model_User extends RedBean_SimpleModel
    {
/**
 * Check for a role
 *
 * @param string	$contextname
 * @param string	$rolename
 *
 * @return object
 */
        public function hasrole($contextname, $rolename)
        {
            $cname = R::findOne('rolecontext', 'name=?', array($contextname));
            $rname = R::findOne('rolename', 'name=?', array($rolename));
            return R::findOne('role', 'rolecontext_id=? and rolename_id=? and user_id=? and start <= NOW() and (end is NULL or end >= NOW())',
                array($cname->getID(), $rname->getID(), $this->bean->getID()));
        }
/**
 * Check for a role
 *
 * @param string	$contextname
 * @param string	$rolename
 *
 * @return void
 */
        public function delrole($contextname, $rolename)
        {
            $cname = R::findOne('rolecontext', 'name=?', array($contextname));
            $rname = R::findOne('rolename', 'name=?', array($rolename));
            $bn = R::findOne('role', 'rolecontext_id=? and rolename_id=? and user_id=? and start <= NOW() and (end is NULL or end >= NOW())',
                array($cname->getID(), $rname->getID(), $this->bean->getID()));
            if (is_object($bn))
            {
                R::trash($bn);
            }
        }
/**
 *  Add a role
 *
 * @param string	$contextname
 * @param string	$rolename
 * @param string	$otherinfo
 * @param string	$start		A datetime
 * @param string	$end		A datetime or ''
 *
 * @return object
 */
        public function addrole($contextname, $rolename, $otherinfo, $start, $end = '')
        {
            $cname = R::findOne('rolecontext', 'name=?', array($contextname));
            $rname = R::findOne('rolename', 'name=?', array($rolename));
            $r = R::dispense('role');
            $r->user = $this->bean;
            $r->rolecontext = $cname;
            $r->rolename = $rname;
            $r->otherinfo = $otherinfo;
            $r->start = $start;
            $r->end = $end == '' ? NULL : $end;
            R::store($r);
        }
/**
 * Get all currently valid roles for this user
 *
 * @return array
 */
        public function roles()
        {
            return $this->bean->withCondition('start <= NOW() and (end is null or end >= NOW())')->ownRole;
        }
/**
 * Is this user an admin?
 *
 * @return boolean
 */
        public function isadmin()
        {
            return is_object($this->hasrole('Site', 'Admin'));
        }
/**
 * Is this user active?
 *
 * @return boolean
 */
        public function isactive()
        {
            return $this->bean->active;
        }
/**
 * Is this user confirmed?
 *
 * @return boolean
 */
        public function isconfirmed()
        {
            return $this->bean->confirm;
        }
/**
 * Is this user a developer?
 *
 * @return boolean
 */
        public function isdeveloper()
        {
            return is_object($this->hasrole('Site', 'Developer'));
        }
/**
 * Set the user's password
 *
 * @param string	$pw	The password
 *
 * @return void
 */
        public function setpw($pw)
        {
            $this->bean->password = password_hash($pw, PASSWORD_DEFAULT);
            R::store($this->bean);
        }
/**
 * Check a password
 *
 * @param string	$pw The password
 *
 * @return boolean
 */
        public function pwok($pw)
        {
            return password_verify($pw, $this->bean->password);
        }
/**
 * Set the email confirmation flag
 *
 * @return void
 */
        public function doconfirm()
        {
            $this->bean->active = 1;
            $this->bean->confirm = 1;
            R::store($this->bean);
        }
/**
 * Generate a token for this user that can be used as a unique id from a phone.
 *
 * @param string        $device     Currently not used....
 *
 * @return string
 */
	public function maketoken($device = '')
	{
	    $token = new stdClass;
	    $token->iss = Config::SITEURL;
	    $token->iat = idate('U');
	    $token->sub = $this->bean->getID();
	    return JWT::encode($token, Context::KEY);
	}
    }
?>

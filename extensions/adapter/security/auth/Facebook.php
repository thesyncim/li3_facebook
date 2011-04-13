<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2011, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_facebook\extensions\adapter\security\auth;

use li3_facebook\extensions\FacebookProxy;
use lithium\security\Auth;
use lithium\storage\Session;
use lithium\core\Libraries;
use lithium\core\ConfigException;
use \Exception;

/**
 * Extends Lithium's Auth adapter to look for a Facebook session
 * and use that to set auth if available.
 *
 * @see lithium\security\Auth
*/

class Facebook extends \lithium\core\Object {

	/**
	 * Called by the `Auth` class to run an authentication check against the Facebook API
	 * and returns an array of user information on success, or `false` on failure.
	 *
	 * @param string $name The name of the `Auth` configuration/adapter to check against.
	 * @param array $options Options which include the options for session key names and also FB API method options.
	 * @return array Returns an array containing user information on success, or `false` on failure.
	 */
	public function check($name=null, array $options = array()) {
		$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? 'https://' : 'http://';
		$default_logout_url = $protocol . $_SERVER['HTTP_HOST'] . '/';
		$facebook_config = Libraries::get('li3_facebook');
		// get the options from the li3_facebook library configuration if set there
		$options += $facebook_config;
		
		// otherwise, set some defaults
		$defaults = array(
			'logout_url_options' => array(
				'next' => $default_logout_url
			),
			'login_url_options' => array(
			),
			'logout_url_session_key' => 'fb_logout_url',
			'login_url_session_key' => 'fb_login_url',
			'local_fb_session_name' => 'fb_session'
		);
		$options += $defaults;
		
		$user_data = false;
		if($facebook_config) {
			$session = FacebookProxy::getSession();
			$uid = null;
			// Session based API call.
			if ($session) {
				// Set the session locally
				Session::write($options['local_fb_session_name'], $session);
				try {
					$uid = FacebookProxy::getUser();
				} catch (Exception $e) {
					//error_log($e);
				}
			}
			
			// If $uid is set, then write the fb_logout_url session key
			if (!empty($uid)) {
				if($options['logout_url_session_key']) {
					Session::write($options['logout_url_session_key'], FacebookProxy::getLogoutUrl($options['logout_url_options']));
				}
				
				// Get the user data to return
				$user_data = array();
				try {
					$user_data = FacebookProxy::api('/me');
				} catch(Exception $e) {
					//error_log($e);
				}
				
			} else {
				// Else, the user hasn't logged in yet, write the fb_login_url session key
				if($options['login_url_session_key']) {
					Session::write($options['login_url_session_key'], FacebookProxy::getLoginUrl($options['login_url_options']));
				}
			}
			
		} else {
			throw new ConfigException("Facebook configuration has not been defined.");
		}
		
		return $user_data;
	}
	
	/**
	 * A pass-through method called by `Auth`. Returns the value of `$data`, which is written to
	 * a user's session. When implementing a custom adapter, this method may be used to modify or
	 * reject data before it is written to the session.
	 *
	 * @param array $data User data to be written to the session.
	 * @param array $options Adapter-specific options. Not implemented in the `Facebook` adapter.
	 * @return array Returns the value of `$data`.
	 */
	public function set($data, array $options = array()) {
		return $data;
	}
	
	/**
	 * Called by `Auth` when a user session is terminated. Not implemented in the `Facebook` adapter.
	 *
	 * @param array $options Adapter-specific options. Not implemented in the `Facebook` adapter.
	 * @return void
	 */
	public function clear(array $options = array()) {
	}

}
?>
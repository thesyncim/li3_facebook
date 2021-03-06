<?php
/**
 * li3_facebook helper, mainly, to embed and use the Facebook JavaScript SDK.
 * However, it also provides some other methods that relate to the PHP SDK.
 *
*/
namespace li3_facebook\extensions\helper;

use lithium\storage\Session;
use lithium\core\Libraries;

class Facebook extends \lithium\template\helper\Html {
    
    public function _init() {
        parent::_init();
        
        // Get some required values
        $facebook_config = Libraries::get('li3_facebook');
        if(!empty($facebook_config)) {
            extract($facebook_config);
        }
        
        $this->facebook_app_id = (isset($appId)) ? $appId:false;
        $this->facebook_locale = (isset($locale)) ? $locale:'en_US';
    }
    
    /**
	 * Displays a basic Facebook Connect login button.
	 * Works with the PHP SDK to get the login URL.
	 * This does not use the JavaScript SDK for xfbml.
	 *
	 * @param $options Array
	 * @return String
	*/
	public function facebook_login(array $options = array()) {
		$defaults = array(
			'div' => 'fb_login',
			'button_image' => '/li3_facebook/img/fb-login-button.png',
			'button_alt' => 'Login with Facebook',
			'additional_copy' => null,
            'fb_login_url_session_key' => 'fb_login_url'
		);
		$options += $defaults;
		$output = '';
		
		$fb_login_url = Session::read($options['fb_login_url_session_key']);
		if(!empty($fb_login_url)) {
			if($options['div'] !== false) {
				$output .= '<div id="' . $options['div'] . '">' . $options['additional_copy'];
			}
			
			$output .= '<a href="' . $fb_login_url . '"><img src="' . $options['button_image'] . '" alt="' . $options['button_alt'] .'" /></a>';
			
			if($options['div'] !== false) {
				$output .= '</div>';
			}
		}
		
		return $output;
	}
    
    /**
	 * Embeds the Facebook JavaScript SDK
	 * Facebook app id, locale, etc. is set in app/bootstrap/libraries.php
	 * with configuration options for Libraries::add('minerva').
	 * ex.
	 * Libraries::add('minerva', array(
	 *     'facebook' => array(
	 *         'appId' => 0000,
	 *         'secret' => 0000,
	 *         'locale' => 'en_US'
	 *     )
	 * ))
	 *
	 * TODO: add other options to be passed... like "status", "cookie" and "xfbml"
	 *
	 * @param $async Boolean Whether or not to embed it so it loads asynchronously
	 * @param $debug Boolean Whether or not to use the debug version
	 * @return String The HTML embed code
	*/
	public function facebook_init($async=true, $debug=false) {
		$script = 'all.js';
		if($debug === true) {
			$script = 'core.debug.js';
		}
		$output = '';
		if($this->facebook_app_id) {
			if($async) {
				$output = "<div id=\"fb-root\"></div><script>window.fbAsyncInit = function() { FB.init({appId: '".$this->facebook_app_id."', status: true, cookie: true, xfbml: true}); }; (function() { var e = document.createElement('script'); e.async = true; e.src = document.location.protocol + '//connect.facebook.net/".$this->facebook_locale."/".$script."'; document.getElementById('fb-root').appendChild(e); }());</script>";
			} else {
				$output = "<div id=\"fb-root\"></div><script src=\"http://connect.facebook.net/".$this->facebook_locale."/".$fb_script."\"></script><script>FB.init({ appId  : '".$this->facebook_app_id."', status : true, cookie : true, xfbml : true });</script>";
			}
		}
		return $output;
	}
    
}
?>
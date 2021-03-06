<?php
/**
 * Sample class for PHPUnit.
 *
 * @package     PirateForms
 * @subpackage  Tests
 * @copyright   Copyright (c) 2017, Marius Cristea
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0.0
 */

/**
 * Sample test class.
 */
class Test_Pirate_Forms extends WP_UnitTestCase {

	/**
	 * Testing WP mail
	 *
	 * @access public
	 */
	public function test_wp_mail() {
        do_action('admin_head');

        $settings   = PirateForms_Util::get_option();
        $this->assertEquals('yes', $settings['pirateformsopt_nonce']);
        
        $settings['pirateformsopt_nonce']   = 'no';
        $settings['pirateformsopt_recaptcha_field']   = 'no';
        $settings['pirateformsopt_store']   = 'yes';
        
        PirateForms_Util::set_option( $settings );

        $settings   = PirateForms_Util::get_option();

        $this->assertEquals('no', $settings['pirateformsopt_nonce']);
        $this->assertEquals('yes', $settings['pirateformsopt_store']);
        
        $_POST  = array(
            'honeypot'                  => '',
            'pirate-forms-contact-name' => 'x',
            'pirate-forms-contact-email' => 'x@x.com',
            'pirate-forms-contact-subject' => 'x',
            'pirate-forms-contact-message' => 'x',
        );
        add_action( 'phpmailer_init', array( $this, 'phpmailer_wp_mail' ), 999 );
        do_action('pirate_unittesting_template_redirect');

        $posts  = get_posts(array(
            'post_type'     => 'pf_contact',
            'post_author'  => 1,
            'post_status'  => 'private',
            'numberposts'   => 1,
            'fields'        => 'ids',
        ));

        $this->assertEquals(1, count($posts));

	}

	/**
	 * Testing SMTP
	 *
	 * @access public
	 * @dataProvider smptProvider
	 */
	public function test_smtp( $host, $port, $user, $pass, $auth) {
        do_action('admin_head');

        $settings   = PirateForms_Util::get_option();
        $this->assertEquals('yes', $settings['pirateformsopt_nonce']);
        $settings['pirateformsopt_nonce']   = 'no';
        $settings['pirateformsopt_recaptcha_field']   = 'no';
        $settings['pirateformsopt_store']   = 'yes';
        $settings['pirateformsopt_use_smtp']   = 'yes';
        $settings['pirateformsopt_smtp_host']   = $host;
        $settings['pirateformsopt_smtp_port']   = $port;
        $settings['pirateformsopt_use_smtp_authentication']   = $auth ? 'yes' : 'no';
        $settings['pirateformsopt_smtp_username']   = $user;
        $settings['pirateformsopt_smtp_password']   = $pass;
        
        $this->smpt_data    = array( 'host' => $host, 'port' => $port, 'user' => $user, 'pass' => $pass, 'auth' => $auth );

        PirateForms_Util::set_option( $settings );

        $settings   = PirateForms_Util::get_option();

        $this->assertEquals('no', $settings['pirateformsopt_nonce']);
        $this->assertEquals('yes', $settings['pirateformsopt_store']);
        $this->assertEquals($host, $settings['pirateformsopt_smtp_host']);
        
        $_POST  = array(
            'honeypot'                  => '',
            'pirate-forms-contact-name' => 'x',
            'pirate-forms-contact-email' => 'x@x.com',
            'pirate-forms-contact-subject' => 'x',
            'pirate-forms-contact-message' => 'x',
        );
        add_action( 'phpmailer_init', array( $this, 'phpmailer_smtp_mail' ), 999 );
        do_action('pirate_unittesting_template_redirect');

        $posts  = get_posts(array(
            'post_type'     => 'pf_contact',
            'post_author'  => 1,
            'post_status'  => 'private',
            'numberposts'   => 1,
            'fields'        => 'ids',
        ));

        $this->assertEquals(1, count($posts));

	}

	/**
	 * Checking phpmailer for WP mail
	 *
	 * @access public
	 */
    public function phpmailer_wp_mail( $phpmailer ) {
        $this->assertEquals('<h2>Contact form submission from Test Blog (http://example.org)</h2><table><tr><th>Your Name:</th><td>x</td></tr><tr><th>Your Email:</th><td>x@x.com</td></tr><tr><th>Subject:</th><td>x</td></tr><tr><th>Your message:</th><td>x</td></tr><tr><th>IP address:</th><td>127.0.0.1</td></tr><tr><th>IP search:</th><td>http://whatismyipaddress.com/ip/127.0.0.1</td></tr><tr><th>Sent from page:</th><td></td></tr></table>', $phpmailer->Body);
    }

	/**
	 * Checking phpmailer for SMTP mail
	 *
	 * @access public
	 */
    public function phpmailer_smtp_mail( $phpmailer ) {
        $this->assertEquals('<h2>Contact form submission from Test Blog (http://example.org)</h2><table><tr><th>Your Name:</th><td>x</td></tr><tr><th>Your Email:</th><td>x@x.com</td></tr><tr><th>Subject:</th><td>x</td></tr><tr><th>Your message:</th><td>x</td></tr><tr><th>IP address:</th><td>127.0.0.1</td></tr><tr><th>IP search:</th><td>http://whatismyipaddress.com/ip/127.0.0.1</td></tr><tr><th>Sent from page:</th><td></td></tr></table>', $phpmailer->Body);
        $this->assertEquals($this->smpt_data['host'], $phpmailer->Host);
        $this->assertEquals($this->smpt_data['port'], $phpmailer->Port);
        $this->assertEquals($this->smpt_data['user'], $phpmailer->Username);
        $this->assertEquals($this->smpt_data['pass'], $phpmailer->Password);
    }

	/**
	 * Provide the SMTP data
	 *
	 * @access public
	 */
	public function smptProvider() {
		return array(
			array('smtp.gmail.com', '465', 'x', 'x', true),
		);
	}

}

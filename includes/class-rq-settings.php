<?php
/**
 * Plugin settings: defaults, access and the traffic-source catalogue.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class RQ_Settings {

	const OPTION = 'rev_quiz_settings';

	private static $cache = null;

	/**
	 * Ad settings live in RQ_Ads (REW QUIZZ > Ad blocks).
	 */
	public static function defaults() {
		return array(
			// Traffic filter (cloaking).
			'traffic_sources'        => array(),
			'custom_sources'         => '',
			'fallback_mode'          => 'content', // content | redirect.
			'fallback_url'           => '',
			'remember_hours'         => 24,
			'admin_bypass'           => 1,
			'ads_for_blocked'        => 0,

			// Permalinks.
			'base_slug'              => 'quiz',

			// Content.
			'default_instructions'   => '<p>Answer each question by tapping the option that fits you best. Every step saves your choice and takes you to the next question automatically. There are no right or wrong answers, and you can go back at any time.</p>',
			'rewarded_box'           => 1,
			'rewarded_text'          => 'Redeem rewards to unlock the full guide',
			'rewarded_button'        => "Let's Play",

			// Push notifications and translation.
			'onesignal_app_id'       => '',
			'translate_enabled'      => 1,
			'translate_page_lang'    => 'en',
			'translate_languages'    => 'hi,es,fr,pt,ru,tr,mr,te,vi',

		);
	}

	public static function all() {
		if ( null === self::$cache ) {
			$saved       = get_option( self::OPTION, array() );
			self::$cache = wp_parse_args( is_array( $saved ) ? $saved : array(), self::defaults() );
		}
		return self::$cache;
	}

	public static function get( $key ) {
		$all = self::all();
		return isset( $all[ $key ] ) ? $all[ $key ] : null;
	}

	public static function flush_cache() {
		self::$cache = null;
	}

	public static function base_slug() {
		$slug = sanitize_title( self::get( 'base_slug' ) );
		return $slug ? $slug : 'quiz';
	}

	/**
	 * Social networks a visitor can be referred from.
	 *
	 * domains: referrer hosts (a trailing ".*" matches any TLD).
	 * utm:     utm_source / ref / source query values.
	 * ua:      in-app browser user agent tokens.
	 */
	public static function sources() {
		return array(
			'facebook'  => array(
				'label'   => 'Facebook',
				'domains' => array( 'facebook.com', 'fb.com', 'fb.me', 'fb.watch' ),
				'utm'     => array( 'facebook', 'fb', 'facebook.com', 'meta' ),
				'ua'      => array( 'FBAN', 'FBAV', 'FB_IAB', 'FBIOS' ),
			),
			'instagram' => array(
				'label'   => 'Instagram',
				'domains' => array( 'instagram.com' ),
				'utm'     => array( 'instagram', 'ig', 'instagram.com' ),
				'ua'      => array( 'Instagram' ),
			),
			'messenger' => array(
				'label'   => 'Messenger',
				'domains' => array( 'messenger.com', 'm.me' ),
				'utm'     => array( 'messenger' ),
				'ua'      => array( 'MessengerForiOS', 'FB_IAB/MESSENGER', 'Orca-Android' ),
			),
			'threads'   => array(
				'label'   => 'Threads',
				'domains' => array( 'threads.net', 'threads.com' ),
				'utm'     => array( 'threads' ),
				'ua'      => array( 'Barcelona' ),
			),
			'twitter'   => array(
				'label'   => 'X (Twitter)',
				'domains' => array( 'twitter.com', 't.co', 'x.com' ),
				'utm'     => array( 'twitter', 'x', 'twitter.com', 'x.com' ),
				'ua'      => array( 'Twitter' ),
			),
			'tiktok'    => array(
				'label'   => 'TikTok',
				'domains' => array( 'tiktok.com' ),
				'utm'     => array( 'tiktok', 'tt', 'tiktok.com' ),
				'ua'      => array( 'musical_ly', 'BytedanceWebview', 'TikTok', 'trill_' ),
			),
			'youtube'   => array(
				'label'   => 'YouTube',
				'domains' => array( 'youtube.com', 'youtu.be' ),
				'utm'     => array( 'youtube', 'yt', 'youtube.com' ),
				'ua'      => array(),
			),
			'pinterest' => array(
				'label'   => 'Pinterest',
				'domains' => array( 'pinterest.*', 'pin.it' ),
				'utm'     => array( 'pinterest', 'pin', 'pinterest.com' ),
				'ua'      => array( 'Pinterest' ),
			),
			'snapchat'  => array(
				'label'   => 'Snapchat',
				'domains' => array( 'snapchat.com' ),
				'utm'     => array( 'snapchat', 'snap' ),
				'ua'      => array( 'Snapchat' ),
			),
			'linkedin'  => array(
				'label'   => 'LinkedIn',
				'domains' => array( 'linkedin.com', 'lnkd.in' ),
				'utm'     => array( 'linkedin', 'linkedin.com' ),
				'ua'      => array( 'LinkedInApp' ),
			),
			'reddit'    => array(
				'label'   => 'Reddit',
				'domains' => array( 'reddit.com', 'redd.it' ),
				'utm'     => array( 'reddit', 'reddit.com' ),
				'ua'      => array( 'Reddit' ),
			),
			'whatsapp'  => array(
				'label'   => 'WhatsApp',
				'domains' => array( 'whatsapp.com', 'wa.me' ),
				'utm'     => array( 'whatsapp', 'wa' ),
				'ua'      => array( 'WhatsApp' ),
			),
			'telegram'  => array(
				'label'   => 'Telegram',
				'domains' => array( 't.me', 'telegram.me', 'telegram.org' ),
				'utm'     => array( 'telegram', 'tg' ),
				'ua'      => array( 'Telegram' ),
			),
			'quora'     => array(
				'label'   => 'Quora',
				'domains' => array( 'quora.com' ),
				'utm'     => array( 'quora' ),
				'ua'      => array(),
			),
			'tumblr'    => array(
				'label'   => 'Tumblr',
				'domains' => array( 'tumblr.com' ),
				'utm'     => array( 'tumblr' ),
				'ua'      => array(),
			),
			'vk'        => array(
				'label'   => 'VK',
				'domains' => array( 'vk.com', 'vk.ru' ),
				'utm'     => array( 'vk' ),
				'ua'      => array(),
			),
			'discord'   => array(
				'label'   => 'Discord',
				'domains' => array( 'discord.com', 'discord.gg', 'discordapp.com' ),
				'utm'     => array( 'discord' ),
				'ua'      => array( 'Discord' ),
			),
		);
	}
}

<?php

namespace WatchTheDot\Plugins\DivibbPress;

class ModuleRegistry {

	private static array $modules;

	public function load() {
		if ( isset( self::$modules ) ) {
			return;
		}

		self::$modules = array();

		require_once __DIR__ . '/../modules/BBPressModule.php';
		require_once __DIR__ . '/../modules/BBPressShortcode.php';

		$shortcodes = bbpress()->shortcodes->codes;

		$include_if_isset = function ( $shortcode, $folder, $name ) use ( $shortcodes ) {
			if ( ! isset( $shortcodes[ $shortcode ] ) ) {
				return;
			}

			$this->include( $name, $folder );
		};

		/** Forums ********************************************************/
		$include_if_isset( 'bbp-forum-index', 'Forums', 'ForumIndex' );
		$include_if_isset( 'bbp-forum-form', 'Forums', 'ForumForm' );
		$include_if_isset( 'bbp-single-forum', 'Forums', 'SingleForum' );

		/** Topics ********************************************************/
		$include_if_isset( 'bbp-topic-index', 'Topics', 'TopicIndex' );
		$include_if_isset( 'bbp-topic-form', 'Topics', 'TopicForm' );
		$include_if_isset( 'bbp-single-topic', 'Topics', 'SingleTopic' );

		/** Topic Tags ****************************************************/
		$include_if_isset( 'bbp-topic-tags', 'TopicTags', 'TopicTags' );
		$include_if_isset( 'bbp-single-tag', 'TopicTags', 'SingleTag' );

		/** Replies *******************************************************/
		$include_if_isset( 'bbp-reply-form', 'Replies', 'ReplyForm' );
		$include_if_isset( 'bbp-single-reply', 'Replies', 'SingleReply' );

		/** Views *********************************************************/
		$include_if_isset( 'bbp-single-view', 'Views', 'SingleView' );

		/** Account *******************************************************/
		$include_if_isset( 'bbp-login', 'Account', 'Login' );
		$include_if_isset( 'bbp-register', 'Account', 'Register' );
		$include_if_isset( 'bbp-lost-pass', 'Account', 'LostPass' );

		/** Others *******************************************************/
		$include_if_isset( 'bbp-stats', 'Others', 'Stats' );
	}

	private function include( string $name, string $folder = '' ) {
		if ( $folder ) {
			$file_path = __DIR__ . "/../modules/$folder/$name/$name.php";
		} else {
			$file_path = __DIR__ . "/../modules/$name/$name.php";
		}

		if ( ! file_exists( $file_path ) ) {
			_doing_it_wrong(
				__CLASS__ . '@' . __METHOD__,
				esc_html( "Path `$file_path` doesn't exist" ),
				//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				Plugin::VERSION
			);
			return null;
		}

		// phpcs:disable Squiz.PHP.DisallowMultipleAssignments.Found
		return self::$modules[ $name ] = require_once $file_path;
	}

	public function get_modules() {
		return self::$modules;
	}

	public function get_module( string $key ) {
		return self::get_modules()[ $key ] ?? null;
	}
}

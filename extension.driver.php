<?php

	class Extension_ProfileDevKit extends Extension {
	/*-------------------------------------------------------------------------
		Definition:
	-------------------------------------------------------------------------*/

		public static $active = false;
		public static $log = array();

		public function about() {
			return array(
				'name'			=> 'Profile Devkit',
				'version'		=> '1.0.5pre',
				'release-date'	=> 'unreleased',
				'author'		=> array(
					'name'			=> 'Rowan Lewis',
					'website'		=> 'http://rowanlewis.com/',
					'email'			=> 'me@rowanlewis.com'
				)
			);
		}

		public function getSubscribedDelegates() {
			return array(
				array(
					'page'		=> '/frontend/',
					'delegate'	=> 'FrontendDevKitResolve',
					'callback'	=> 'frontendDevKitResolve'
				),
				array(
					'page'		=> '/frontend/',
					'delegate'	=> 'ManipulateDevKitNavigation',
					'callback'	=> 'manipulateDevKitNavigation'
				),
				array(
					'page' => '/frontend/',
					'delegate' => 'LogQuery',
					'callback' => 'logQuery'
				),
			);
		}

		public function frontendDevKitResolve(array $context) {
			if (isset($_GET['profile'])) {
				require_once(EXTENSIONS . '/profiledevkit/content/content.profile.php');

				$context['devkit'] = new Content_ProfileDevkit();
				self::$active = true;
			}
		}

		public function manipulateDevKitNavigation(array $context) {
			$xml = $context['xml'];
			$item = $xml->createElement('item');
			$item->setAttribute('name', __('Profile'));
			$item->setAttribute('handle', 'profile');
			$item->setAttribute('active', (self::$active ? 'yes' : 'no'));

			$xml->documentElement->appendChild($item);
		}

		public function logQuery(array $context) {
			if(isset($_GET['profile']) && $_GET['profile'] == 'database-queries') {
				self::$log[$context['query_hash']] = array(
					'query' => $context['query'],
					'time' => $context['execution_time']
				);
			}
		}

	}

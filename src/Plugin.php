<?php

namespace Detain\MyAdminVpsSoftaculous;

use Symfony\Component\EventDispatcher\GenericEvent;

class Plugin {

	public function __construct() {
	}

	public static function Load(GenericEvent $event) {
		$service = $event->getSubject();
		function_requirements('class.Addon');
		$addon = new \Addon();
		$addon->set_module('vps')->set_text('Softaculous')->set_cost(VPS_SOFTACULOUS_COST)
			->set_require_ip(true)->set_enable(function() {
				require_once 'include/licenses/license.functions.inc.php';
			})->set_disable(function() {
			})->register();
		$service->add_addon($addon);
	}

	public static function Settings(GenericEvent $event) {
		$module = 'vps';
		$settings = $event->getSubject();
		$settings->add_text_setting($module, 'Addon Costs', 'vps_softaculous_cost', 'VPS Softaculous License:', 'This is the cost for purchasing a softaculous license on top of a VPS.', $settings->get_setting('VPS_SOFTACULOUS_COST'));
	}

}

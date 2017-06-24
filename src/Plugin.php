<?php

namespace Detain\MyAdminVpsSoftaculous;

use Symfony\Component\EventDispatcher\GenericEvent;

class Plugin {

	public static $name = 'Softaculous Licensing VPS Addon';
	public static $description = 'Allows selling of Softaculous Server and VPS License Types.  More info at https://www.netenberg.com/softaculous.php';
	public static $help = 'It provides more than one million end users the ability to quickly install dozens of the leading open source content management systems into their web space.  	Must have a pre-existing cPanel license with cPanelDirect to purchase a softaculous license. Allow 10 minutes for activation.';
	public static $module = 'vps';
	public static $type = 'addon';


	public function __construct() {
	}

	public static function getHooks() {
		return [
			self::$module.'.load_addons' => [__CLASS__, 'getAddon'],
			self::$module.'.settings' => [__CLASS__, 'getSettings'],
		];
	}

	public static function getAddon(GenericEvent $event) {
		$service = $event->getSubject();
		function_requirements('class.Addon');
		$addon = new \Addon();
		$addon->setModule(self::$module)
			->set_text('Softaculous')
			->set_cost(VPS_SOFTACULOUS_COST)
			->set_require_ip(true)
			->set_enable([__CLASS__, 'doEnable'])
			->set_disable([__CLASS__, 'doDisable'])
			->register();
		$service->addAddon($addon);
	}

	public static function doEnable(\Service_Order $serviceOrder, $repeatInvoiceId, $regexMatch = false) {
		$serviceInfo = $serviceOrder->getServiceInfo();
		$settings = get_module_settings(self::$module);
		require_once __DIR__.'/../../../../include/licenses/license.functions.inc.php';
		myadmin_log(self::$module, 'info', 'activating softnoc', __LINE__, __FILE__);
		$noc = new \Detain\MyAdminSoftaculous\SOFT_NOC(SOFTACULOUS_USERNAME, SOFTACULOUS_PASSWORD);
		myadmin_log(self::$module, 'info', json_encode($noc->buy($serviceInfo[$settings['PREFIX'].'_ip'], '1M', 2, $GLOBALS['tf']->accounts->cross_reference($serviceInfo[$settings['PREFIX'].'_custid']), 1)), __LINE__, __FILE__);
		$GLOBALS['tf']->history->add($settings['TABLE'], 'add_softaculous', $serviceInfo[$settings['PREFIX'].'_id'], $serviceInfo[$settings['PREFIX'].'_ip'], $serviceInfo[$settings['PREFIX'].'_custid']);
	}

	public static function doDisable(\Service_Order $serviceOrder) {
		$serviceInfo = $serviceOrder->getServiceInfo();
		$settings = get_module_settings(self::$module);
		require_once __DIR__.'/../../../../include/licenses/license.functions.inc.php';
		myadmin_log(self::$module, 'info', 'deactivating softnoc', __LINE__, __FILE__);
		$noc = new \Detain\MyAdminSoftaculous\SOFT_NOC(SOFTACULOUS_USERNAME, SOFTACULOUS_PASSWORD);
		myadmin_log(self::$module, 'info', json_encode($noc->cancel('', $serviceInfo[$settings['PREFIX'].'_ip'])), __LINE__, __FILE__);
		$GLOBALS['tf']->history->add($settings['TABLE'], 'del_softaculous', $serviceInfo[$settings['PREFIX'].'_id'], $serviceInfo[$settings['PREFIX'].'_ip'], $serviceInfo[$settings['PREFIX'].'_custid']);
	}

	public static function getSettings(GenericEvent $event) {
		$settings = $event->getSubject();
		$settings->add_text_setting(self::$module, 'Addon Costs', 'vps_softaculous_cost', 'VPS Softaculous License:', 'This is the cost for purchasing a softaculous license on top of a VPS.', $settings->get_setting('VPS_SOFTACULOUS_COST'));
	}

}

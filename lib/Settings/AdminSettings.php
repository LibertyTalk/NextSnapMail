<?php
namespace OCA\SnapMailLT\Settings;

use OCP\AppFramework\Http\TemplateResponse;
use OCP\IConfig;
use OCP\Settings\ISettings;

class AdminSettings implements ISettings
{
	private $config;

	public function __construct(IConfig $config)
	{
		$this->config = $config;
	}

	public function getForm()
	{
		\OCA\SnapMailLT\Util\SnappyMailHelper::loadApp();

		$keys = [
			'snapmail_lt-autologin',
			'snapmail_lt-autologin-with-email',
			'snapmail_lt-autologin-oidc'
		];
		$parameters = [];
		foreach ($keys as $k) {
			$v = $this->config->getAppValue('snapmail_lt', $k);
			$parameters[$k] = $v;
		}
		$parameters['snapmail_lt-integrated-admin-url'] =
			\OC::$server->get(\OCP\IURLGenerator::class)->linkToRoute('snapmail_lt.page.admin');

		$oConfig = \RainLoop\Api::Config();
		$brandingChanged = false;
		if ('SnappyMail Webmail' === $oConfig->Get('webmail', 'title', '')) {
			$oConfig->Set('webmail', 'title', 'SnapMail LT Webmail');
			$brandingChanged = true;
		}
		if ('SnappyMail' === $oConfig->Get('webmail', 'loading_description', '')) {
			$oConfig->Set('webmail', 'loading_description', 'SnapMail LT');
			$brandingChanged = true;
		}
		if ($brandingChanged) {
			$oConfig->Save();
		}
		$passfile = APP_PRIVATE_DATA . 'admin_password.txt';
		$sPassword = '';
		if (\is_file($passfile)) {
			$sPassword = \file_get_contents($passfile);
		}

		$parameters['can-import-rainloop'] = $sPassword && \is_dir(
			\rtrim(\trim($this->config->getSystemValue('datadirectory', '')), '\\/')
			. '/rainloop-storage'
		);

		$parameters['snapmail_lt-debug'] = $oConfig->Get('debug', 'enable', false);

		// Prevent "Failed loading /nextcloud/snappymail/v/2.N.N/static/js/min/libs.min.js"
		$app_path = $oConfig->Get('webmail', 'app_path');
		if (!$app_path) {
			$app_path = \OC::$server->get(\OCP\App\IAppManager::class)->getAppWebPath('snapmail_lt') . '/app/';
			$oConfig->Set('webmail', 'app_path', $app_path);
			$oConfig->Set('webmail', 'theme', 'NextcloudV25+');
			$oConfig->Save();
		}
		$parameters['snapmail_lt-app_path'] = $oConfig->Get('webmail', 'app_path', false);
		$parameters['snapmail_lt-nc-lang'] = !$oConfig->Get('webmail', 'allow_languages_on_settings', true);
		$parameters['gmail-oauth-client-id'] = $this->config->getAppValue('snapmail_lt', 'gmail-oauth-client-id', '');
		$parameters['gmail-oauth-client-secret-set'] = '' !== $this->config->getAppValue('snapmail_lt', 'gmail-oauth-client-secret', '');
		$parameters['gmail-oauth-domains'] = $this->config->getAppValue('snapmail_lt', 'gmail-oauth-domains', "gmail.com\ngooglemail.com");
		$parameters['gmail-oauth-auto-configure'] = $this->config->getAppValue('snapmail_lt', 'gmail-oauth-auto-configure', '1');
		$parameters['gmail-oauth-callback-url'] = \OC::$server->get(\OCP\IURLGenerator::class)
			->linkToRouteAbsolute('snapmail_lt.page.index') . '?SnapMailLTGmailOauth';

		$oNextcloudPlugin = \RainLoop\Api::Actions()->Plugins()->CreatePluginByName('nextcloud');
		$oNextcloudPluginConfig = $oNextcloudPlugin ? $oNextcloudPlugin->Config() : null;
		$parameters['nextcloud-plugin-suggestions'] = $oNextcloudPluginConfig
			? (bool) $oNextcloudPluginConfig->Get('plugin', 'suggestions', true) : true;
		$parameters['nextcloud-plugin-ignore-system-addressbook'] = $oNextcloudPluginConfig
			? (bool) $oNextcloudPluginConfig->Get('plugin', 'ignoreSystemAddressbook', true) : true;
		$parameters['nextcloud-plugin-calendar'] = $oNextcloudPluginConfig
			? (bool) $oNextcloudPluginConfig->Get('plugin', 'calendar', false) : false;

		\OCP\Util::addScript('snapmail_lt', 'snapmail_lt');
		\OCP\Util::addScript('snapmail_lt', 'admin-dashboard');
		\OCP\Util::addStyle('snapmail_lt', 'style');
		\OCP\Util::addStyle('snapmail_lt', 'admin-dashboard');
		return new TemplateResponse('snapmail_lt', 'admin-local', $parameters);
	}

	public function getSection()
	{
		return 'snapmail_lt';
	}

	public function getPriority()
	{
		return 50;
	}
}

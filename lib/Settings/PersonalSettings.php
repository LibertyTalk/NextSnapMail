<?php
namespace OCA\SnapMailLT\Settings;

use OCP\AppFramework\Http\TemplateResponse;
use OCP\IConfig;
use OCP\Settings\ISettings;

class PersonalSettings implements ISettings
{
	private $config;

	public function __construct(IConfig $config)
	{
		$this->config = $config;
	}

	public function getForm()
	{
		$uid = \OC::$server->get(\OCP\IUserSession::class)->getUser()->getUID();
		$sEmail = $this->config->getUserValue($uid, 'snapmail_lt', 'snapmail_lt-email');
		if ($sPass = $this->config->getUserValue($uid, 'snapmail_lt', 'snapmail_lt-password')) {
			$this->config->deleteUserValue($uid, 'snapmail_lt', 'snapmail_lt-password');
			$this->config->setUserValue($uid, 'snapmail_lt', 'passphrase', $sPass);
		}
		$session = \OC::$server->get(\OCP\ISession::class);
		$parameters = [
			'snapmail_lt-email' => $sEmail,
			'snapmail_lt-password' => $this->config->getUserValue($uid, 'snapmail_lt', 'passphrase') ? '******' : '',
			'gmail-oauth-enabled' => $this->isGmailOauthEnabled(),
			'gmail-oauth-url' => \OC::$server->get(\OCP\IURLGenerator::class)
				->linkToRoute('snapmail_lt.page.index') . '?SnapMailLTGmailOauthStart',
			'gmail-oauth-message' => (string) $session->get('snapmail_lt-gmail-oauth-message'),
			'gmail-oauth-error' => (string) $session->get('snapmail_lt-gmail-oauth-error')
		];
		$session->remove('snapmail_lt-gmail-oauth-message');
		$session->remove('snapmail_lt-gmail-oauth-error');
		\OCP\Util::addScript('snapmail_lt', 'snapmail_lt');
		return new TemplateResponse('snapmail_lt', 'personal_settings', $parameters, '');
	}

	private function isGmailOauthEnabled(): bool
	{
		return '' !== \trim($this->config->getAppValue('snapmail_lt', 'gmail-oauth-client-id', ''))
			&& '' !== \trim($this->config->getAppValue('snapmail_lt', 'gmail-oauth-client-secret', ''));
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

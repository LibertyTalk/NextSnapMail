<?php

namespace OCA\SnapMailLT\AppInfo;

use OCA\SnapMailLT\Util\SnappyMailHelper;
use OCA\SnapMailLT\Controller\FetchController;
use OCA\SnapMailLT\Controller\PageController;
use OCA\SnapMailLT\Dashboard\UnreadMailWidget;
use OCA\SnapMailLT\Search\Provider;
use OCA\SnapMailLT\Listeners\AccessTokenUpdatedListener;

use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\App\IAppManager;
use OCP\EventDispatcher\IEventDispatcher;
use OCP\IConfig;
use OCP\IL10N;
use OCP\ISession;
use OCP\User\Events\PostLoginEvent;
use OCP\User\Events\BeforeUserLoggedOutEvent;
use OCA\OIDCLogin\Events\AccessTokenUpdatedEvent;

class Application extends App implements IBootstrap
{
	public const APP_ID = 'snapmail_lt';

	public function __construct(array $urlParams = [])
	{
		parent::__construct(self::APP_ID, $urlParams);
	}

	public function register(IRegistrationContext $context): void
	{
		/**
		 * Controllers
		 */
		$context->registerService(
			'PageController', function($c) {
				return new PageController(
					$c->query('AppName'),
					$c->query('Request')
				);
			}
		);

		$context->registerService(
			'FetchController', function($c) {
				return new FetchController(
					$c->query('AppName'),
					$c->query('Request'),
					$c->query(IAppManager::class),
					$c->query(IConfig::class),
					$c->query(IL10N::class)
				);
			}
		);

		/**
		 * Utils
		 */
		$context->registerService(
			'SnappyMailHelper', function($c) {
				return new SnappyMailHelper();
			}
		);

		$context->registerSearchProvider(Provider::class);
		$context->registerEventListener(AccessTokenUpdatedEvent::class, AccessTokenUpdatedListener::class);

		// TODO: Not working yet, needs a Vue UI
//		$context->registerDashboardWidget(UnreadMailWidget::class);
	}

	public function boot(IBootContext $context): void
	{
		$server = $context->getServerContainer();
		$config = $server->get(IConfig::class);
		$session = $server->get(ISession::class);

		if (!\is_dir(\rtrim(\trim($config->getSystemValue('datadirectory', '')), '\\/') . '/appdata_snapmail_lt')) {
			return;
		}

		$dispatcher = $server->get(IEventDispatcher::class);
		$logger = $server->get(\Psr\Log\LoggerInterface::class);
		$dispatcher->addListener(PostLoginEvent::class, function (PostLoginEvent $Event) use ($session, $logger) {
/*
			$config = \OC::$server->getConfig();
			// Only store the user's password in the current session if they have
			// enabled auto-login using Nextcloud username or email address.
			if ($config->getAppValue('snapmail_lt', 'snapmail_lt-autologin', false)
			 || $config->getAppValue('snapmail_lt', 'snapmail_lt-autologin-with-email', false)) {
*/
			try {
				$sUID = $Event->getUser()->getUID();
				$session->set('snapmail_lt-nc-uid', $sUID);
				$session->set('snapmail_lt-passphrase', SnappyMailHelper::encodePassword($Event->getPassword(), $sUID));
			} catch (\Throwable $e) {
				$logger->warning('Could not prepare SnapMail LT session after Nextcloud login: ' . $e->getMessage(), [
					'app' => self::APP_ID
				]);
			}
/*
			}
*/
		});

		$dispatcher->addListener(BeforeUserLoggedOutEvent::class, function (BeforeUserLoggedOutEvent $Event) use ($logger) {
			// https://github.com/nextcloud/server/issues/36083#issuecomment-1387370634
//			\OC::$server->getSession()['snapmail_lt-passphrase'] = '';
			try {
				SnappyMailHelper::loadApp();
//			\RainLoop\Api::Actions()->Logout(true);
				\RainLoop\Api::Actions()->DoLogout();
				// The integrated administration creates a short-lived SnappyMail
				// administrator session for the current Nextcloud administrator.
				// Remove it together with the webmail session so it can never
				// survive a Nextcloud logout on a shared browser.
				(new \RainLoop\ActionsAdmin())->DoAdminLogout();
			} catch (\Throwable $e) {
				$logger->warning('Could not clear SnapMail LT session during Nextcloud logout: ' . $e->getMessage(), [
					'app' => self::APP_ID
				]);
			}
		});

		// https://github.com/nextcloud/impersonate/issues/179
		// https://github.com/nextcloud/impersonate/pull/180
		$class = 'OCA\Impersonate\Events\BeginImpersonateEvent';
		if (\class_exists($class)) {
			$dispatcher->addListener($class, function ($Event) use ($session, $logger) {
				$session->set('snapmail_lt-passphrase', '');
				try {
					SnappyMailHelper::loadApp();
					\RainLoop\Api::Actions()->Logout(true);
				} catch (\Throwable $e) {
					$logger->warning('Could not clear SnapMail LT session during impersonation start: ' . $e->getMessage(), [
						'app' => self::APP_ID
					]);
				}
			});
			$dispatcher->addListener('OCA\Impersonate\Events\EndImpersonateEvent', function ($Event) use ($session, $logger) {
				$session->set('snapmail_lt-passphrase', '');
				try {
					SnappyMailHelper::loadApp();
					\RainLoop\Api::Actions()->Logout(true);
				} catch (\Throwable $e) {
					$logger->warning('Could not clear SnapMail LT session during impersonation end: ' . $e->getMessage(), [
						'app' => self::APP_ID
					]);
				}
			});
		}
	}
}

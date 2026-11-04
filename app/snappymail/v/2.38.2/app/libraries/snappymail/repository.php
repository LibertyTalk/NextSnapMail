<?php
/**
 * SnapMail LT — Extension catalog
 *
 * This file answers one question for the Extensions page of the webmail
 * admin panel: "which extensions exist, and which of them are installed?"
 *
 * SnapMail LT ships its extensions inside the app, in the folder
 * app/bundled-plugins/. That folder IS the catalog. Nothing in this file
 * talks to the internet; there is no remote repository to reach, so there
 * is nothing that can be "unavailable".
 *
 * Two folders matter:
 *   - the BUNDLED folder   (app/bundled-plugins/<id>/)  — what ships with the app
 *   - the INSTALLED folder (the data directory's plugins/<id>/) — what has been
 *     copied out of the bundle and can be switched on
 *
 * "Install" copies a bundled extension into the installed folder.
 * "Enable" adds its id to the enabled list in the application config.
 * "Delete" removes it from the installed folder (the bundled copy stays).
 *
 * The Nextcloud integration extension (id "nextcloud") is the one piece the
 * app cannot run without, so it can never be disabled or deleted.
 */

namespace SnappyMail;

abstract class Repository
{
	/** The one extension that must always stay installed and enabled. */
	const REQUIRED_PLUGIN = 'nextcloud';

	// ------------------------------------------------------------------
	// Reading the bundled catalog
	// ------------------------------------------------------------------

	/**
	 * Absolute path of the bundled-plugins folder, with a trailing slash.
	 */
	private static function bundledPath() : string
	{
		return APP_INDEX_ROOT_PATH . 'bundled-plugins/';
	}

	/**
	 * Every extension found in the bundled folder, keyed by id.
	 *
	 * An extension is any sub-folder that contains an index.php. Its name,
	 * version and description are read from the constants that every
	 * SnappyMail plugin declares at the top of its index.php
	 * (NAME, VERSION, DESCRIPTION), so the catalog describes itself and
	 * needs no separate manifest file.
	 */
	private static function getBundledPackages() : array
	{
		$aResult = array();
		$sPath = static::bundledPath();
		if (!\is_dir($sPath)) {
			return $aResult;
		}

		foreach (new \DirectoryIterator($sPath) as $oItem) {
			if ($oItem->isDot() || !$oItem->isDir()) {
				continue;
			}
			$sId = $oItem->getFilename();
			$sIndex = $sPath . $sId . '/index.php';
			if (!\preg_match('/^[a-z0-9\-]+$/', $sId) || !\is_file($sIndex)) {
				continue;
			}

			$aInfo = static::readPluginInfo($sIndex);

			$aResult[$sId] = array(
				'type'         => 'plugin',
				'id'           => $sId,
				'name'         => $aInfo['name'] ?: static::pluginNameFromId($sId),
				'installed'    => '',
				// Not yet installed. The row template greys any row whose
				// "enabled" flag is false, so a catalog entry is marked enabled
				// purely to keep it readable; the real on/off state is set
				// later in getPackagesList() for installed extensions.
				'enabled'      => true,
				'version'      => $aInfo['version'],
				'file'         => $sId,
				'release'      => '',
				'desc'         => $aInfo['description'] ?: 'Bundled extension',
				'canBeDeleted' => false,
				// The row template shows the Install icon only when
				// "canBeUpdated" is true, so a not-yet-installed entry says true.
				'canBeUpdated' => true
			);
		}

		\uksort($aResult, 'strcasecmp');
		return $aResult;
	}

	/**
	 * Pull NAME, VERSION and DESCRIPTION out of a plugin's index.php
	 * without executing it. Missing values come back as empty strings.
	 */
	private static function readPluginInfo(string $sIndexFile) : array
	{
		$aInfo = array('name' => '', 'version' => '', 'description' => '');
		$sSource = (string) \file_get_contents($sIndexFile);
		foreach (array('name' => 'NAME', 'version' => 'VERSION', 'description' => 'DESCRIPTION') as $sKey => $sConst) {
			if (\preg_match('/\b' . $sConst . '\s*=\s*([\'"])(.*?)\1/s', $sSource, $aMatch)) {
				$aInfo[$sKey] = \trim($aMatch[2]);
			}
		}
		return $aInfo;
	}

	/**
	 * Turn an id like "tinymce-composer" into "Tinymce Composer".
	 * Used only when a plugin declares no NAME of its own.
	 */
	private static function pluginNameFromId(string $sId) : string
	{
		return \implode(' ', \array_map('ucfirst', \explode(' ', \preg_replace('/[^a-z0-9]+/i', ' ', $sId))));
	}

	// ------------------------------------------------------------------
	// Copying files
	// ------------------------------------------------------------------

	/**
	 * Copy a folder tree from $sSource to $sDestination, creating folders
	 * as needed. Used to install a bundled extension.
	 */
	private static function copyDirectory(string $sSource, string $sDestination) : void
	{
		if (!\is_dir($sDestination) && !\mkdir($sDestination, 0755, true) && !\is_dir($sDestination)) {
			throw new \RuntimeException("Could not create directory {$sDestination}");
		}

		$oIterator = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator($sSource, \FilesystemIterator::SKIP_DOTS),
			\RecursiveIteratorIterator::SELF_FIRST
		);
		$iPrefixLength = \strlen($sSource) + 1;

		foreach ($oIterator as $oItem) {
			$sTarget = $sDestination . '/' . \substr($oItem->getPathname(), $iPrefixLength);
			if ($oItem->isDir()) {
				if (!\is_dir($sTarget) && !\mkdir($sTarget, 0755, true) && !\is_dir($sTarget)) {
					throw new \RuntimeException("Could not create directory {$sTarget}");
				}
			} elseif (!\copy($oItem->getPathname(), $sTarget)) {
				throw new \RuntimeException("Could not copy {$sTarget}");
			}
		}
	}

	/**
	 * Remove an installed extension's folder (and its .phar, if any).
	 * Returns true when nothing is left behind.
	 */
	private static function deletePackageDir(string $sId) : bool
	{
		$sPath = APP_PLUGINS_PATH . $sId;
		return (!\is_dir($sPath) || \MailSo\Base\Utils::RecRmDir($sPath))
			&& (!\is_file("{$sPath}.phar") || \unlink("{$sPath}.phar"));
	}

	// ------------------------------------------------------------------
	// Core version — SnapMail LT updates as a whole Nextcloud app, never
	// piecemeal from here, so these always say "you are current".
	// ------------------------------------------------------------------

	public static function getLatestCoreInfo()
	{
		\RainLoop\Api::Actions()->IsAdminLoggined();
		return (object) array(
			'version'  => APP_VERSION,
			'file'     => '',
			'warnings' => array()
		);
	}

	public static function downloadCore() : ?string
	{
		return null;
	}

	public static function canUpdateCore() : bool
	{
		return false;
	}

	// ------------------------------------------------------------------
	// Enabling and disabling
	// ------------------------------------------------------------------

	/**
	 * Ids of the extensions currently switched on, lowercased.
	 */
	public static function getEnabledPackagesNames() : array
	{
		return \array_map('trim',
			\explode(',', \strtolower(\RainLoop\Api::Config()->Get('plugins', 'enabled_list', '')))
		);
	}

	/**
	 * Switch an installed extension on ($bEnable = true) or off.
	 * The required extension cannot be switched off.
	 */
	public static function enablePackage(string $sName, bool $bEnable = true) : bool
	{
		$sName = \strtolower(\trim($sName));
		if (!\strlen($sName)) {
			return false;
		}
		if (static::REQUIRED_PLUGIN === $sName && !$bEnable) {
			return false;
		}

		$aEnabled = static::getEnabledPackagesNames();
		if ($bEnable) {
			$aEnabled[] = $sName;
		} else {
			$aEnabled = \array_filter($aEnabled, static function ($sPlugin) use ($sName) {
				return $sName !== $sPlugin && \strlen($sPlugin);
			});
		}

		$oConfig = \RainLoop\Api::Config();
		$oConfig->Set('plugins', 'enabled_list', \trim(\implode(',', \array_unique($aEnabled)), ' ,'));
		return $oConfig->Save();
	}

	// ------------------------------------------------------------------
	// The list the Extensions page shows
	// ------------------------------------------------------------------

	/**
	 * Build the full list for the Extensions page: every bundled extension,
	 * overlaid with what is installed and what is enabled. An installed
	 * extension that is not in the bundle (for example one uploaded by hand)
	 * is listed too, so it can be enabled or deleted.
	 */
	public static function getPackagesList() : array
	{
		empty($_ENV['SNAPPYMAIL_INCLUDE_AS_API']) && \RainLoop\Api::Actions()->IsAdminLoggined();

		$aList = static::getBundledPackages();
		$aEnabled = static::getEnabledPackagesNames();

		foreach (\RainLoop\Api::Actions()->Plugins()->InstalledPlugins() as $aItem) {
			if (!$aItem) {
				continue;
			}
			$sId = $aItem[0];
			$sInstalledVersion = $aItem[1];
			$bIsRequired = static::REQUIRED_PLUGIN === \strtolower($sId);

			if (isset($aList[$sId])) {
				// Bundled and installed: mark it, and offer an update when the bundle is newer.
				$aList[$sId]['installed']    = $sInstalledVersion;
				$aList[$sId]['enabled']      = \in_array(\strtolower($sId), $aEnabled);
				$aList[$sId]['canBeDeleted'] = !$bIsRequired;
				$aList[$sId]['canBeUpdated'] = \version_compare($sInstalledVersion, $aList[$sId]['version'], '<');
			} else {
				// Installed but not bundled (uploaded by hand).
				$aList[$sId] = array(
					'type'         => 'plugin',
					'id'           => $sId,
					'name'         => $aItem[2],
					'installed'    => $sInstalledVersion,
					'enabled'      => \in_array(\strtolower($sId), $aEnabled),
					'version'      => '',
					'file'         => '',
					'release'      => '',
					'desc'         => $aItem[3],
					'canBeDeleted' => !$bIsRequired,
					'canBeUpdated' => false
				);
			}
		}

		// Enabled extensions first, then alphabetical.
		$aList = \array_values($aList);
		\usort($aList, static function ($a, $b) {
			return ((int) !empty($b['enabled']) <=> (int) !empty($a['enabled']))
				?: \strcasecmp($a['name'] ?: $a['id'], $b['name'] ?: $b['id']);
		});

		return array(
			'Real'  => true,
			'List'  => $aList,
			'Error' => ''
		);
	}

	// ------------------------------------------------------------------
	// Install and delete
	// ------------------------------------------------------------------

	/**
	 * Install (or update) an extension by copying it out of the bundle.
	 * The $sType and $sFile arguments are kept for compatibility with the
	 * admin page; only $sType 'plugin' is meaningful here.
	 */
	public static function installPackage(string $sType, string $sId, string $sFile = '') : bool
	{
		empty($_ENV['SNAPPYMAIL_INCLUDE_AS_API']) && \RainLoop\Api::Actions()->IsAdminLoggined();

		\SnappyMail\Log::info('INSTALLER', 'Install bundled extension: ' . $sId);

		if ('plugin' !== $sType || !\preg_match('/^[a-z0-9\-]+$/', $sId)) {
			throw new \Exception('Unknown package: ' . $sId);
		}

		$sBundled = static::bundledPath() . $sId;
		if (!\is_dir($sBundled) || !\is_file($sBundled . '/index.php')) {
			throw new \Exception('Extension is not part of the SnapMail LT bundle: ' . $sId);
		}

		if (!static::deletePackageDir($sId)) {
			throw new \Exception('Cannot remove previous plugin folder: ' . $sId);
		}

		static::copyDirectory($sBundled, APP_PLUGINS_PATH . $sId);
		return true;
	}

	/**
	 * Switch an extension off and remove its installed folder.
	 * The required extension cannot be deleted.
	 */
	public static function deletePackage(string $sId) : bool
	{
		\RainLoop\Api::Actions()->IsAdminLoggined();
		if (static::REQUIRED_PLUGIN === \strtolower(\trim($sId))) {
			return false;
		}
		static::enablePackage($sId, false);
		return static::deletePackageDir($sId);
	}
}

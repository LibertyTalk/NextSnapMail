<div class="section snapmail_lt-admin-settings">
	<form class="snapmail_lt" action="admin.php" method="post">
		<input type="hidden" name="requesttoken" value="<?php echo $_['requesttoken'] ?>" id="requesttoken">
		<fieldset class="personalblock">
			<h2><?php echo($l->t('SnapMail LT Webmail')); ?></h2>
			<p class="settings-hint"><?php echo($l->t('This administration area uses your current Nextcloud administrator session. No separate SnapMail LT login is required.')); ?></p>

			<nav class="snapmail_lt-admin-navigation" aria-label="<?php echo \htmlspecialchars($l->t('SnapMail LT administration'), ENT_QUOTES|ENT_SUBSTITUTE, 'UTF-8'); ?>">
				<button type="button" class="snapmail_lt-admin-navigation-item active" data-snapmail_lt-page="integration"><?php echo($l->t('Nextcloud integration')); ?></button>
				<button type="button" class="snapmail_lt-admin-navigation-item" data-snapmail_lt-page="general"><?php echo($l->t('General')); ?></button>
				<button type="button" class="snapmail_lt-admin-navigation-item" data-snapmail_lt-page="domains"><?php echo($l->t('Domains & Google OAuth')); ?></button>
				<button type="button" class="snapmail_lt-admin-navigation-item" data-snapmail_lt-page="login"><?php echo($l->t('Login')); ?></button>
				<button type="button" class="snapmail_lt-admin-navigation-item" data-snapmail_lt-page="branding"><?php echo($l->t('Branding')); ?></button>
				<button type="button" class="snapmail_lt-admin-navigation-item" data-snapmail_lt-page="contacts"><?php echo($l->t('Contacts')); ?></button>
				<button type="button" class="snapmail_lt-admin-navigation-item" data-snapmail_lt-page="security"><?php echo($l->t('Security')); ?></button>
				<button type="button" class="snapmail_lt-admin-navigation-item" data-snapmail_lt-page="extensions"><?php echo($l->t('Extensions')); ?></button>
				<button type="button" class="snapmail_lt-admin-navigation-item" data-snapmail_lt-page="config"><?php echo($l->t('Advanced configuration')); ?></button>
				<button type="button" class="snapmail_lt-admin-navigation-item" data-snapmail_lt-page="data"><?php echo($l->t('Import & reset')); ?></button>
				<button type="button" class="snapmail_lt-admin-navigation-item" data-snapmail_lt-page="about"><?php echo($l->t('About')); ?></button>
			</nav>

			<div class="snapmail_lt-integrated-admin" data-snapmail_lt-admin-workspace>
				<iframe id="snapmail_lt-admin-frame" title="<?php echo \htmlspecialchars($l->t('SnapMail LT administration'), ENT_QUOTES|ENT_SUBSTITUTE, 'UTF-8'); ?>" src="<?php echo \htmlspecialchars($_['snapmail_lt-integrated-admin-url'], ENT_QUOTES|ENT_SUBSTITUTE, 'UTF-8'); ?>#/settings/domains" loading="eager"></iframe>
			</div>

			<section class="snapmail_lt-admin-context" data-snapmail_lt-context="domains" hidden>
				<div class="snapmail_lt-settings-card">
					<h3><?php echo($l->t('Gmail / Google OAuth2')); ?></h3>
					<p class="settings-hint"><?php echo($l->t('Configure this to let users connect Gmail or Google Workspace accounts without a password.')); ?></p>
					<p><label for="snapmail_lt-gmail-oauth-client-id"><?php echo($l->t('Client ID')); ?></label><input id="snapmail_lt-gmail-oauth-client-id" name="snapmail_lt-gmail-oauth-client-id" type="text" value="<?php echo \htmlspecialchars($_['gmail-oauth-client-id']); ?>"></p>
					<p><label for="snapmail_lt-gmail-oauth-client-secret"><?php echo($l->t('Client Secret')); ?></label><input id="snapmail_lt-gmail-oauth-client-secret" name="snapmail_lt-gmail-oauth-client-secret" type="password" placeholder="<?php echo $_['gmail-oauth-client-secret-set'] ? \htmlspecialchars($l->t('Leave empty to keep existing secret')) : ''; ?>"></p>
					<p><label for="snapmail_lt-gmail-oauth-domains"><?php echo($l->t('Email domains')); ?></label><textarea id="snapmail_lt-gmail-oauth-domains" name="snapmail_lt-gmail-oauth-domains" rows="3"><?php echo \htmlspecialchars($_['gmail-oauth-domains']); ?></textarea></p>
					<p><input id="snapmail_lt-gmail-oauth-auto-configure" name="snapmail_lt-gmail-oauth-auto-configure" type="checkbox" class="checkbox" <?php if ($_['gmail-oauth-auto-configure']) echo 'checked="checked"'; ?>><label for="snapmail_lt-gmail-oauth-auto-configure"><?php echo($l->t('Automatically configure Gmail domains')); ?></label></p>
					<p class="settings-hint snapmail_lt-callback-url"><?php echo($l->t('Authorized redirect URI for Google Cloud Console:')); ?> <code><?php echo \htmlspecialchars($_['gmail-oauth-callback-url'], ENT_QUOTES|ENT_SUBSTITUTE, 'UTF-8'); ?></code></p>
				</div>
			</section>

			<section class="snapmail_lt-admin-context" data-snapmail_lt-context="extensions" hidden>
				<div class="snapmail_lt-settings-card">
					<h3><?php echo($l->t('Install plugin package')); ?></h3>
					<p class="settings-hint"><?php echo($l->t('Upload only trusted SnappyMail/SnapMail LT plugin packages. Plugins are executable PHP code.')); ?></p>
					<input id="snapmail_lt-plugin-package" name="snapmail_lt-plugin-package" type="file" accept=".tgz,.tar.gz,.zip">
					<p><input id="snapmail_lt-plugin-overwrite" name="snapmail_lt-plugin-overwrite" type="checkbox" class="checkbox"><label for="snapmail_lt-plugin-overwrite"><?php echo($l->t('Overwrite existing plugin after creating a backup')); ?></label></p>
					<button id="snapmail_lt-upload-plugin-button" type="button"><?php echo($l->t('Upload and install plugin')); ?></button>
					<div class="snapmail_lt-plugin-upload-result-desc" style="white-space: pre-wrap"></div>
				</div>
			</section>

			<section class="snapmail_lt-admin-page active" data-snapmail_lt-panel="integration">
				<h3><?php echo($l->t('Nextcloud integration')); ?></h3>
				<div class="snapmail_lt-settings-card">
					<p class="snapmail_lt-radio-row"><input type="radio" id="snapmail_lt-noautologin" name="snapmail_lt-autologin" value="0" <?php if (!$_['snapmail_lt-autologin']&&!$_['snapmail_lt-autologin-with-email']) echo 'checked="checked"'; ?>><label for="snapmail_lt-noautologin"><?php echo($l->t('Users will login manually, or define credentials in their personal settings for automatic logins.')); ?></label></p>
					<p class="snapmail_lt-radio-row"><input type="radio" id="snapmail_lt-autologin" name="snapmail_lt-autologin" value="1" <?php if ($_['snapmail_lt-autologin']) echo 'checked="checked"'; ?>><label for="snapmail_lt-autologin"><?php echo($l->t('Attempt to automatically login users with their Nextcloud username and password, or user-defined credentials, if set.')); ?></label></p>
					<p class="snapmail_lt-radio-row"><input type="radio" id="snapmail_lt-autologin-with-email" name="snapmail_lt-autologin" value="2" <?php if ($_['snapmail_lt-autologin-with-email']) echo 'checked="checked"'; ?>><label for="snapmail_lt-autologin-with-email"><?php echo($l->t('Attempt to automatically login users with their Nextcloud email and password, or user-defined credentials, if set.')); ?></label></p>
					<p><input id="snapmail_lt-autologin-oidc" name="snapmail_lt-autologin-oidc" type="checkbox" class="checkbox" <?php if ($_['snapmail_lt-autologin-oidc']) echo 'checked="checked"'; ?>><label for="snapmail_lt-autologin-oidc"><?php echo($l->t('Attempt to automatically login with OIDC when active')); ?></label></p>
					<p><input id="snapmail_lt-debug" name="snapmail_lt-debug" type="checkbox" class="checkbox" <?php if ($_['snapmail_lt-debug']) echo 'checked="checked"'; ?>><label for="snapmail_lt-debug"><?php echo($l->t('Debug')); ?></label></p>
					<?php if ($_['can-import-rainloop']) { ?><p><input id="import-rainloop" name="import-rainloop" type="checkbox" class="checkbox"><label for="import-rainloop"><?php echo($l->t('Import RainLoop data')); ?></label></p><?php } ?>
					<p><input id="snapmail_lt-nc-lang" name="snapmail_lt-nc-lang" type="checkbox" class="checkbox" <?php if ($_['snapmail_lt-nc-lang']) echo 'checked="checked"'; ?>><label for="snapmail_lt-nc-lang"><?php echo($l->t('Force Nextcloud personal language')); ?></label></p>
					<p><label for="snapmail_lt-app_path"><?php echo($l->t('app_path')); ?></label><input id="snapmail_lt-app_path" name="snapmail_lt-app_path" type="text" value="<?php echo \htmlspecialchars($_['snapmail_lt-app_path']); ?>"></p>
					<h4><?php echo($l->t('Integrated functions')); ?></h4>
					<p><input id="nextcloud-plugin-suggestions" name="nextcloud-plugin-suggestions" type="checkbox" class="checkbox" <?php if ($_['nextcloud-plugin-suggestions']) echo 'checked="checked"'; ?>><label for="nextcloud-plugin-suggestions"><?php echo($l->t('Use Nextcloud contacts for recipient suggestions')); ?></label></p>
					<p><input id="nextcloud-plugin-ignore-system-addressbook" name="nextcloud-plugin-ignore-system-addressbook" type="checkbox" class="checkbox" <?php if ($_['nextcloud-plugin-ignore-system-addressbook']) echo 'checked="checked"'; ?>><label for="nextcloud-plugin-ignore-system-addressbook"><?php echo($l->t('Ignore the system address book when using Nextcloud contacts')); ?></label></p>
					<p><input id="nextcloud-plugin-calendar" name="nextcloud-plugin-calendar" type="checkbox" class="checkbox" <?php if ($_['nextcloud-plugin-calendar']) echo 'checked="checked"'; ?>><label for="nextcloud-plugin-calendar"><?php echo($l->t('Allow saving calendar invitations to a Nextcloud calendar')); ?></label></p>
				</div>
			</section>

			<section class="snapmail_lt-admin-page" data-snapmail_lt-panel="data" hidden>
				<h3><?php echo($l->t('Import & reset')); ?></h3>
				<p class="settings-hint"><?php echo($l->t('Import existing SnappyMail data or reset this SnapMail LT installation.')); ?></p>
				<div class="snapmail_lt-settings-card">
					<h3><?php echo($l->t('Import old SnappyMail data')); ?></h3>
					<button id="snapmail_lt-import-snappymail-button" type="button"><?php echo($l->t('Import old SnappyMail data')); ?></button>
					<div class="snapmail_lt-import-result-desc"></div>
				</div>
				<div class="snapmail_lt-settings-card snapmail_lt-danger-zone">
					<h3><?php echo($l->t('Reset SnapMail LT')); ?></h3>
					<p class="settings-hint"><?php echo($l->t('Remove all SnapMail LT account settings and recreate the application data folder.')); ?></p>
					<button id="snapmail_lt-reset-button" type="button"><?php echo($l->t('Reset SnapMail LT')); ?></button>
					<div class="snapmail_lt-reset-result-desc" style="white-space: pre-wrap"></div>
				</div>
			</section>

			<div class="snapmail_lt-save-area" data-snapmail_lt-save-area>
				<button id="snapmail_lt-save-button" name="snapmail_lt-save-button"><?php echo($l->t('Save')); ?></button>
				<div class="snapmail_lt-result-desc" style="white-space: pre"></div>
			</div>
		</fieldset>
	</form>
</div>

<div class="section">
	<form class="snapmail_lt" action="personal.php" method="post">
		<input type="hidden" name="requesttoken" value="<?php echo $_['requesttoken'] ?>" id="requesttoken">
		<fieldset class="personalblock">
			<h2><?php echo $l->t('SnapMail LT Webmail'); ?></h2>
			<p>
				<?php echo $l->t('Enter an email and password to auto-login to SnapMail LT.'); ?>
			</p>
			<p>
				<input type="text" id="snapmail_lt-email" name="snapmail_lt-email"
					value="<?php echo $_['snapmail_lt-email']; ?>" placeholder="<?php echo($l->t('Email')); ?>" />

				<input type="password" id="snapmail_lt-password" name="snapmail_lt-password"
					value="<?php echo $_['snapmail_lt-password']; ?>" placeholder="<?php echo($l->t('Password')); ?>" />

				<button id="snapmail_lt-save-button" name="snapmail_lt-save-button"><?php echo($l->t('Save')); ?></button>
				&nbsp;&nbsp;<span class="snapmail_lt-result-desc"></span>
			</p>
			<?php if (!empty($_['gmail-oauth-enabled'])): ?>
				<p>
					<a class="button" href="<?php echo \htmlspecialchars($_['gmail-oauth-url'], ENT_QUOTES|ENT_SUBSTITUTE, 'UTF-8'); ?>">
						<?php echo($l->t('Connect Gmail / Google account')); ?>
					</a>
				</p>
				<p class="settings-hint">
					<?php echo($l->t('This will replace the saved main account credentials for SnapMail LT with a Google OAuth login.')); ?>
				</p>
			<?php endif; ?>
			<?php if (!empty($_['gmail-oauth-message'])): ?>
				<p class="snapmail_lt-success">
					<?php echo \htmlspecialchars($_['gmail-oauth-message'], ENT_QUOTES|ENT_SUBSTITUTE, 'UTF-8'); ?>
				</p>
			<?php endif; ?>
			<?php if (!empty($_['gmail-oauth-error'])): ?>
				<p class="snapmail_lt-error">
					<?php echo \htmlspecialchars($_['gmail-oauth-error'], ENT_QUOTES|ENT_SUBSTITUTE, 'UTF-8'); ?>
				</p>
			<?php endif; ?>
		</fieldset>
	</form>
</div>

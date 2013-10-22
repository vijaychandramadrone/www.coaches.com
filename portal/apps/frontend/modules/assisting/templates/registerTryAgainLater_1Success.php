


<p>An error has occurred with your account.  Please contact CTI Customer Service at 1-800-691-6008, option 1 for assistance.<p>

<?php if(sfConfig::get('sf_environment') == 'dev'): ?>
<p class="alert">
<?php echo $diagnostics ?>
</p>
<?php endif ?>

<p><a href="<?php echo url_for('main/mySchedule') ?>">Return to my schedule</a></p>





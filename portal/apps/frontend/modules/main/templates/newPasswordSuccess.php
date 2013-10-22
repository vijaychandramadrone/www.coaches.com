

<?php if($ok): ?>
<h1>Thank you</h1>
<p>Your new password has been saved. Please <a href="<?php echo url_for('main/login'); ?>">log in</a>.

<?php else: ?>
<h1>New Password</h1>

<?php if($msg): ?>
<p class="alert"><?php echo $msg ?></p>
<?php endif ?>

<p>Please enter your new password</p>


 <form action="<?php echo url_for('main/newPasswordProcess'); ?>" method="POST">
   <input type="hidden" name="k" value="<?php echo $key ?>">
     <table>
    <tr>
  <th><label for="signin_username">Email address</label></th>
  <td><?php echo $email ?></td>
</tr>
<tr>
  <th><label for="signin_password">Password</label></th>
  <td><input type="password" name="password" id="signin_password" style="width:160px;" /></td>
</tr>
<tr>
  <th><label for="signin_password">Verify</label></th>
  <td><input type="password" name="verify" id="signin_verify"  style="width:160px;" /></td>
</tr>
  <tr><td></td><td>
  <input type="submit" name="submitbtn" value="Save New Password" />
  </td></tr>
  </table>

</form>

 
<?php endif ?>

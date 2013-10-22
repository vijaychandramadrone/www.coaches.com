
<h1>I'm Not Able To Login</h1>

<p>If you have forgotten your password, please enter your email address and click the "Request New Password" button. A link will be sent to the email address on record with us.</p>

<?php if($msg): ?>
<p class="notify"><?php echo $msg ?></p>
<?php endif ?>

 <form action="requestNewPasswordProcess" method="POST">
  <input type="hidden" name="redirect" value="" />
     <table>
    <tr>
  <th><label for="signin_username">Email address</label></th>
  <td><input type="text" name="email" id="signin_username" /></td>
</tr>
  <tr><td></td><td>
  <input type="submit" value="Request New Password" />
  </td></tr>
  </table>

</form>
 
<h1>My Account</h1>

<?php if($emailchange == 'yes'): ?>
   <p class="alert">Your email address change to <b><?php echo $student->getNewEmail() ?></b> is in process. We have sent a note requesting confirmation of this request to your new email address. You must take action within a 24 hour period to validate this request. Following the validation, this will be your primary address for future CTI communications.</p>
<?php endif; ?>

<form action="<?php echo url_for('account/edit') ?>">
<table class="account">

<tr>
<td>First Name</td>
<td><?php echo $student->getFirstName() ?></td>
</tr>

<tr>
<td>Last Name</td>
<td><?php echo $student->getLastName() ?></td>
</tr>

<tr>
<td>Email Address</td>
<td><?php echo $student->getEmail() ?></td>
</tr>

<tr>
<td>Address</td>
<td><?php echo $student->getHomeAddress() ?></td>
</tr>

<tr>
<td>City</td>
<td><?php echo $student->getCity() ?></td>
</tr>

<tr>
<td>State or Province</td>
<td><?php echo $student->getStateProv() ?></td>
</tr>

<tr>
<td>Country</td>
<td><?php echo $student->getCountry() ?></td>
</tr>

<tr>
<td>Zip/Postal Code</td>
<td><?php echo $student->getZipPostal() ?></td>
</tr>

<tr>
<td>Home Phone</td>
<td><?php echo $student->getHomePhone() ?></td>
</tr>

<tr>
<td>Cell Phone</td>
<td><?php echo $student->getCellPhone() ?></td>
</tr>

<!--
<tr>
<td>Business Phone</td>
<td><?php echo $student->getBusinessPhone() ?></td>
</tr>
-->

<tr>
<td colspan="2" style="text-align:center;"><input type="submit" name="submit1" value="Edit My Information" /></td> 

</tr>

</table>
</form>
<h1>My Account</h1>

<?php if($msg): ?>
<p class="alert"><?php echo $msg ?></p>
<?php endif; ?>


<form action="<?php echo url_for('account/update') ?>" method="POST">
<table class="account account-edit">

<tr>
<td>First Name</td>
<td><input type="text" name="first_name" value="<?php echo $student->getFirstName() ?>" /> </td>
</tr>

<tr>
<td>Last Name</td>
<td><input type="text" name="last_name" value="<?php echo $student->getLastName() ?>" /> </td>
</tr>

<tr>
<td>Email Address</td>
<td><input type="text" name="email" value="<?php echo $student->getEmail() ?>" /> </td>
</tr>

<tr>
<td>Address</td>
<td><input type="text" name="home_address" value="<?php echo $student->getHomeAddress() ?>" /> </td>
</tr>

<tr>
<td>City</td>
<td><input type="text" name="city" value="<?php echo $student->getCity() ?>" /> </td>
</tr>

<tr>
<td>State or Province</td>
<td><input type="text" name="state_prov" value="<?php echo $student->getStateProv() ?>" /> </td>
</tr>

<tr>
<td>Country</td>
<td><input type="text" name="country" value="<?php echo $student->getCountry() ?>" /> </td>
</tr>

<tr>
<td>Zip/Postal Code</td>
<td><input type="text" name="zip_postal" value="<?php echo $student->getZipPostal() ?>" /> </td>
</tr>

<tr>
<td>Home Phone</td>
<td><input type="text" name="home_phone" value="<?php echo $student->getHomePhone() ?>" /> </td>
</tr>

<tr>
<td>Cell Phone</td>
<td><input type="text" name="cell_phone" value="<?php echo $student->getCellPhone() ?>" /> </td>
</tr>

<!--
<tr>
<td>Business Phone</td>
<td><input type="text" name="business_phone" value="<?php echo $student->getBusinessPhone() ?>" /> </td>
</tr>
-->
<input type="hidden" name="business_phone" value="<?php echo $student->getBusinessPhone() ?>" />

<tr>
<td></td>
<td ><input style="padding-top:5px;" type="submit" name="submit1" value="Submit Changes" /><br /><br /><input type="button" value="Cancel" onclick="location.href='<?php echo url_for("account/index") ?>'" /></td> 
</tr>

</table>
</form>
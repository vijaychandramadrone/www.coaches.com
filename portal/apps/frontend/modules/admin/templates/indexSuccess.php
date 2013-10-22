<h1>Student Portal Administration</h1>

<p>&nbsp;</p>

<h3>Search for currently enrolled students</h3>
 <form action="<?php echo url_for('admin/index') ?>" method="POST">
 
     <table class="events">
    <tr>
  <th><label for="name">Student Name</label></th>
  <td><input type="text" name="name" id="name" /></td>
</tr>
  <tr><td></td><td>
  <input type="submit" value="Search" />
  </td></tr>
  </table>

</form>
 

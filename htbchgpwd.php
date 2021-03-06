<?php
/*
** FILE: htbchgpwd.page
** PURPOSE: allows to change the password of a user
** AUTHOR(S): Daniel Schreckling
*/
	global $http, $db_link, $htbconf;
	
	// if the user pressed the submit button to change the current password
	if(isset($http['submit'])) {
		// get the account information of the user currently logged in
		$sql="SELECT ".$htbconf['db/users.password']." FROM ".$htbconf['db/users']." where ". $htbconf['db/users.id']."='".$_SESSION['userid']."' and ". $htbconf['db/users.password']."='".$http['oldpwd']."'";
		$result = mysql_query($sql, $db_link);
			
		// check if there was a result
		if($result) {
			$matches = mysql_num_rows($result);
			// there was a match => the old password is correct
			if($matches) {
				// and the two new passwords are not unequal inform the user with an error message
				if($http['newpwd1'] != $http['newpwd2']) {
					$_SESSION['error'] = "<p>Your new password and its retyped version are not equal!</p><p>Password was <b>not changed</b>!</p><p>Please retry!</p>";
					htb_redirect(htb_pageurl('htbchgpwd'));
				} else {
					$sql="update ".$htbconf['db/users']." set ".$htbconf['db/users.password']."='".$http['newpwd1']."' where ". $htbconf['db/users.id']."='".$_SESSION['userid']."'";
					// otherwise change the password
					$result = mysql_query($sql, $db_link);
					// we were successful changing the password
					if($result) {
						$_SESSION['success'] = "<p>Your password has successfully been changed</p>";
						htb_redirect(htb_pageurl('htbmain'));
					}
					// if we had problems changing the password, inform the user
					else {
						$_SESSION['error'] = "<p>An error occurred while changing your password!</p><p>Password was <b>not changed</b>!</p><p>Please retry!</p>";
						htb_redirect(htb_pageurl('htbchgpwd'));
					}
				}
			}
			// the old password was not correct
			else {
					$_SESSION['error'] = "<p>Your old password is not correct.</p><p>Password was <b>not changed</b>!</p><p>Please retry!</p>";
					htb_redirect(htb_pageurl('htbchgpwd'));
			}
		}
		// No result from mysql_query
		else {
			$_SESSION['error'] = "<p>An error occurred while changing your password!</p><p>Password was <b>not changed</b>!</p><p>Please retry!</p>";
			htb_redirect(htb_pageurl('htbchgpwd'));
		}
	}
?>
<table cellspacing="0" cellpadding="4" class="tblInfo">
	<tr>
		<th>
			Change Your Password
		</th>
	</tr>
	<tr>
		<td align="center">
			<form action="index.php?page=htbchgpwd" method="post">
				<table cellspacing="3" cellpadding="3" class="tblForm" align="center">
					<tr>
						<td class="right">Old password</td>
						<td class="left"><input type="password" name="oldpwd" class="txtBox2"></td>
					</tr>
					<tr>
						<td class="right">New password</td>
						<td class="left"><input type="password" name="newpwd1" class="txtBox2"></td>
					</tr>
					<tr>
						<td class="right">Retype password</td>
						<td class="left"><input type="password" name="newpwd2" class="txtBox2"></td>
					</tr>
					<tr>
						<td class="right">&nbsp;</td>
						<td>&nbsp;</td>
					</tr>
					<tr>
						<td colspan="2" class="center"><input type="submit" name="submit" value="Submit" class="butnStyle2" /></td>
					</tr>
				</table>
			</form>
		</td>
	</tr>
</table>

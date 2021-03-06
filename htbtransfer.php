<?php
/*
** FILE: htbtransfer.page
** PURPOSE: page for transfering funds from one account to another
** AUTHOR(S): Daniel Schreckling
*/
	global $db_link, $xorValue, $htbconf;
		// the submit button was pressed
	if(isset($http['htbtransfer']) == 'Transfer') {
		if(!isset($http['srcacc']) || $http['srcacc'] == "") {
			$_SESSION['error'] = "<p>Specify a source account!</p>";
			htb_reload_page();
			exit();
		}
		
		if(!isset($http['dstbank']) || $http['dstbank'] == "") {
			$_SESSION['error'] = "<p>Choose a destination bank!</p>";
			htb_reload_page();
			exit();
		}
		if(!isset($http['dstacc']) || $http['dstacc'] == "") {
			$_SESSION['error'] = "<p>Specify a destination account!</p>";
			htb_reload_page();
			exit();
		}
			
		if($http['dstacc'] == ($http['srcacc'] ^ $xorValue) && $http['dstbank'] == $htbconf['bank/code']) {
			$_SESSION['error'] = "<p>Transfers within the same account are not permitted!</p>";
			htb_reload_page();
			exit();
		}
		
		if(!isset($http['amount']) || $http['amount'] == "" || !is_numeric($http['amount'])) {
			$_SESSION['error'] = "<p>Specify a correct amount you want to transfer!</p><p>Allowed number format is: <b>1234.56</b> or <b>1234</b>";
			htb_reload_page();
			exit();
		}
		// if we get here, all checks were successful thus, we transfer the money check the source account first for sufficient funds
		$sql="select ".$htbconf['db/accounts.curbal'].", ". $htbconf['db/accounts.deposit']." from ".$htbconf['db/accounts']." where ". $htbconf['db/accounts.account']."='".($http['srcacc'] ^ $xorValue)."'";
		$result = mysql_query($sql, $db_link);
		if(is_resource($result)) {
			$row = mysql_fetch_row($result);
			if($row[0] + $row[1] - $http['amount'] < 0) {
				$_SESSION['error'] = "<p>Not sufficient funds for transfer!</p><p>Check your account balance!</p><p>Transfer aborted!</p>\n";
				htb_redirect(htb_getbaseurl().'index.php?page=htbaccounts');
				exit;
			} else {
				$sql="update ".$htbconf['db/accounts']." set ".$htbconf['db/accounts.curbal']."=".$htbconf['db/accounts.curbal']."-(".$http['amount']."), ".$htbconf['db/accounts.time']."=now() where ".$htbconf['db/accounts.account']."=".($http['srcacc'] ^ $xorValue);
				$result = mysql_query($sql, $db_link);
				$sql="update ".$htbconf['db/accounts']." set ".$htbconf['db/accounts.curbal']."=".$htbconf['db/accounts.curbal']."+(".$http['amount']."), ".$htbconf['db/accounts.time']."=now() where ".$htbconf['db/accounts.account']."=".$http['dstacc'];
				$result = mysql_query($sql, $db_link);
				
				// enter the transfer into the transfers table
				$sql="insert into ".$htbconf['db/transfers']." (".$htbconf['db/transfers.time'].", ".$htbconf['db/transfers.srcbank'].", ".$htbconf['db/transfers.srcacc'].", ".$htbconf['db/transfers.dstbank'].", ".$htbconf['db/transfers.dstacc'].", ".$htbconf['db/transfers.remark'].", ".$htbconf['db/transfers.amount'].") values(now(), ".$htbconf['bank/code'].", ".($http['srcacc'] ^ $xorValue).", ".$http['dstbank'].", ".$http['dstacc'].", '".$http['remark']."', ".$http['amount'].")";
				$result = mysql_query($sql);
				
				$_SESSION['success'] = "<p>Your transfer has successfully been processed!</p>\n";
				htb_redirect(htb_getbaseurl().'index.php?page=htbmain');
				exit;
			}
		} else {
			$_SESSION['error'] = "<p>An error occurred while checking your source account!</p><p>Transfer aborted</p><p>Please retry</p>\n";
			htb_reload_page();
			exit();
		}
	}
?>
<table cellspacing="0" cellpadding="4" class="tblInfo">
	<tr><th>Transfer Funds</th></tr>
	<tr>
		<td align="center">
			<form action="index.php?page=htbtransfer" method="get">
				<?php
					print "<input type=\"hidden\" name=\"page\" value=\"htbtransfer\">\n";	// xxx
				?>
				<table cellspacing="3" cellpadding="3" class="tblForm" align="center">
					<tr>
						<td class="right">Source Account No.</td>
						<td class="left">
							<select name="srcacc" class="drpDown2">
								<?php
									global $db_link, $xorValue, $htbconf;
									# get possible source accounts
									$result = mysql_query("SELECT ".$htbconf['db/accounts.account']." FROM ".$htbconf['db/accounts']." where ".$htbconf['db/accounts.owner']."=".$_SESSION['userid'], $db_link);
									$options = "";
									$matches = mysql_num_rows($result);
									for($i=0; $i < $matches; $i++){
										$row = mysql_fetch_row($result);
										$options .= "<option value=\"".($row[0] ^ $xorValue)."\">".$row[0]."</option>";
									}
									
									# get possible banks
									$result = mysql_query("SELECT ".$htbconf['db/banks.code'].", ".$htbconf['db/banks.name'].", ".$htbconf['db/banks.id']." FROM ".$htbconf['db/banks']." order by ".$htbconf['db/banks.code'], $db_link);
									$banks = "";
									$matches = mysql_num_rows($result);
									for($i=0; $i < $matches; $i++){
										$row = mysql_fetch_row($result);
										$banks .= "<option value=\"".$row[0]."\"";
											if($row[0] == $htbconf['bank/code']) $banks .= "selected";
										$banks .= ">".$row[0]." (".$row[1].")</option>";
									}
									
									print "
											$options
							</select>
						</td>
					</tr>
					<tr>
						<td class=\"left_\">
							Destination Bank Code
						</td>
						<td class=\"left\">
							<select name=\"dstbank\" class=\"drpDown2\">
											$banks;
							</select>
						</td>
					</tr>
					<tr>
						<td class=\"left_\">
							Destination Account No.
						</td>
						<td class=\"left\">
							<input name=\"dstacc\" class=\"txtBox2\">
						</td>\n";
						?>
					</tr>
					<tr>
						<td class="right">
							Amount
						</td>
						<td class="left">
							<input type="text" name="amount" class="txtBox2">&nbsp;USD
						</td>
					</tr>
					<tr>
						<td class="right">
							Remark
						</td>
						<td class="left">
							<input type="text" name="remark" class="txtBox2">
						</td>
					</tr>
					<tr><td>&nbsp;</td><td>&nbsp;</td></tr>
					<tr>
						<td colspan="2" class="center">
							<input type="submit" name="htbtransfer" value="Transfer" class="butnStyle2" />
						</td>
					</tr>
				</table>
			</form>
		</td>
	</tr>
</table>

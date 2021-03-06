<?php
/*
** FILE: htbloanreg.page
** PURPOSE: page for requesting a loan from bank
** AUTHOR(S): Daniel Schreckling
*/

//header("access-control-allow-origin: http://127.0.0.1/vBank/");

if (!isset($_SESSION)) {
    session_start(); 
}


?>
<table cellspacing="0" cellpadding="4" class="tblInfo">
	<tr>
		<th>
			Loan request
		</th>
	</tr>
	<tr>
		<td align="center">
			<form action="index.php?page=htbloanconf" method="post">
				<?php
					global $http;
				?>
				<table cellspacing="3" cellpadding="3" class="tblForm" align="center" width="100%">
					<tr>
						<td class="right">
							Credit Account No.
						</td>
						<td class="left">
							<select name="creditacc" class="drpDown2">
								<?php
									global $db_link, $xorValue, $htbconf;
									$sql="SELECT a.".$htbconf['db/accounts.account']." FROM ".$htbconf['db/accounts']." a where ".$htbconf['db/accounts.owner']."=".$_SESSION['userid'];
									$result = mysql_query($sql, $db_link);
									$options = "";
									$matches = mysql_num_rows($result);
									for($i=0; $i < $matches; $i++) {
										$row = mysql_fetch_row($result);
										$options .= "<option value=\"".($row[0] ^ $xorValue)."\">".$row[0]."</option>";
									}
									
									print "
											$options
							</select>
						</td>
					</tr>
					<tr>
						<td class=\"right\">
							Debit Account No.
						</td>
						<td class=\"left\">
							<select name=\"debitacc\" class=\"drpDown2\">
											$options
							</select>
						</td>\n";
						?>
					</tr>
					<tr>
						<td class="right">
							Loan Amount
						</td>
						<td class="left">
							<input type="text" name="loan" class="txtBox2">&nbsp;USD
						</td>
					</tr>
					<tr>
						<td class="right">
							Loan Period
						</td>
						<td class="left">
							<select name="period" class="drpDown2">
								<option value="1">1</option>
								<option value="2">2</option>
								<option value="5">5</option>
								<option value="10">10</option>
								<option value="25">25</option>
							</select>&nbsp;&nbsp;year(s)</td>
						</tr>
						<tr>
							<td class="right">
								Interest rate
							</td>
							<td class="left">
								<?php global $htbconf; print $htbconf['bank/interest']; ?>%
							</td>
						</tr>
						<tr><td>&nbsp;</td><td>&nbsp;</td></tr>
						<tr>
							<td colspan="2" class="center">
								<input type="submit" name="submit" value="Request" class="butnStyle2" />
							</td>
						</tr>
					</table>
					<input type="hidden" name="interest" value="<?php global $htbconf; print $htbconf['bank/interest']; ?>">
				</form>
			</td>
		</tr>
	</table>


<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	$_SESSION['post-data'] = $_POST;
}
?>



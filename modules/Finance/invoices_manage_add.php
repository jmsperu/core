<?
/*
Gibbon, Flexible & Open School System
Copyright (C) 2010, Ross Parker

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program. If not, see <http://www.gnu.org/licenses/>.
*/

session_start() ;

//Module includes
include "./modules/" . $_SESSION[$guid]["module"] . "/moduleFunctions.php" ;

if (isActionAccessible($guid, $connection2, "/modules/Finance/invoices_manage_add.php")==FALSE) {
	//Acess denied
	print "<div class='error'>" ;
		print "You do not have access to this action." ;
	print "</div>" ;
}
else {
	//Proceed!
	print "<div class='trail'>" ;
	print "<div class='trailHead'><a href='" . $_SESSION[$guid]["absoluteURL"] . "'>Home</a> > <a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . getModuleName($_GET["q"]) . "/" . getModuleEntry($_GET["q"], $connection2, $guid) . "'>" . getModuleName($_GET["q"]) . "</a> > <a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/Finance/invoices_manage.php&gibbonSchoolYearID=" . $_GET["gibbonSchoolYearID"] . "'>Manage Invoices</a> > </div><div class='trailEnd'>Add Fees & Invoices</div>" ;
	print "</div>" ;
	
	$addReturn = $_GET["addReturn"] ;
	$addReturnMessage ="" ;
	$class="error" ;
	if (!($addReturn=="")) {
		if ($addReturn=="fail0") {
			$addReturnMessage ="Add failed because you do not have access to this action." ;	
		}
		else if ($addReturn=="fail2") {
			$addReturnMessage ="Add failed due to a database error." ;	
		}
		else if ($addReturn=="fail3") {
			$addReturnMessage ="Add failed because your inputs were invalid." ;	
		}
		else if ($addReturn=="fail4") {
			$addReturnMessage ="Some aspects of your update failed, effecting the following areas:<ul>" ;	
			if ($_GET["studentFailCount"]) {
				$addReturnMessage.="<li>" . $_GET["studentFailCount"] . " students encountered problems.</li>" ;
			}
			if ($_GET["invoiceFailCount"]) {
				$addReturnMessage.="<li>" . $_GET["invoiceFailCount"] . " invoices encountered problems.</li>" ;
			}
			if ($_GET["invoiceFeeFailCount"]) {
				$addReturnMessage.="<li>" . $_GET["invoiceFeeFailCount"] . " fee entires encountered problems.</li>" ;
			}
			$addReturnMessage.="</ul>It is recommended that you remove all pending invoices and try to recreate them." ;
		}
		else if ($addReturn=="success0") {
			$addReturnMessage ="Add was successful. You can add more records if you wish." ;	
			$class="success" ;
		}
		print "<div class='$class'>" ;
			print $addReturnMessage;
		print "</div>" ;
	}
	
	print "<p>" ;
		print "Here you can add fees to one or more students. These fees will be added to an existing invoice or used to form a new invoice, depending on the specified billing schedule and other details." ;
	print "</p>" ; 
	
	//Check if school year specified
	$gibbonSchoolYearID=$_GET["gibbonSchoolYearID"] ;
	$status=$_GET["status"] ;
	$gibbonFinanceInvoiceeID=$_GET["gibbonFinanceInvoiceeID"] ;
	$monthOfIssue=$_GET["monthOfIssue"] ;
	$gibbonFinanceBillingScheduleID=$_GET["gibbonFinanceBillingScheduleID"] ;
	if ($gibbonSchoolYearID=="") {
		print "<div class='error'>" ;
			print "You have not specified a school year." ;
		print "</div>" ;
	}
	else {
		if ($status!="" OR $gibbonFinanceInvoiceeID!="" OR $monthOfIssue!="" OR $gibbonFinanceBillingScheduleID!="") {
			print "<div class='linkTop'>" ;
				print "<a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/Finance/invoices_manage.php&gibbonSchoolYearID=$gibbonSchoolYearID&status=$status&gibbonFinanceInvoiceeID=$gibbonFinanceInvoiceeID&monthOfIssue=$monthOfIssue&gibbonFinanceBillingScheduleID=$gibbonFinanceBillingScheduleID'>Back to Search Results</a>" ;
			print "</div>" ;
		}
		?>
		<form method="post" action="<? print $_SESSION[$guid]["absoluteURL"] . "/modules/" . $_SESSION[$guid]["module"] . "/invoices_manage_addProcess.php?gibbonSchoolYearID=$gibbonSchoolYearID&status=$status&gibbonFinanceInvoiceeID=$gibbonFinanceInvoiceeID&monthOfIssue=$monthOfIssue&gibbonFinanceBillingScheduleID=$gibbonFinanceBillingScheduleID" ?>">
			<table style="width: 100%">	
				<tr><td style="width: 30%"></td><td></td></tr>
				<tr>
					<td colspan=2> 
						<h3 class='top'>Basic Information</h3>
					</td>
				</tr>
				<tr>
					<td> 
						<b>School Year *</b><br/>
						<span style="font-size: 90%"><i>This value cannot be changed.</i></span>
					</td>
					<td class="right">
						<?
						$yearName="" ;
						try {
							$dataYear=array("gibbonSchoolYearID"=>$gibbonSchoolYearID); 
							$sqlYear="SELECT * FROM gibbonSchoolYear WHERE gibbonSchoolYearID=:gibbonSchoolYearID" ;
							$resultYear=$connection2->prepare($sqlYear);
							$resultYear->execute($dataYear);
						}
						catch(PDOException $e) { 
							print "<div class='error'>" . $e->getMessage() . "</div>" ; 
						}
						if ($resultYear->rowCount()==1) {
							$rowYear=$resultYear->fetch() ;
							$yearName=$rowYear["name"] ;
						}
						?>
						<input readonly name="yearName" id="yearName" maxlength=20 value="<? print $yearName ?>" type="text" style="width: 300px">
						<script type="text/javascript">
							var yearName = new LiveValidation('yearName');
							yearName.add(Validate.Presence);
						</script>
					</td>
				</tr>
				<tr>
					<td> 
						<b>Invoicees *</b><br/>
						<span style="font-size: 90%"><i>Use Control and/or Shift to select multiple. If a student is missing from this list, visit <a href='<? print $_SESSION[$guid]["absoluteURL"] ?>/index.php?q=/modules/Finance/invoicees_manage.php'>Manage Invoicees</a> to automatically generate them.</i></span>
					</td>
					<td class="right">
						<select name="gibbonFinanceInvoiceeIDs[]" id="gibbonFinanceInvoiceeIDs[]" multiple style="width: 302px; height: 150px">
							<optgroup label='--Enrolable Students--'>
							<?
							try {
								$dataSelect=array("gibbonSchoolYearID"=>$gibbonSchoolYearID); 
								$sqlSelect="SELECT gibbonFinanceInvoiceeID, preferredName, surname, gibbonRollGroup.name AS name FROM gibbonPerson, gibbonStudentEnrolment, gibbonRollGroup, gibbonFinanceInvoicee WHERE gibbonPerson.gibbonPersonID=gibbonStudentEnrolment.gibbonPersonID AND gibbonStudentEnrolment.gibbonRollGroupID=gibbonRollGroup.gibbonRollGroupID AND gibbonFinanceInvoicee.gibbonPersonID=gibbonPerson.gibbonPersonID AND status='FULL' AND gibbonRollGroup.gibbonSchoolYearID=:gibbonSchoolYearID ORDER BY name, surname, preferredName" ;
								$resultSelect=$connection2->prepare($sqlSelect);
								$resultSelect->execute($dataSelect);
							}
							catch(PDOException $e) { }
							while ($rowSelect=$resultSelect->fetch()) {
								print "<option value='" . $rowSelect["gibbonFinanceInvoiceeID"] . "'>" . htmlPrep($rowSelect["name"]) . " - " . formatName("", htmlPrep($rowSelect["preferredName"]), htmlPrep($rowSelect["surname"]), "Student", true) . "</option>" ;
							}
							?>
							</optgroup>
							<optgroup label='--All Users--'>
							<?
							try {
								$dataSelect=array(); 
								$sqlSelect="SELECT gibbonFinanceInvoiceeID, surname, preferredName, status FROM gibbonPerson JOIN gibbonFinanceInvoicee ON (gibbonFinanceInvoicee.gibbonPersonID=gibbonPerson.gibbonPersonID) ORDER BY surname, preferredName" ;
								$resultSelect=$connection2->prepare($sqlSelect);
								$resultSelect->execute($dataSelect);
							}
							catch(PDOException $e) { }
							while ($rowSelect=$resultSelect->fetch()) {
								print "<option value='" . $rowSelect["gibbonFinanceInvoiceeID"] . "'>" . formatName("", htmlPrep($rowSelect["preferredName"]), htmlPrep($rowSelect["surname"]), "Student", true) . "</option>" ;
							}
							?>
							</optgroup>
						</select>
					</td>
				</tr>
				<? //BILLING TYPE CHOOSER ?>
				<tr>
					<td> 
						<b>Scheduling *</b><br/>
						<span style="font-size: 90%"><i>When using scheduled, invoice due date is linked to and determined by the schedule.</i></span>
					</td>
					<td class="right">
						<input checked type="radio" name="scheduling" class="scheduling" value="Scheduled" /> Scheduled
						<input type="radio" name="scheduling" class="scheduling" value="Ad Hoc" /> Ad Hoc
					</td>
				</tr>
				<script type="text/javascript">
					$(document).ready(function(){
						$("#adHocRow").css("display","none");
						invoiceDueDate.disable() ;
						$("#schedulingRow").slideDown("fast", $("#schedulingRow").css("display","table-row")); 
						
						$(".scheduling").click(function(){
							if ($('input[name=scheduling]:checked').val() == "Scheduled" ) {
								$("#adHocRow").css("display","none");
								invoiceDueDate.disable() ;
								$("#schedulingRow").slideDown("fast", $("#schedulingRow").css("display","table-row")); 
								gibbonFinanceBillingScheduleID.enable() ;
							} else {
								$("#schedulingRow").css("display","none");
								gibbonFinanceBillingScheduleID.disable() ;
								$("#adHocRow").slideDown("fast", $("#adHocRow").css("display","table-row")); 
								invoiceDueDate.enable() ;
							}
						 });
					});
				</script>
				
				<tr id="schedulingRow">
					<td> 
						<b>Billing Schedule *</b><br/>
					</td>
					<td class="right">
						<select name="gibbonFinanceBillingScheduleID" id="gibbonFinanceBillingScheduleID" style="width: 302px">
							<?
							print "<option value='Please select...'>Please select...</option>" ;
							try {
								$dataSelect=array(); 
								$sqlSelect="SELECT * FROM gibbonFinanceBillingSchedule WHERE active='Y' ORDER BY name" ;
								$resultSelect=$connection2->prepare($sqlSelect);
								$resultSelect->execute($dataSelect);
							}
							catch(PDOException $e) { }
							while ($rowSelect=$resultSelect->fetch()) {
								print "<option value='" . $rowSelect["gibbonFinanceBillingScheduleID"] . "'>" . $rowSelect["name"] . "</option>" ;
							}
							?>				
						</select>
						<script type="text/javascript">
							var gibbonFinanceBillingScheduleID = new LiveValidation('gibbonFinanceBillingScheduleID');
							gibbonFinanceBillingScheduleID.add(Validate.Exclusion, { within: ['Please select...'], failureMessage: "Select something!"});
						 </script>
					</td>
				</tr>
				<tr id="adHocRow">
					<td> 
						<b>Invoice Due Date *</b><br/>
						<span style="font-size: 90%"><i>For fees added to existing invoice, specified date will override existing due date.</i></span>
					</td>
					<td class="right">
						<input name="invoiceDueDate" id="invoiceDueDate" maxlength=10 value="" type="text" style="width: 300px">
							<script type="text/javascript">
							var invoiceDueDate = new LiveValidation('invoiceDueDate');
							invoiceDueDate.add( Validate.Format, {pattern: /^(0[1-9]|[12][0-9]|3[01])[- /.](0[1-9]|1[012])[- /.](19|20)\d\d$/i, failureMessage: "Use dd/mm/yyyy." } ); 
							invoiceDueDate.add(Validate.Presence);
						 </script>
						 <script type="text/javascript">
							$(function() {
								$( "#invoiceDueDate" ).datepicker();
							});
						</script>
					</td>
				</tr>
				<tr>
					<td colspan=2> 
						<b>Notes</b> 
						<textarea name='notes' id='notes' rows=5 style='width: 300px'></textarea>
					</td>
				</tr>
				
				<tr>
					<td colspan=2> 
						<h3>Fees</h3>
					</td>
				</tr>
				<? 
				$type="fee" ; 
				?> 
				<style>
					#<? print $type ?> { list-style-type: none; margin: 0; padding: 0; width: 100%; }
					#<? print $type ?> div.ui-state-default { margin: 0 0px 5px 0px; padding: 5px; font-size: 100%; min-height: 58px; }
					div.ui-state-default_dud { margin: 5px 0px 5px 0px; padding: 5px; font-size: 100%; min-height: 58px; }
					html>body #<? print $type ?> li { min-height: 58px; line-height: 1.2em; }
					.<? print $type ?>-ui-state-highlight { margin-bottom: 5px; min-height: 58px; line-height: 1.2em; width: 100%; }
					.<? print $type ?>-ui-state-highlight {border: 1px solid #fcd3a1; background: #fbf8ee url(images/ui-bg_glass_55_fbf8ee_1x400.png) 50% 50% repeat-x; color: #444444; }
				</style>
				<tr>
					<td colspan=2> 
						<div class="fee" id="fee" style='width: 100%; padding: 5px 0px 0px 0px; border-top: 1px solid #333; border-bottom: 1px solid #333; min-height: 66px'>
							<div id="feeOuter0">
								<div style='color: #ddd; font-size: 230%; margin: 15px 0 0 6px'>Fees will be listed here...</div>
							</div>
						</div>
						<div style='width: 100%; padding: 0px 0px 0px 0px; border-bottom: 1px solid #333'>
							<div class="ui-state-default_dud odd" style='padding: 0px; height: 60px'>
								<table style='width: 100%'>
									<tr>
										<td style='width: 50%'>
											<script type="text/javascript">
												var feeCount=1 ;
											</script>
											<select id='newFee' onChange='feeDisplayElements(this.value);' style='float: none; margin-left: 3px; margin-top: 0px; margin-bottom: 3px; width: 350px'>
												<option class='all' value='0'>Choose a fee to add it</option>
												<?
												print "<option value='Ad Hoc'>Ad Hoc Fee</option>" ;
												$switchContents.="case \"Ad Hoc\": " ;
												$switchContents.="$(\"#fee\").append('<div id=\'feeOuter' + feeCount + '\'><img style=\'margin: 10px 0 5px 0\' src=\'" . $_SESSION[$guid]["absoluteURL"] . "/themes/Default/img/loading.gif\' alt=\'Loading\' onclick=\'return false;\' /><br/>Loading</div>');" ;
												$switchContents.="$(\"#feeOuter\" + feeCount).load(\"" . $_SESSION[$guid]["absoluteURL"] . "/modules/Finance/invoices_manage_add_blockFeeAjax.php\",\"mode=add&id=\" + feeCount + \"&feeType=" . urlencode("Ad Hoc") . "&gibbonFinanceFeeID=&name=" . urlencode("Ad Hoc Fee") . "&description=&gibbonFinanceFeeCategoryID=1&fee=\") ;" ;
												$switchContents.="feeCount++ ;" ;
												$switchContents.="$('#newFee').val('0');" ;
												$switchContents.="break;" ;
												$currentCategory="" ;
												$lastCategory="" ;
												for ($i=0; $i<2; $i++) {
													try {
														$dataSelect=array("gibbonSchoolYearID"=>$gibbonSchoolYearID); 
														if ($i==0) {
															$sqlSelect="SELECT gibbonFinanceFee.*, gibbonFinanceFeeCategory.name AS category FROM gibbonFinanceFee LEFT JOIN gibbonFinanceFeeCategory ON (gibbonFinanceFee.gibbonFinanceFeeCategoryID=gibbonFinanceFeeCategory.gibbonFinanceFeeCategoryID) WHERE gibbonFinanceFee.active='Y' AND gibbonSchoolYearID=:gibbonSchoolYearID AND NOT gibbonFinanceFee.gibbonFinanceFeeCategoryID=1 ORDER BY gibbonFinanceFee.gibbonFinanceFeeCategoryID, gibbonFinanceFee.name" ;
														}
														else {
															$sqlSelect="SELECT gibbonFinanceFee.*, gibbonFinanceFeeCategory.name AS category FROM gibbonFinanceFee LEFT JOIN gibbonFinanceFeeCategory ON (gibbonFinanceFee.gibbonFinanceFeeCategoryID=gibbonFinanceFeeCategory.gibbonFinanceFeeCategoryID) WHERE gibbonFinanceFee.active='Y' AND gibbonSchoolYearID=:gibbonSchoolYearID AND gibbonFinanceFee.gibbonFinanceFeeCategoryID=1 ORDER BY gibbonFinanceFee.gibbonFinanceFeeCategoryID, gibbonFinanceFee.name" ;
														}
														$resultSelect=$connection2->prepare($sqlSelect);
														$resultSelect->execute($dataSelect);
													}
													catch(PDOException $e) { 
														print "<div class='error'>" . $e->getMessage() . "</div>" ; 
													}
													while ($rowSelect=$resultSelect->fetch()) {
														$currentCategory=$rowSelect["category"] ;
														if (($currentCategory!=$lastCategory) AND $currentCategory!="") {
															print "<optgroup label='--" . $currentCategory . "--'>" ;
															$categories[$categoryCount]= $currentCategory ;
															$categoryCount++ ;
														}
														print "<option value='" . $rowSelect["gibbonFinanceFeeID"] . "'>" . $rowSelect["name"] . "</option>" ;
														$switchContents.="case \"" . $rowSelect["gibbonFinanceFeeID"] . "\": " ;
														$switchContents.="$(\"#fee\").append('<div id=\'feeOuter' + feeCount + '\'><img style=\'margin: 10px 0 5px 0\' src=\'" . $_SESSION[$guid]["absoluteURL"] . "/themes/Default/img/loading.gif\' alt=\'Loading\' onclick=\'return false;\' /><br/>Loading</div>');" ;
														$switchContents.="$(\"#feeOuter\" + feeCount).load(\"" . $_SESSION[$guid]["absoluteURL"] . "/modules/Finance/invoices_manage_add_blockFeeAjax.php\",\"mode=add&id=\" + feeCount + \"&feeType=Standard&gibbonFinanceFeeID=" .  urlencode($rowSelect["gibbonFinanceFeeID"]) . "&name=" . urlencode($rowSelect["name"]) . "&description=" . urlencode($rowSelect["description"]) . "&gibbonFinanceFeeCategoryID=" . urlencode($rowSelect["gibbonFinanceFeeCategoryID"]) . "&fee=" . urlencode($rowSelect["fee"]) . "&category=" . urlencode($rowSelect["category"]) . "\") ;" ;
														$switchContents.="feeCount++ ;" ;
														$switchContents.="$('#newFee').val('0');" ;
														$switchContents.="break;" ;
														$lastCategory=$rowSelect["category"] ;
													}
												}
												?>
											</select>
											<script type='text/javascript'>
												function feeDisplayElements(number) {
													$("#<? print $type ?>Outer0").css("display", "none") ;
													switch(number) {
														<? print $switchContents ?>
													}
												}
											</script>
										</td>
									</tr>
								</table>
							</div>
						</div>
					</td>
				</tr>
				
				<tr>
					<td class="right" colspan=2>
						<input name="gibbonFinanceInvoiceID" id="gibbonFinanceInvoiceID" value="<? print $gibbonFinanceInvoiceID ?>" type="hidden">
						<input type="hidden" name="address" value="<? print $_SESSION[$guid]["address"] ?>">
						<input type="reset" value="Reset"> <input type="submit" value="Submit">
					</td>
				</tr>
				<tr>
					<td class="right" colspan=2>
						<span style="font-size: 90%"><i>* denotes a required field</i></span>
					</td>
				</tr>
			</table>
		</form>
		<?
	}
}
?>
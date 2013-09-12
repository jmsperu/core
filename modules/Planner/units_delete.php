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

if (isActionAccessible($guid, $connection2, "/modules/Planner/units_delete.php")==FALSE) {
	//Acess denied
	print "<div class='error'>" ;
		print "You do not have access to this action." ;
	print "</div>" ;
}
else {
	//Get action with highest precendence
	$highestAction=getHighestGroupedAction($guid, $_GET["q"], $connection2) ;
	if ($highestAction==FALSE) {
		print "<div class='error'>" ;
		print "The highest grouped action cannot be determined." ;
		print "</div>" ;
	}
	else {
		//Proceed!
		print "<div class='trail'>" ;
		print "<div class='trailHead'><a href='" . $_SESSION[$guid]["absoluteURL"] . "'>Home</a> > <a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . getModuleName($_GET["q"]) . "/" . getModuleEntry($_GET["q"], $connection2, $guid) . "'>" . getModuleName($_GET["q"]) . "</a> > <a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . getModuleName($_GET["q"]) . "/units.php&gibbonSchoolYearID=" . $_GET["gibbonSchoolYearID"] . "&gibbonCourseID=" . $_GET["gibbonCourseID"] . "'>Manage Units</a> > </div><div class='trailEnd'>Delete Unit</div>" ;
		print "</div>" ;
		
		$deleteReturn = $_GET["deleteReturn"] ;
		$deleteReturnMessage ="" ;
		$class="error" ;
		if (!($deleteReturn=="")) {
			if ($deleteReturn=="fail0") {
				$deleteReturnMessage ="Update failed because you do not have access to this action." ;	
			}
			else if ($deleteReturn=="fail1") {
				$deleteReturnMessage ="Update failed because a required parameter was not set." ;	
			}
			else if ($deleteReturn=="fail2") {
				$deleteReturnMessage ="Update failed due to a database error." ;	
			}
			else if ($deleteReturn=="fail3") {
				$deleteReturnMessage ="Update failed because your inputs were invalid." ;	
			}
			print "<div class='$class'>" ;
				print $deleteReturnMessage;
			print "</div>" ;
		} 
		
		//Check if courseschool year specified
		$gibbonSchoolYearID=$_GET["gibbonSchoolYearID"];
		$gibbonCourseID=$_GET["gibbonCourseID"]; 
		$gibbonUnitID=$_GET["gibbonUnitID"]; 
		if ($gibbonCourseID=="" OR $gibbonSchoolYearID=="") {
			print "<div class='error'>" ;
				print "You have not specified a course." ;
			print "</div>" ;
		}
		else {
			try {
				if ($highestAction=="Manage Units_all") {
					$dataCourse=array("gibbonSchoolYearID"=>$gibbonSchoolYearID, "gibbonCourseID"=>$gibbonCourseID); 
					$sqlCourse="SELECT * FROM gibbonCourse WHERE gibbonSchoolYearID=:gibbonSchoolYearID AND gibbonCourseID=:gibbonCourseID" ;
				}
				else if ($highestAction=="Manage Units_learningAreas") {
					$dataCourse=array("gibbonSchoolYearID"=>$gibbonSchoolYearID, "gibbonCourseID"=>$gibbonCourseID, "gibbonPersonID"=>$_SESSION[$guid]["gibbonPersonID"]); 
					$sqlCourse="SELECT gibbonCourseID, gibbonCourse.name, gibbonCourse.nameShort FROM gibbonCourse JOIN gibbonDepartment ON (gibbonCourse.gibbonDepartmentID=gibbonDepartment.gibbonDepartmentID) JOIN gibbonDepartmentStaff ON (gibbonDepartmentStaff.gibbonDepartmentID=gibbonDepartment.gibbonDepartmentID) WHERE gibbonDepartmentStaff.gibbonPersonID=:gibbonPersonID AND (role='Coordinator' OR role='Assistant Coordinator' OR role='Teacher (Curriculum)') AND gibbonSchoolYearID=:gibbonSchoolYearID AND gibbonCourseID=:gibbonCourseID ORDER BY gibbonCourse.nameShort" ;
				}
				$resultCourse=$connection2->prepare($sqlCourse);
				$resultCourse->execute($dataCourse);
			}
			catch(PDOException $e) { 
				print "<div class='error'>" . $e->getMessage() . "</div>" ; 
			}

			if ($resultCourse->rowCount()!=1) {
				print "<div class='error'>" ;
					print "The specified course cannot be found or you do not have access to it." ;
				print "</div>" ;
			}
			else {
				//Check if unit specified
				if ($gibbonUnitID=="") {
					print "<div class='error'>" ;
						print "You have not specified a unit." ;
					print "</div>" ;
				}
				else {
					try {
						$data=array("gibbonUnitID"=>$gibbonUnitID, "gibbonCourseID"=>$gibbonCourseID); 
						$sql="SELECT * FROM gibbonUnit WHERE gibbonUnitID=:gibbonUnitID AND gibbonCourseID=:gibbonCourseID" ;
						$result=$connection2->prepare($sql);
						$result->execute($data);
					}
					catch(PDOException $e) { 
						print "<div class='error'>" . $e->getMessage() . "</div>" ; 
					}
					
					if ($result->rowCount()!=1) {
						print "<div class='error'>" ;
							print "The specified unit cannot be found." ;
						print "</div>" ;
					}
					else {
						//Let's go!
						$row=$result->Fetch() ;
						?>
						<form method="post" action="<? print $_SESSION[$guid]["absoluteURL"] . "/modules/" . $_SESSION[$guid]["module"] . "/units_deleteProcess.php?gibbonUnitID=$gibbonUnitID&gibbonCourseID=$gibbonCourseID&gibbonSchoolYearID=$gibbonSchoolYearID" ?>">
							<table style="width: 100%">	
								<tr>
									<td> 
										<b>Are you sure you want to delete unit "<? print $row["name"] ?>"?</b><br/>
										<span style="font-size: 90%; color: #cc0000"><i>This operation cannot be undone, and may lead to loss of vital data in your system.<br/>PROCEED WITH CAUTION!</i></span>
									</td>
									<td class="right">
										
									</td>
								</tr>
								<tr>
									<td> 
										<input name="gibbonCourseID" id="gibbonCourseID" value="<? print $gibbonCourseID ?>" type="hidden">
										<input name="gibbonUnitID" id="gibbonUnitID" value="<? print $gibbonUnitID ?>" type="hidden">
										<input name="gibbonSchoolYearID" id="gibbonSchoolYearID" value="<? print $gibbonSchoolYearID ?>" type="hidden">
										<input type="hidden" name="address" value="<? print $_SESSION[$guid]["address"] ?>">
										<input type="submit" value="Yes">
									</td>
									<td class="right">
										
									</td>
								</tr>
							</table>
						</form>
						<?
					}
				}
			}
		}
	}
	//Print sidebar
	$_SESSION[$guid]["sidebarExtra"]=sidebarExtraUnits($guid, $connection2, $gibbonCourseID, $gibbonSchoolYearID) ;
}
?>
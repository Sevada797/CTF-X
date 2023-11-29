<?php
session_start();
error_reporting(0);
if (isset($_SESSION["status"])) {
if ($_SESSION["status"]=="loggedin") {
//Don't think that you can hack session cookies... it all works in server side, you actually only see PHPSESSID which
//contains the info like of a memory address which tells serevr where cookies are actually stored in the server
header("Location: adminpanel.php");
die;
}
}
//LATER CODE SHOULD BE ADDED SO THAT IF PC IS CONNECTED TO A PROXY THAN DIE(MSG) 

$IP=$_SERVER["REMOTE_ADDR"];

$host = "127.0.0.1";
$usr = "root";
$pwd = "";
$db = "test";
$attempts=[];

//FETCHING ALL ATTEMPTS
//is it possible for SQLI by manipulating http request? hmm not sure we will check that later
$conn=mysqli_connect($host, $usr, $pwd, $db);
$sql3="SELECT * FROM `limiter` WHERE `IP`='$IP'# AND `TIME`==NULL";
$result=$conn->query($sql3);
while ($row=$result->fetch_assoc()) {
array_push($attempts, 1);
}

//ALL ATTEMPTS FETCHING FINISHED
//STRUCTURE
/*
        
        FETCH ALL ATTEMPTS ASSOCIATED WITH CURRENT IP THAN

        IF (ATTEMPTS==5)                ----------------------
                                                              \
        INSERT IP AND TIME IN DB AND DIE(TOO MANY ATTEMPTS) <---

        ELSE IF (ATTEMPTS>5)                            ------------
                                                                    \
        FETCH TIME ASSOCIATED WITH IP | IF 5MIN PASSED ---      <---
  -------------------------------------                   \ \
 |DELETE ALL ASSOCIATED WITH IP, AND PROCEED LOGIN <-------  --------> ELSE ---> DIE(MSG) 

        FUNCTION LOGIN() {
            if creds are correct procced to login
            else if attempt == 4 {return true}
            else add attempt to db
            //I made it a lil bit complex but main thing is it works perfect
        } <--
                                   \
        ELSE AUTOMATICALLY LOGIN() -




*/


if (count($attempts)>4) {
//I have only one row that has no attempt value but instead has time value so...

$result1=$conn->query("SELECT * FROM `limiter` WHERE `IP`='$IP' AND `TIME` IS NOT NULL");
if (!$result1) {
    die("Error " . $conn->error);
}
while ($row=$result1->fetch_assoc()) {
$time=$row["TIME"];
}

if (substr(join("", explode(":", join("", explode("T",join("", explode("-", date("c"))))))), 0, -5)>intval($time)) {
//5 minutes had passed, we need to remove all where IP is curr. client IP, also proceed to try logging in

$conn->query("DELETE FROM `limiter` WHERE `IP`='$IP'");
login();

}
else {
die("<script>alert('Limitation time didn\'t passed yet try a bit later'); window.location='index.php'</script><noscript>please turn on JS for better experience,<br>also limitation time didn't passed yet try a bit later</noscript>");
}
//WHEN else doesn't work ELSE IF CAN WORK, I AM BREAKING THE STANDARDS OF IF-ELSE IF-ELSE ORDER xD

}

else if (count($attempts)==4 && login()) {
$LIMIT_EXP_TIME=date("c", strtotime("+5 minutes"));
$LIMIT_EXP_TIME=intval(substr(join("", explode(":", join("", explode("T",join("", explode("-", $LIMIT_EXP_TIME)))))), 0, -5));
( $conn->query("INSERT INTO `limiter` (`IP`,`TIME`,`attempt`) VALUES('$IP', '$LIMIT_EXP_TIME', NULL)") );

die("<script>alert('Too many login attempts try 5 minutes later.'); window.location='index.php'</script><noscript>please turn on JS for better experience,<br>also too many login attempts try 5 minutes later</noscript>");
}

///////////||||||||||\\\\\\\\\
//FUNCTION LOGIN        ||||||
//\\\\\\\\\||||||||||/////////
function login() {
$attempts=$GLOBALS["attempts"];
$conn=$GLOBALS["conn"];
$IP=$GLOBALS["IP"];
$sql="SELECT * FROM `hackme` LIMIT 1";
$result2=$conn->query($sql);
while ($row=$result2->fetch_assoc()) {
$name=$row["username"];
$pass=$row["password"];
}
//Check if creds are correct, if correct set session cookie and redirect to adminpanel
if ($_POST["u"]===$name && $_POST["p"]===$pass) {
$_SESSION["status"] = "loggedin";
header("Location: adminpanel.php");
$conn->query("DELETE FROM `limiter` WHERE `IP`='$IP'");
die;
}
else if (count($attempts)==4) {
return true;
}
else {
//send attempt to db
//just in advance run this with internet conn 4 now
$sql2="INSERT INTO `limiter` (`IP`, `TIME`, `attempt`) VALUE('$IP', NULL, 'attemptx')";
$conn->query($sql2);
die("<script>alert('Wrong username or password'); window.location='index.php'</script><noscript>please turn on JS for better experience,<br>also wrong username or password</noscript>");

}


}


login();



?>

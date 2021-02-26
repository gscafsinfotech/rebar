<?php
echo phpinfo();
//set variables for connecting to mssql

$mshostname = "SATHISH\SQLEXPRESS_2019";
$msusername = "SATHISH\CAFS-INFOTECH";
$mspassword = "";
$msdbName   = "SmartHrRdd";
/*
$serverName = "SATHISH\SQLEXPRESS_2019";
    $connectionOptions = array("Database"=>"SmartHrRdd", "Uid"=>"SATHISH\CAFS-INFOTECH", "PWD"=>"");
    
     //Establishes the connection
     $conn = sqlsrv_connect($serverName, $connectionOptions);*/

//set variables for connecting to mysql

$myhostname = "MYSQL SERVER";
$myusername = "";
$mypassword = "";
$mydbname = "";
$mytablename = "";



mssql_pconnect($mshostname,$msusername,$mspassword) OR DIE("MSSQL Database connection failed.");

mssql_select_db($msdbName) or DIE("MSSQL DB unavailable");



mysql_pconnect($myhostname,$myusername,$mypassword) OR DIE("MySQL Database connection failed.");

mysql_select_db($mydbname) or DIE("MySQL DB unavailable");




?>
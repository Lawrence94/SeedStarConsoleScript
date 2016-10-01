<?php 

$url = "http://jenkinsuser:jenkinspassword@jenkinsinstance/api/json";     
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);

curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 

// $output contains the output string
$output = curl_exec($ch);
$stuff = json_decode($output);
$jobs = $stuff->jobs;

$dir = 'sqlite:db/jenkins.db';
$dbconn  = new PDO($dir) or die("cannot open the database");
$dbconn->setAttribute(PDO::ATTR_ERRMODE, 
                            PDO::ERRMODE_EXCEPTION);

// Prepare INSERT statement to SQLite3 file db
$insert = "INSERT INTO jobdetails (jobname, jobstatus, timechecked) 
            VALUES (:jobname, :jobstatus, :timechecked)";

$stmt = $dbconn->prepare($insert);

// Bind parameters to statement variables
$stmt->bindParam(':jobname', $title);
$stmt->bindParam(':jobstatus', $status);
$stmt->bindParam(':timechecked', $time);

foreach ($jobs as $val) {
  $http = str_replace('http://', '', $val->url);
  $url1 = "http://jenkinsuser:jenkinspassword@" . $http . 'lastBuild/api/json'; 
  $ch1 = curl_init();
  curl_setopt($ch1, CURLOPT_URL, $url1);

  curl_setopt($ch1, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch1, CURLOPT_CUSTOMREQUEST, "GET");
  curl_setopt($ch1, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch1, CURLOPT_SSL_VERIFYHOST, false);
  curl_setopt($ch1, CURLOPT_SSL_VERIFYPEER, false); 

  // $output contains the output string
  $output1 = curl_exec($ch1);
  $statuses = json_decode($output1);

  $title = $val->name;
  $status = $statuses->result;
  $time = date('y-M-d H:m:s');
  //var_dump($time);
  // Execute statement
  try {
    $stmt->execute();
  } catch (Exception $e) {
    echo $e->getMessage();
  }
  echo "Done inserting";
  curl_close($ch1);  
}
//exit;

// close curl resource to free up system resources
curl_close($ch);    
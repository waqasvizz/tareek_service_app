<?php
	require('iPayBenefitPipe.php');
	
	$myObj =new iPayBenefitPipe(); 
	
	// modify the following to reflect your "Alias Name", "resource.cgn" file path, "keystore.pooh" file path.
	$myObj->setAlias("test");
	$myObj->setResourcePath("resource/"); //only the path that contains the file; do not write the file name
	$myObj->setKeystorePath("resource/"); //only the path that contains the file; do not write the file name
	
	$trandata = "";
	$paymentID = "";
	$result = "";
	$responseCode = "";
	$response = "";
	$transactionID = "";
	$referenceID = "";
	$trackID = "";
	$amount = "";
	$UDF1 = "";
	$UDF2 = "";
	$UDF3 = "";
	$UDF4 = "";
	$UDF5 = "";
	$authCode = "";
	$postDate = "";
	$errorCode = "";
	$errorText = "";
	
	$trandata = isset($_POST["trandata"]) ? $_POST["trandata"] : "";
	
	if ($trandata != "")
	{
		$returnValue = $myObj->parseEncryptedRequest($trandata);
		if ($returnValue == 0)
		{
			$paymentID = $myObj->getPaymentId();
			$result = $myObj->getresult();
			$responseCode = $myObj->getAuthRespCode();
			$transactionID = $myObj->getTransId();
			$referenceID = $myObj->getRef();
			$trackID = $myObj->getTrackId();
			$amount = $myObj->getAmt();
			$UDF1 = $myObj->getUdf1();
			$UDF2 = $myObj->getUdf2();
			$UDF3 = $myObj->getUdf3();
			$UDF4 = $myObj->getUdf4();
			$UDF5 = $myObj->getUdf5();
			$authCode = $myObj->getAuth();
			$postDate = $myObj->getDate();
			$errorCode = $myObj->getError();
			$errorText = $myObj->getError_text();

		}
		else
		{
			$errorText = $myObj->getError_text();
		}
	}
	else if (isset($_POST["ErrorText"]))
    {
        $paymentID = $_POST["paymentid"];
        $trackID = $_POST["trackid"];
        $amount = $_POST["amt"];
        $UDF1 = $_POST["udf1"];
        $UDF2 = $_POST["udf2"];
        $UDF3 = $_POST["udf3"];
        $UDF4 = $_POST["udf4"];
        $UDF5 = $_POST["udf5"];
        $errorText = $_POST["ErrorText"];
    }
    else
    {
        $errorText = "Unknown Exception";
    }
	

	// Remove any HTML/CSS/javascrip from the page. Also, you MUST NOT write anything on the page EXCEPT the word "REDIRECT=" (in upper-case only) followed by a URL.
	// If anything else is written on the page then you will not be able to complete the process.
	if ($myObj->getResult() == "CAPTURED")
	{
		echo("REDIRECT=https://www.yourWebsite.com/PG/approved.php");
	}
	else if ($myObj->getResult() == "NOT CAPTURED" || $myObj->getResult() == "CANCELED" || $myObj->getResult() == "DENIED BY RISK" || $myObj->getResult() == "HOST TIMEOUT")
	{
		if ($myObj->getResult() == "NOT CAPTURED")
		{
			switch ($myObj->getAuthRespCode())
			{
				case "05":
					$response = "Please contact issuer";
					break;
				case "14":
					$response = "Invalid card number";
					break;
				case "33":
					$response = "Expired card";
					break;
				case "36":
					$response = "Restricted card";
					break;
				case "38":
					$response = "Allowable PIN tries exceeded";
					break;
				case "51":
					$response = "Insufficient funds";
					break;
				case "54":
					$response = "Expired card";
					break;
				case "55":
					$response = "Incorrect PIN";
					break;
				case "61":
					$response = "Exceeds withdrawal amount limit";
					break;
				case "62":
					$response = "Restricted Card";
					break;
				case "65":
					$response = "Exceeds withdrawal frequency limit";
					break;
				case "75":
					$response = "Allowable number PIN tries exceeded";
					break;
				case "76":
					$response = "Ineligible account";
					break;
				case "78":
					$response = "Refer to Issuer";
					break;
				case "91":
					$response = "Issuer is inoperative";
					break;
				default:
					// for unlisted values, please generate a proper user-friendly message
					$response = "Unable to process transaction temporarily. Try again later or try using another card.";
					break;
			}
		}
		else if ($myObj->getResult() == "CANCELED")
		{
			$response = "Transaction was canceled by user.";
		}
		else if ($myObj->getResult() == "DENIED BY RISK")
		{
			$response = "Maximum number of transactions has exceeded the daily limit.";
		}
		else if ($myObj->getResult() == "HOST TIMEOUT")
		{
			$response = "Unable to process transaction temporarily. Try again later.";
		}
		echo "REDIRECT=https://www.yourWebsite.com/PG/declined.php";
	}
	else
	{
		//Unable to process transaction temporarily. Try again later or try using another card.
		echo "REDIRECT=https://www.yourWebsite.com/PG/err-response.php";
	}
	
?>
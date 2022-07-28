<?php
	require('iPayBenefitPipe.php');
	$trandata = isset($_POST["trandata"]) ? $_POST["trandata"] : "";
	
	if ($trandata != "")
	{
		$Pipe = new iPayBenefitPipe();
		
		// modify the following to reflect your "Terminal Resourcekey"
		$Pipe->setkey("ABC0123456789");
		
		$Pipe->settrandata($trandata);
		
		$returnValue =  $Pipe->parseResponseTrandata();
		if ($returnValue == 1)
		{
			$paymentID = $Pipe->getpaymentId();
			$result = $Pipe->getresult();
			$responseCode = $Pipe->getauthRespCode();
			$transactionID = $Pipe->gettransId();
			$referenceID = $Pipe->getref();
			$trackID = $Pipe->gettrackId();
			$amount = $Pipe->getamt();
			$UDF1 = $Pipe->getudf1();
			$UDF2 = $Pipe->getudf2();
			$UDF3 = $Pipe->getudf3();
			$UDF4 = $Pipe->getudf4();
			$UDF5 = $Pipe->getudf5();
			$authCode = $Pipe->getauthCode();
			$postDate = $Pipe->gettranDate();
			$errorCode = $Pipe->geterror();
			$errorText = $Pipe->geterrorText();
		
			// Remove any HTML/CSS/javascrip from the page. Also, you MUST NOT write anything on the page EXCEPT the word "REDIRECT=" (in upper-case only) followed by a URL.
			// If anything else is written on the page then you will not be able to complete the process.
			if ($Pipe->getresult() == "CAPTURED")
			{
				echo("REDIRECT=https://www.yourWebsite.com/PG/approved.php");
			}
			else if ($Pipe->getresult() == "NOT CAPTURED" || $Pipe->getresult() == "CANCELED" || $Pipe->getresult() == "DENIED BY RISK" || $Pipe->getresult() == "HOST TIMEOUT")
			{
				if ($Pipe->getresult() == "NOT CAPTURED")
				{
					switch ($Pipe->getAuthRespCode())
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
				else if ($Pipe->getresult() == "CANCELED")
				{
					$response = "Transaction was canceled by user.";
				}
				else if ($Pipe->getresult() == "DENIED BY RISK")
				{
					$response = "Maximum number of transactions has exceeded the daily limit.";
				}
				else if ($Pipe->getresult() == "HOST TIMEOUT")
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
		}
		else
		{
			$errorText = $Pipe->geterrorText();
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


?>
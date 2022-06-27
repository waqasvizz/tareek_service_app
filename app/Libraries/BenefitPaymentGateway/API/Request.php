<?php
	require('iPayBenefitPipe.php');

	$Pipe = new iPayBenefitPipe();
	
	// modify the following to reflect your "Tranportal ID", "Tranportal Password ", "Terminal Resourcekey"
	$Pipe->setkey("1234567");
	$Pipe->setid("1234567");
	$Pipe->setpassword("0123456789");
	
	// Do NOT change the values of the following parameters at all.
	$Pipe->setaction("1");
	$Pipe->setcardType("D");
	$Pipe->setcurrencyCode("048");
	
	// modify the following to reflect your pages URLs
	$Pipe->setresponseURL("https://www.yourWebsite.com/PG/response.php");
	$Pipe->seterrorURL("https://www.yourWebsite.com/PG/error.php");
	
	// set a unique track ID for each transaction so you can use it later to match transaction response and identify transactions in your system and “BENEFIT Payment Gateway” portal.
	$Pipe->settrackId("ABC123456789");
	
	// set transaction amount
	$Pipe->setamt("1.500");
	
	// The following user-defined fields (UDF1, UDF2, UDF3, UDF4, UDF5) are optional fields.
	// However, we recommend setting theses optional fields with invoice/product/customer identification information as they will be reflected in “BENEFIT Payment Gateway” portal where you will be able to link transactions to respective customers. This is helpful for dispute cases. 
	$Pipe->setudf1("set value 1");
	$Pipe->setudf2("set value 2");
	$Pipe->setudf3("set value 3");
	$Pipe->setudf4("set value 4");
	$Pipe->setudf5("set value 5");
	
	$isSuccess = $Pipe->performeTransaction();
	if($isSuccess==1){
		header('location:'.$Pipe->getresult());
	}
	else{
		echo 'Error: '.$Pipe->geterror().'<br />Error Text: '.$Pipe->geterrorText();
	}
?>
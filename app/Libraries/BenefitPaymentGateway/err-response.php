<?php
	echo "<b>From Resposne Page</b>" . "<br /><br />";
	echo "Payment ID: " . $_POST["paymentid"] . "<br />";
	echo "Track ID: " . $_POST["trackid"] . "<br />";
	echo "Amount: " . $_POST["amt"] . "<br />";
	echo "UDF 1: " . $_POST["udf1"] . "<br />";
	echo "UDF 2: " . $_POST["udf2"] . "<br />";
	echo "UDF 3: " . $_POST["udf3"] . "<br />";
	echo "UDF 4: " . $_POST["udf4"] . "<br />";
	echo "UDF 5: " . $_POST["udf5"] . "<br />";
	echo "Error Text: " . $_POST["ErrorText"] . "<br />";
?>
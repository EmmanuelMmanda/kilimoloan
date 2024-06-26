<?php
include 'db_connect.php';

// Check if the POST keys exist before accessing them
$sessionId = isset($_POST['sessionId']) ? $_POST['sessionId'] : '';
$serviceCode = isset($_POST['serviceCode']) ? $_POST['serviceCode'] : '';
$phoneNumber = isset($_POST['phoneNumber']) ? $_POST['phoneNumber'] : '';
$text = isset($_POST['text']) ? $_POST['text'] : '';

$query = "SELECT id,firstname,lastname FROM borrowers WHERE contact_no = '$phoneNumber'";
$borrower = $conn->query($query)->fetch_assoc();
// Explode $text to handle multi-level input
$inputArray = explode('*', $text);

// Initialize response
$response = "END Neno lisilotambulika. Tafadhali jaribu tena.";

// Main menu
if ($text == "") {
    if(!$borrower){
        $response = "END habari , Hujasajiliwa katika mfumo wa mikopo ya pembejeo , wasiliana nasi kupitia namba ya huduma kwa wteja +255200112233 ili usajiliwe ";
        sendMessage($phoneNumber,'register');

    }else{
        $name = $borrower['firstname']. ' '. $borrower['lastname'];
        $response = "CON Karibu .$name. kwenye mfumo wa pembejeo Tanzania\n Chagua huduma\n";
        $response .= "1. Omba mkopo\n";
        $response .= "2. Maelezo ya mikopo\n";
        $response .= "3. Lipa mkopo";
    }
}
// Omba mkopo
elseif ($text == "1") {
    $response = "CON Je wewe ni nani?\n";
    $response .= "1. Mkulima Binafsi\n";
    $response .= "2. Taasisi\n";
    $response .= "3. Kikundi";
}
// Maelezo ya mikopo
elseif ($text == "2") {
    $response = "END Mikopo hii ni maalumu kwa wakulima waliojisajili katika ofisi za halmashauri za wilaya";
    sendMessage($phoneNumber,'loans_info');

}
// Lipa mkopo
elseif ($text == "3") {
    $response = "END KuLipa mkopo katika Taasisi ya Pembejeo\n";
    $response .= "Wasiliana nasi kupitia\n";
    $response .= "1. Huduma: +2552200112233\n";
    $response .= "2. Barua Pepe: mikopo@pemebejeo.go.tz\n";
    $response .= "3. Fika Ofisi ilio karibu nawe kwa malekezo ya malipo \n";

    sendMessage($phoneNumber,'repayment_details');
}
// Handle Mkulima Binafsi flow
elseif ($text == "1*1") {
    $response = "CON Chagua aina ya mkopo\n";
    // Query the loan_types table
    $sql = "SELECT * FROM loan_types";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $response .= $row['id'] . ". " . $row['type_name'] . "\n";
        }
    } else {
        $response .= "Hakuna aina za mikopo.\n";
    }
}
elseif (preg_match('/^1\*1\*\d+$/', $text)) { // Matches 1*1*{loan_type_id}
    $loan_type_id = $inputArray[2];

    $response = "CON Ingiza kiasi cha mkopo ,  (TZS)\n";
}
elseif (preg_match('/^1\*1\*\d+\*\d+$/', $text)) { // Matches 1*1*{loan_type_id}*{amount}
    $loan_type_id = $inputArray[2];
    $amount = $inputArray[3];
    $query = "SELECT type_name FROM loan_types WHERE id = '$loan_type_id'";
    $ltype = $conn->query($query)->fetch_assoc();
    $typename = $ltype['type_name'];
    $response = "CON Umekubaliana na mkopo wa $amount TZS, aina ya mkopo inayotumika ni: $typename.\n";
    $response .= "1. Ndiyo\n";
    $response .= "2. Hapana";
}
elseif (preg_match('/^1\*1\*\d+\*\d+\*1$/', $text)) { // Matches 1*1*{loan_type_id}*{amount}*1 (Ndiyo)
    $loan_type_id = $inputArray[2];
    $amount = $inputArray[3];
  
    // Call save_loan function for Mkulima Binafsi
    $result = save_loan($phoneNumber, 'Mkulima Binafsi', $loan_type_id, $amount);

    if ($result) {
        $response = "END Ombi lako la mkopo limewasilishwa. Utapokea ujumbe hivi karibuni.";
    } else {
        $response = "END Kuna tatizo katika kuwasilisha ombi lako. Tafadhali jaribu tena baadaye.";
    }
}
elseif (preg_match('/^1\*1\*\d+\*\d+\*2$/', $text)) { // Matches 1*1*{loan_type_id}*{amount}*2 (Hapana)
    $response = "END Ombi lako la mkopo limekataliwa.";
}
// Handle Taasisi flow
elseif ($text == "1*2") {
    $response = "CON Chagua aina ya mkopo\n";
    // Query the loan_types table
    $sql = "SELECT * FROM loan_types";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $response .= $row['id'] . ". " . $row['type_name'] . "\n";
        }
    } else {
        $response .= "Hakuna aina za mikopo.\n";
    }
}
elseif (preg_match('/^1\*2\*\d+$/', $text)) { // Matches 1*2*{loan_type_id}
    $loan_type_id = $inputArray[2];

    $response = "CON Ingiza kiasi cha mkopo (TZS)\n";
   
}
elseif (preg_match('/^1\*2\*\d+\*\d+$/', $text)) { // Matches 1*2*{loan_type_id}*{amount}
    $loan_type_id = $inputArray[2];
    $amount = $inputArray[3];
    $query = "SELECT type_name FROM loan_types WHERE id = '$loan_type_id'";
    $ltype = $conn->query($query)->fetch_assoc();
    $typename = $ltype['type_name'];
    $response = "CON Umekubaliana na mkopo wa $amount TZS, aina ya mkopo inayotumika ni: $typename.\n";
    $response .= "1. Ndiyo\n";
    $response .= "2. Hapana";
}
elseif (preg_match('/^1\*2\*\d+\*\d+\*1$/', $text)) { // Matches 1*2*{loan_type_id}*{amount}*1 (Ndiyo)
    $loan_type_id = $inputArray[2];
    $amount = $inputArray[3];

    // Call save_loan function for Taasisi
    $result = save_loan($phoneNumber, 'Taasisi', $loan_type_id, $amount);

    if ($result) {
        $response = "END Ombi lako la mkopo limewasilishwa. Utapokea ujumbe hivi karibuni.";
    } else {
        $response = "END Kuna tatizo katika kuwasilisha ombi lako. Tafadhali jaribu tena baadaye.";
    }
}
elseif (preg_match('/^1\*2\*\d+\*\d+\*2$/', $text)) { // Matches 1*2*{loan_type_id}*{amount}*2 (Hapana)
    $response = "END Ombi lako la mkopo limekataliwa.";
}
// Handle Kikundi flow
elseif ($text == "1*3") {
    $response = "CON Chagua aina ya mkopo\n";
    // Query the loan_types table
    $sql = "SELECT * FROM loan_types";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $response .= $row['id'] . ". " . $row['type_name'] . "\n";
        }
    } else {
        $response .= "Hakuna aina za mikopo.\n";
    }
}
elseif (preg_match('/^1\*3\*\d+$/', $text)) { // Matches 1*3*{loan_type_id}
    $loan_type_id = $inputArray[2];

    $response = "CON Ingiza kiasi cha mkopo (TZS)\n";
   
}
elseif (preg_match('/^1\*3\*\d+\*\d+$/', $text)) { // Matches 1*3*{loan_type_id}*{amount}
    $loan_type_id = $inputArray[2];
    $amount = $inputArray[3];
    $query = "SELECT type_name FROM loan_types WHERE id = '$loan_type_id'";
    $ltype = $conn->query($query)->fetch_assoc();
    $typename = $ltype['type_name'];
    $response = "CON Umekubaliana na mkopo wa $amount TZS, aina ya mkopo inayotumika ni : $typename.\n";
    $response .= "1. Ndiyo\n";
    $response .= "2. Hapana";
}
elseif (preg_match('/^1\*3\*\d+\*\d+\*1$/', $text)) { // Matches 1*3*{loan_type_id}*{amount}*1 (Ndiyo)
    $loan_type_id = $inputArray[2];
    $amount = $inputArray[3];

    // Call save_loan function for Kikundi
    $result = save_loan($phoneNumber, 'Kikundi', $loan_type_id, $amount);

    if ($result) {
        $response = "END Ombi lako la mkopo limewasilishwa. Utapokea ujumbe hivi karibuni.";
    } else {
        $response = "END Kuna tatizo katika kuwasilisha ombi lako. Tafadhali jaribu tena baadaye.";
    }
}
elseif (preg_match('/^1\*3\*\d+\*\d+\*2$/', $text)) { // Matches 1*3*{loan_type_id}*{amount}*2 (Hapana)
    $response = "END Ombi lako la mkopo limekataliwa.";
}

// Send the response back to Africa's Talking
header('Content-type: text/plain');
echo $response;

// Close the connection
$conn->close();

// Function to save loan application to database
function save_loan($phoneNumber, $borrower_type, $loan_type_id, $amount) {
    global $conn;

    // Fetch borrower_id based on phoneNumber
    $query = "SELECT id FROM borrowers WHERE contact_no = '$phoneNumber'";
    $borrower = $conn->query($query)->fetch_assoc();

    if (!$borrower) {

        return false; // Return error if borrower not found
    }

    $borrower_id = $borrower['id'];
    $ref_no = random_int(100,590909890);
    // Prepare data string for SQL query
    $data = " borrower_id = '$borrower_id' ";
    $data .= " , loan_type_id = '$loan_type_id' ";
    $data .= " , ref_no = '$ref_no' ";
    $data .= " , amount = '$amount' ";
    $data .= " , purpose = 'Pembejeo' ";
    $data .= " , plan_id = 1 ";
    $data .= " , status = '0' "; // 0 (requesteing)

    // Save loan application to loan_list table
    $save = $conn->query("INSERT INTO loan_list SET ".$data);

    if ($save) {
        sendMessage($phoneNumber, 'success_loan_request');
        return true; // Success
    } else {
        sendMessage($phoneNumber, 'failed_loan');
        return false; // Error
    }
}




function sendMessage($phoneNumber, $messageType) {
    // Replace these with your own credentials
    $username = "Sandbox";
    $apiKey = "atsk_38b30abe434109925dd72a426458edc88c4bb7ca6965703b2fdabe3c17a9c6b8a3f18848";
    $senderId = "PEMBEJEO LOANS"; 

    $repay = "KuLipa mkopo katika Taasisi ya Pembejeo\n";
    $repay .= "Wasiliana nasi kupitia\n";
    $repay .= "1. Huduma: +2552200112233\n";
    $repay .= "2. Barua Pepe: mikopo@pemebejeo.go.tz\n";
    $repay .= "3. Fika Ofisi ilio karibu nawe kwa malekezo ya malipo \n";


    $reg = "Hujasajiliwa katika mfumo wa Mikopo ya Pembejeo\n";
    $reg .= "Wasiliana nasi kupitia\n";
    $reg .= "1. Huduma: +2552200112233\n";
    $reg .= "2. Barua Pepe: mikopo@pemebejeo.go.tz\n";
    $reg .= "3. Fika Ofisi ilio karibu nawe kwa malekezo ya usajili \n";

    // Define message types and their corresponding messages
    $messages = [
        'failed_loan' => 'Habari , Ombi lako la mkopo Limesitishwa au kukataliwa . Wasiliana nasi kwa maelezo zaidi',
        'success_loan_request' => 'Habari , Ombi lako la mkopo wa Pemebejeo Limetumwa kikamilifu . Tutakutaarifu Pindi litakapo kubaliwa',
        'success_loan' => 'Habari , Ombi lako la mkopo wa Pemebejeo Limekubaliwa . Fika ofisi ilio karibu nawe kwa maelezo zaidi',
        'loans_info' => 'Hii ni Mikopo inayotolowea kwa wakulima kuwasaidia katka mambo mbali mbali ..',
        'repayment_details' => $repay,
        'register' => $reg,
    ];

    // Check if the message type exists in the predefined messages
    if (!array_key_exists($messageType, $messages)) {
        throw new Exception('Invalid message type.');
    }

    $message = $messages[$messageType];

    // Set up the POST data
    $postData = [
        'username' => $username,
        'to' => $phoneNumber,
        'message' => $message,
        'from' => $senderId, // Optional
    ];

    // Initialize cURL
    $ch = curl_init();

    // Set cURL options
    curl_setopt($ch, CURLOPT_URL, 'https://api.sandbox.africastalking.com/version1/messaging');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'apiKey: ' . $apiKey,
        'Content-Type: application/x-www-form-urlencoded',
    ]);

    // Execute cURL request and get response
    $response = curl_exec($ch);

    // Check for errors
    if ($response === false) {
        throw new Exception('cURL Error: ' . curl_error($ch));
    }

    // Close cURL
    curl_close($ch);

    // Decode response
    // $responseDecoded = json_decode($response, true);
    // echo $response;
    // // Check response status
    // if ($responseDecoded['SMSMessageData']['Recipients'][0]['status'] !== 'Success') {
    //     throw new Exception('Failed to send message: ' . $responseDecoded['SMSMessageData']['Recipients'][0]['status']);
    // }

    return true;
}

?>

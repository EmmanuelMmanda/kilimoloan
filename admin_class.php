<?php
session_start();
ini_set('display_errors', 1);
class Action
{
	private $db;

	public function __construct()
	{
		ob_start();
		include 'db_connect.php';

		$this->db = $conn;
	}
	function __destruct()
	{
		$this->db->close();
		ob_end_flush();
	}

	function login()
	{
		extract($_POST);
		$qry = $this->db->query("SELECT * FROM users where username = '" . $username . "' and password = '" . $password . "' ");
		if ($qry->num_rows > 0) {
			foreach ($qry->fetch_array() as $key => $value) {
				if ($key != 'passwors' && !is_numeric($key))
					$_SESSION['login_' . $key] = $value;
			}
			return 1;
		} else {
			return 3;
		}
	}
	function login2()
	{
		extract($_POST);
		$qry = $this->db->query("SELECT * FROM users where username = '" . $email . "' and password = '" . md5($password) . "' ");
		if ($qry->num_rows > 0) {
			foreach ($qry->fetch_array() as $key => $value) {
				if ($key != 'passwors' && !is_numeric($key))
					$_SESSION['login_' . $key] = $value;
			}
			return 1;
		} else {
			return 3;
		}
	}
	function logout()
	{
		session_destroy();
		foreach ($_SESSION as $key => $value) {
			unset($_SESSION[$key]);
		}
		header("location:login.php");
	}
	function logout2()
	{
		session_destroy();
		foreach ($_SESSION as $key => $value) {
			unset($_SESSION[$key]);
		}
		header("location:../index.php");
	}

	function save_user()
	{
		extract($_POST);
		$data = " name = '$name' ";
		$data .= ", username = '$username' ";
		$data .= ", password = '$password' ";
		$data .= ", type = '$type' ";
		if (empty($id)) {
			$save = $this->db->query("INSERT INTO users set " . $data);
		} else {
			$save = $this->db->query("UPDATE users set " . $data . " where id = " . $id);
		}
		if ($save) {
			return 1;
		}
	}
	function signup()
	{
		extract($_POST);
		$data = " name = '$name' ";
		$data .= ", contact = '$contact' ";
		$data .= ", address = '$address' ";
		$data .= ", username = '$email' ";
		$data .= ", password = '" . md5($password) . "' ";
		$data .= ", type = 3";
		$chk = $this->db->query("SELECT * FROM users where username = '$email' ")->num_rows;
		if ($chk > 0) {
			return 2;
			exit;
		}
		$save = $this->db->query("INSERT INTO users set " . $data);
		if ($save) {
			$qry = $this->db->query("SELECT * FROM users where username = '" . $email . "' and password = '" . md5($password) . "' ");
			if ($qry->num_rows > 0) {
				foreach ($qry->fetch_array() as $key => $value) {
					if ($key != 'passwors' && !is_numeric($key))
						$_SESSION['login_' . $key] = $value;
				}
			}
			return 1;
		}
	}

	function save_settings()
	{
		extract($_POST);
		$data = " name = '" . str_replace("'", "&#x2019;", $name) . "' ";
		$data .= ", email = '$email' ";
		$data .= ", contact = '$contact' ";
		$data .= ", about_content = '" . htmlentities(str_replace("'", "&#x2019;", $about)) . "' ";
		if ($_FILES['img']['tmp_name'] != '') {
			$fname = strtotime(date('y-m-d H:i')) . '_' . $_FILES['img']['name'];
			$move = move_uploaded_file($_FILES['img']['tmp_name'], '../assets/img/' . $fname);
			$data .= ", cover_img = '$fname' ";

		}

		// echo "INSERT INTO system_settings set ".$data;
		$chk = $this->db->query("SELECT * FROM system_settings");
		if ($chk->num_rows > 0) {
			$save = $this->db->query("UPDATE system_settings set " . $data);
		} else {
			$save = $this->db->query("INSERT INTO system_settings set " . $data);
		}
		if ($save) {
			$query = $this->db->query("SELECT * FROM system_settings limit 1")->fetch_array();
			foreach ($query as $key => $value) {
				if (!is_numeric($key))
					$_SESSION['setting_' . $key] = $value;
			}

			return 1;
		}
	}


	function save_loan_type()
	{
		extract($_POST);
		$data = " type_name = '$type_name' ";
		$data .= " , description = '$description' ";
		if (empty($id)) {
			$save = $this->db->query("INSERT INTO loan_types set " . $data);
		} else {
			$save = $this->db->query("UPDATE loan_types set " . $data . " where id=" . $id);
		}
		if ($save)
			return 1;
	}
	function delete_loan_type()
	{
		extract($_POST);
		$delete = $this->db->query("DELETE FROM loan_types where id = " . $id);
		if ($delete)
			return 1;
	}
	function save_plan()
	{
		extract($_POST);
		$data = " months = '$months' ";
		$data .= ", interest_percentage = '$interest_percentage' ";
		$data .= ", penalty_rate = '$penalty_rate' ";

		if (empty($id)) {
			$save = $this->db->query("INSERT INTO loan_plan set " . $data);
		} else {
			$save = $this->db->query("UPDATE loan_plan set " . $data . " where id=" . $id);
		}
		if ($save)
			return 1;
	}
	function delete_plan()
	{
		extract($_POST);
		$delete = $this->db->query("DELETE FROM loan_plan where id = " . $id);
		if ($delete)
			return 1;
	}
	function save_borrower()
	{
		extract($_POST);
		$data = " lastname = '$lastname' ";
		$data .= ", firstname = '$firstname' ";
		$data .= ", middlename = '$middlename' ";
		$data .= ", address = '$address' ";
		$data .= ", contact_no = '$contact_no' ";
		$data .= ", email = '$email' ";
		$data .= ", tax_id = '$tax_id' ";
		$data .= ", borrower_type_id = '$borrower_type_id' ";

		if (empty($id)) {
			$save = $this->db->query("INSERT INTO borrowers set " . $data);
		} else {
			$save = $this->db->query("UPDATE borrowers set " . $data . " where id=" . $id);
		}
		if ($save)
			return 1;
	}
	function delete_borrower()
	{
		extract($_POST);
		$delete = $this->db->query("DELETE FROM borrowers where id = " . $id);
		if ($delete)
			return 1;
	}
	function save_loan()
	{
		extract($_POST);
		$data = " borrower_id = $borrower_id ";
		$data .= " , loan_type_id = '$loan_type_id' ";
		$data .= " , plan_id = '$plan_id' ";
		$data .= " , amount = '$amount' ";
		$data .= " , purpose = '$purpose' ";
		if (isset($status)) {
			$data .= " , status = '$status' ";
			if ($status == 2) {
				$plan = $this->db->query("SELECT * FROM loan_plan where id = $plan_id ")->fetch_array();
				for ($i = 1; $i <= $plan['months']; $i++) {
					$date = date("Y-m-d", strtotime(date("Y-m-d") . " +" . $i . " months"));
					$chk = $this->db->query("SELECT * FROM loan_schedules where loan_id = $id and date(date_due) ='$date'  ");
					if ($chk->num_rows > 0) {
						$ls_id = $chk->fetch_array()['id'];
						$this->db->query("UPDATE loan_schedules set loan_id = $id, date_due ='$date' where id = $ls_id ");
					} else {
						$this->db->query("INSERT INTO loan_schedules set loan_id = $id, date_due ='$date' ");
						$ls_id = $this->db->insert_id;
					}
					$sid[] = $ls_id;
				}
				$sid = implode(",", $sid);
				$this->db->query("DELETE FROM loan_schedules where loan_id = $id and id not in ($sid) ");
				$data .= " , date_released = '" . date("Y-m-d H:i") . "' ";

			} else {
				$chk = $this->db->query("SELECT * FROM loan_schedules where loan_id = $id")->num_rows;
				if ($chk > 0) {
					$thi->db->query("DELETE FROM loan_schedules where loan_id = $id ");
				}

			}
		}
		if (empty($id)) {
			$ref_no = mt_rand(1, 99999999);
			$i = 1;

			while ($i == 1) {
				$check = $this->db->query("SELECT * FROM loan_list where ref_no ='$ref_no' ")->num_rows;
				if ($check > 0) {
					$ref_no = mt_rand(1, 99999999);
				} else {
					$i = 0;
				}
			}
			$data .= " , ref_no = '$ref_no' ";
		}
		if (empty($id))
			$save = $this->db->query("INSERT INTO loan_list set " . $data);
		else
			$save = $this->db->query("UPDATE loan_list set " . $data . " where id=" . $id);
		if ($save)
			return 1;
	}
	function delete_loan()
	{
		extract($_POST);
		$delete = $this->db->query("DELETE FROM loan_list where id = " . $id);
		if ($delete)
			return 1;
	}
	function save_payment()
	{
		extract($_POST);
		$data = " loan_id = $loan_id ";
		$data .= " , payee = '$payee' ";
		$data .= " , amount = '$amount' ";
		$data .= " , penalty_amount = '$penalty_amount' ";
		$data .= " , overdue = '$overdue' ";
		if (empty($id)) {
			$save = $this->db->query("INSERT INTO payments set " . $data);
		} else {
			$save = $this->db->query("UPDATE payments set " . $data . " where id = " . $id);

		}
		if ($save)
			return 1;

	}
	function delete_payment()
	{
		extract($_POST);
		$delete = $this->db->query("DELETE FROM payments where id = " . $id);
		if ($delete)
			return 1;
	}


	function sendMessage($phoneNumber, $messageType)
	{
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


}
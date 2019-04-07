<?php
require_once(dirname(__DIR__) . '/vendor/formvalidator.php');

class Auth {
    public $errorMessage;
    private $tableName;
    private $databaseReader;
    private $databaseWriter;

    public function __construct($databaseReader, $databaseWriter) {
        $this->tableName = 'user';
        $this->databaseReader = $databaseReader;
        $this->databaseWriter = $databaseWriter;
    }

    public function registerUser() {
        if (!(isset($_POST['submitted'])) || !($this->validateFormSubmission())) {
            return false;
        }

        $formInfo = $this->collectFormSubmission();
        
        if (!$this->saveToDatabase($formInfo)) {
            return false;
        }

        return true;
    }

    public function loginUser() {
        if (empty($_POST['username'])) {
            $this->handleError("Username is empty!");
            return false;
        }
        
        if (empty($_POST['password'])) {
            $this->handleError("Password is empty!");
            return false;
        }
        
        $username = trim($_POST['username']);
        $password = trim($_POST['password']);
        
        if (!isset($_SESSION)) { 
            session_start(); 
        }

        if (!$this->checkLoginInDB($username, $password)) {
            return false;
        }
        
        $_SESSION[$this->getLoginSessionVar()] = $username;
        
        return true;
    }

    private function checkLoginInDB($username, $password) {
        $username = $this->sanitizeForSQL($username);
        $encryptedPassword = md5($password);
        $query = "select full_name, email from " . $this->tablename . " where username='" . $username . "' and password='" . $encryptedPassword . "'";
        $query = "select username from " . $this->tableName . " where " . $fieldName . "= ?";

        $result = $this->databaseReader->select($query,$this->connection);
        
        if(!$result || mysql_num_rows($result) <= 0)
        {
            $this->HandleError("Error logging in. The username or password does not match");
            return false;
        }
        
        $row = mysql_fetch_assoc($result);
        
        
        $_SESSION['name_of_user']  = $row['name'];
        $_SESSION['email_of_user'] = $row['email'];
        
        return true;
    }

    private function collectFormSubmission() {
        $formInfo = array();

        $formInfo['name'] = $this->sanitize($_POST['name']);
        $formInfo['email'] = $this->sanitize($_POST['email']);
        $formInfo['username'] = $this->sanitize($_POST['username']);
        $formInfo['password'] = $this->sanitize($_POST['password']);
        $formInfo['house-code'] = $this->sanitize($_POST['house-code']);

        return $formInfo;
    }

    private function saveToDatabase($formInfo) {
        if (!$this->isFieldUnique($formInfo, 'email')) {
            $this->handleError("This email is already registered");
            return false;
        }
        
        if (!$this->isFieldUnique($formInfo, 'username')) {
            $this->handleError("This UserName is already used. Please try another username");
            return false;
        }  

        if (!$this->insertIntoDb($formInfo)) {
            $this->handleError("Inserting to Database failed!");
            return false;
        }

        return true;
    }

    private function insertIntoDb($formInfo) {
        $query = 'insert into user set ';
        $query .= 'id = ?, ';
        $query .= 'token = ?, ';
        $query .= 'email = ?, ';
        $query .= 'fullName = ?, ';
        $query .= 'username = ?, ';
        $query .= 'password = ?, ';
        $query .= 'status = ?, ';
        $query .= 'addressId = ?, ';
        $query .= 'houseCode = ? ';

        // $values = array(
        //     NULL,
        //     'sampleToken3',
        //     $this->sanitizeForSQL($formInfo['email']),
        //     $this->sanitizeForSQL($formInfo['name']),
        //     $this->sanitizeForSQL($formInfo['username']),
        //     md5($formInfo['password']),
        //     'standard',
        //     1,
        //     1,
        // ); 
        $values = array(
            NULL,
            'sampletoken3',
            'jamesking@gmail.com',
            'SAMPLE INSERT',
            'sampleuser',
            'samplepass',
            'standard',
            1,
            1
        );


        $this->databaseWriter->testSecurityQuery($query, 0);
        print_r($values);
        $this->databaseWriter->sanitizeQuery($query);
        return $this->databaseWriter->insert($query, $values);
    }

    private function isFieldUnique($formInfo, $fieldName) {
        $fieldValue = $this->sanitizeForSQL($formInfo[$fieldName]);
        $query = "select username from " . $this->tableName . " where " . $fieldName . "= ?";
        $result = $this->databaseReader->select($query, array($fieldName));
        if ($result && my_sql_num_rows($result) > 0) {
            return false;
        }

        return true;
    }

    private function validateFormSubmission() {    
        $validator = new FormValidator();
        $validator->addValidation("name", "req", "Please fill in Name");
        $validator->addValidation("email", "email", "The input for Email should be a valid email value");
        $validator->addValidation("email", "req", "Please fill in Email");
        $validator->addValidation("username", "req", "Please fill in UserName");
        $validator->addValidation("password", "req", "Please fill in Password");
        
        if(!$validator->ValidateForm()) {
            $error = '';
            $errorHash = $validator->GetErrors();
            foreach ($errorHash as $inputName => $inputError) {
                $error .= $inputName . ':' . $inputError . "\n";
            }

            $this->handleError($error);
            return false;
        }
        
        return true;
    }

    private function handleError($error) {
        $this->errorMessage .= $error . "\r\n";
    }

    public function redirect($url) {
        header("Location: $url");
        exit;
    }

    private function sanitizeForSQL($string) {
        if (function_exists("mysql_real_escape_string")) {
            $sanitizedString = mysql_real_escape_string($string);
        } else {
            $sanitizedString = addslashes($string);
        }

        return $sanitizedString;
    }

    /*
    Sanitize() function removes any potential threat from the
    data submitted. Prevents email injections or any other hacker attempts.
    if $removeNl is true, newline chracters are removed from the input.
    */
    private function sanitize($str, $removeNl = true) {
        $str = $this->stripSlashes($str);

        if ($removeNl) {
            $injections = array('/(\n+)/i',
                '/(\r+)/i',
                '/(\t+)/i',
                '/(%0A+)/i',
                '/(%0D+)/i',
                '/(%08+)/i',
                '/(%09+)/i'
                );
            $str = preg_replace($injections, '', $str);
        }
        return $str;
    }    

    private function stripSlashes($str) {
        if (get_magic_quotes_gpc()) {
            $str = stripslashes($str);
        }
        return $str;
    } 
}
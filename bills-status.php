
<?php
require_once('templates/top.php');
require_once('lib/config.php');
session_start();

// for local test
// $user=array("id"=>1,"token"=>"token","email"=>"email@gmail.com","fullName"=>"hello","status"=>"admin");
// $users=array($user);
// $_SESSION['userInfo'] = $users;

if (!isset($_SESSION['userInfo'])) {
    $auth->redirect('index.php');
}

$userId=$_SESSION['userInfo'][0]['id'];

if(isset($_GET['billId'])){
    $billId=$_GET['billId'];
    confirmBill($billId, $thisDatabaseReader, $thisDatabaseWriter);
}

function getUserBills($userId, $thisDatabaseReader){

    $data = array($userId);
    $query = 'SELECT * ';
    $query .= 'FROM USER_BILL_RLT ';
    $query .= 'WHERE userId = ? ';
    $query = $thisDatabaseReader->sanitizeQuery($query);
    $records = $thisDatabaseReader->select($query, $data);

    $bills=array();
    foreach ($records as $record) {
        $bill=getBillById($record['billId'], $thisDatabaseReader);
        $address=getAddressById($bill['addressId'], $thisDatabaseReader);
        $addInfo=$address['houseNumber'].' '.$address['street'].' '.$address['unitNumber'].' '.$address['zip'].' '.$address['city'].' '.$address['state'];
        $bill['status']=$record['status'];
        $bill['addInfo']=$addInfo;
        array_push($bills,$bill);
    }
    return $bills;
}

function getBillById($billId, $thisDatabaseReader){
    $data = array($billId);
    $query = 'SELECT * ';
    $query .= 'FROM bill ';
    $query .= 'WHERE id = ? ';
    $query = $thisDatabaseReader->sanitizeQuery($query);
    $records = $thisDatabaseReader->select($query, $data);
    return $records[0];
}


function getAddressById($addressId, $thisDatabaseReader){
    $data = array($addressId);
    $query = 'SELECT * ';
    $query .= 'FROM address ';
    $query .= 'WHERE id = ? ';
    $query = $thisDatabaseReader->sanitizeQuery($query);
    $records = $thisDatabaseReader->select($query, $data);
    return $records[0];
}

function confirmBill($billId, $thisDatabaseReader, $thisDatabaseWriter){
    $data=array($billId);
    $query=' DELETE FROM USER_BILL_RLT WHERE billId= ? ';
    $query = $thisDatabaseReader->sanitizeQuery($query);
    $records = $thisDatabaseWriter->delete($query, $data);
}

?>

<div id="household-buttons" class="row text-center">
    <table>
        <tr>
            <th>Bill Type</th>
            <th>Information</th>
            <th>Due Date</th>
            <th>Additional Info</th>
            <th>File</th>
            <th>Status</th>
            <?php if ($_SESSION['userInfo'][0]['status']=='admin'){
                    print'<th>Confirm</th>';
                }
            ?>
        </tr>
        <tr>
            <td></td>
        </tr>
        <?php
            $bills=getUserBills($userId, $thisDatabaseReader);
            foreach ($bills as $bill) {
                print '
                <tr>
                    <td>'.$bill['type'].'</td>
                    <td>'.$bill['amount'].'</td>
                    <td>'.$bill['dueDate'].'</td>
                    <td>'.$bill['addInfo'].'</td>
                    <td>'.$bill['fileName'].'</td>
                    <td>'.$bill['status'].'</td>
                </tr>';

                if ($_SESSION['userInfo'][0]['status']=='admin'){
                    print'<td><a href="bills.php?billId='.$bill['id'].'"></a></td>';
                }
            }
        ?>
    </table>
</div>


<?php
require_once('templates/footer.php');
?>

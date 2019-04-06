<?php
require_once(__DIR__ . '/templates/top.php');

function getAddressID($thisDatabaseReader, $currentToken)
{

    $data = array($currentToken); //This is where we need to retrieve user token from current session!!

    $query = 'SELECT addressId ';
    $query .= 'FROM user ';
    $query .= 'WHERE token = ? ';
    //$records = $thisDatabaseReader->testSecurityQuery($query, 1, 0);


    if ($thisDatabaseReader->querySecurityOk($query, 1, 0)) {
        $query = $thisDatabaseReader->sanitizeQuery($query);
        $records = $thisDatabaseReader->select($query, $data);
    }

    if (DEBUG) {
        print '<p>Contents of the array<pre>';
        print_r($records);
        print '</pre></p>';
    }

    return $records;
}

function getBills($thisDatabaseReader, $currentAddressId)
{
    $data = array($currentAddressId);

    $query = 'SELECT * ';
    $query .= 'FROM bill ';
    $query .= 'INNER JOIN address ';
    $query .= 'WHERE bill.addressId = address.id ';
    $query .= 'AND bill.addressId = ? ';

//    $records = $thisDatabaseReader->testSecurityQuery($query, 1, 1);


    if ($thisDatabaseReader->querySecurityOk($query, 1, 1)) {
        $query = $thisDatabaseReader->sanitizeQuery($query);
        $records = $thisDatabaseReader->select($query, $data);

    }

    if (DEBUG) {
        print '<p>Contents of the array<pre>';
        print_r($records);
        print '</pre></p>';
    }

    return $records;
}

?>

<body class="dark-edition">
<div class="wrapper ">
    <div class="sidebar" data-color="purple" data-background-color="black" data-image="../assets/img/sidebar-2.jpg">
        <!--
          Tip 1: You can change the color of the sidebar using: data-color="purple | azure | green | orange | danger"

          Tip 2: you can also add an image using data-image tag
      -->
        <div class="logo">
            <a href="#" class="simple-text logo-normal">
                Bill Buddy
            </a>
        </div>
        <div class="sidebar-wrapper">
            <ul class="nav">
                <li class="nav-item active  ">
                    <a class="nav-link" href="examples/dashboard.html">
                        <i class="material-icons">dashboard</i>
                        <p>Dashboard</p>
                    </a>
                </li>
            </ul>
        </div>
    </div>
    <div class="main-panel">
        <!-- Navbar -->
        <nav class="navbar navbar-expand-lg navbar-transparent navbar-absolute fixed-top " id="navigation-example">
            <div class="container-fluid">
                <div class="navbar-wrapper">
                    <a class="navbar-brand" href="javascript:void(0)">Dashboard</a>
                </div>
                <button class="navbar-toggler" type="button" data-toggle="collapse" aria-controls="navigation-index"
                        aria-expanded="false" aria-label="Toggle navigation" data-target="#navigation-example">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="navbar-toggler-icon icon-bar"></span>
                    <span class="navbar-toggler-icon icon-bar"></span>
                    <span class="navbar-toggler-icon icon-bar"></span>
                </button>
            </div>
        </nav>
        <!-- End Navbar -->
        <div class="content">
            <div class="container-fluid">

                <!--    BILL DISPLAY ROW    -->
                <div class="row">

                    <?php
                    $currentToken = 'sampletoken1'; //JUST FOR TESTING
                    $records = getAddressID($thisDatabaseReader, $currentToken);
                    $currentAddressId = '';
                    if (is_array($records)) {
                        foreach ($records as $record) {
                            $currentAddressId = $record['addressId'];
                        }
                    }

                    $records = getBills($thisDatabaseReader, $currentAddressId);
                    if (is_array($records)) {
                        foreach ($records as $record) {
                            print '
                    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
                        <div class="card card-stats">
                            <div class="card-header card-header-success card-header-icon">
                                <div class="card-icon">
                                    <i class="material-icons">store</i>
                                </div>
                                <p class="card-category">'. $record['type'] .'</p>
                                <h3 class="card-title">'. $record['amount'] .'</h3>
                            </div>
                            <div class="card-footer">
                                <div class="stats">
                                    <i class="material-icons">date_range</i> Due:
                                    '. $record['dueDate'] .'
                                </div>
                            </div>
                        </div>
                    </div>';
                        }
                    }


                    ?>


                </div>

            </div>
        </div>
        <footer class="footer">
            <div class="container-fluid">
                <nav class="float-left">
                    <ul>
                        <li>
                            <a href="#">
                                LINK
                            </a>
                        </li>
                        <li>
                            <a href="#">
                                LINK
                            </a>
                        </li>
                        <li>
                            <a href="#">
                                LINK
                            </a>
                        </li>
                        <li>
                            <a href="#">
                                LINK
                            </a>
                        </li>
                    </ul>
                </nav>
                <div class="copyright float-right" id="date">

                    Bill Buddy Inc.
                </div>
            </div>
        </footer>
        <script>
            const x = new Date().getFullYear();
            let date = document.getElementById('date');
            date.innerHTML = '&copy; ' + x + date.innerHTML;
        </script>
    </div>
</div>
<!--   Core JS Files   -->
<script src="assets/js/core/jquery.min.js"></script>
<script src="assets/js/core/popper.min.js"></script>
<script src="assets/js/core/bootstrap-material-design.min.js"></script>
<script src="https://unpkg.com/default-passive-events"></script>
<script src="assets/js/plugins/perfect-scrollbar.jquery.min.js"></script>
<!-- Place this tag in your head or just before your close body tag. -->
<script async defer src="https://buttons.github.io/buttons.js"></script>
<!--  Google Maps Plugin    -->
<script src="https://maps.googleapis.com/maps/api/js?key=YOUR_KEY_HERE"></script>
<!-- Chartist JS -->
<script src="assets/js/plugins/chartist.min.js"></script>
<!--  Notifications Plugin    -->
<script src="assets/js/plugins/bootstrap-notify.js"></script>
<!-- Control Center for Material Dashboard: parallax effects, scripts for the example pages etc -->
<script src="assets/js/material-dashboard.js?v=2.1.0"></script>
<!-- Material Dashboard DEMO methods, don't include it in your project! -->
<script src="assets/demo/demo.js"></script>
<script>

    $(document).ready(function () {
        // Javascript method's body can be found in assets/js/demos.js
        md.initDashboardPageCharts();
    });

</script>
</body>

</html>
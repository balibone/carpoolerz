<?php
session_start();

$username = $_SESSION['username'];
$password = $_SESSION['password'];

$dbconn = pg_connect("host=localhost port=5432 dbname=carpoolerz user=postgres password=postgres")
or die('Could not connect: ' . pg_last_error());

$query = /** @lang text */
"SELECT * FROM systemuser s WHERE '$username' = s.username AND '$password' = s.password AND s.is_admin = 'TRUE'";

$result = pg_query($dbconn, $query);

if (pg_num_rows($result) == 0) {
    header("Location: /carpoolerz/login.php");
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php include '../header.shtml'; ?>
    <?php include 'admin-navbar.shtml'; ?>
</head>

<body>
    <div class=container>
        <h1>Bids</h1>
    </div>
    <div class=container>
        <br>
        <!-- Display all bids -->
        <h3>All Bids</h3>
        <table class="table table-striped table-hover custom-table">
            <thead class="thead-inverse">
                <tr>
                    <th>Relevant Ride ID</th>
                    <th>Amount</th>
                    <th>Passenger</th>
                    <th>Driver</th>
                </tr>
            </thead>
            <tbody>
                <!-- Pull relevant driver by jumping through bids -> ride -> created rides -->
                <?php
                $query = '
                SELECT b.ride_id, b.amount, b.passenger, cr.driver
                FROM bid b, ride r, created_rides cr 
                WHERE b.ride_id = r.ride_id AND r.ride_id = cr.ride_id
                ';
                $result = pg_query($query);
                while ($line = pg_fetch_array($result, null, PGSQL_ASSOC)) {
                    echo "\t<tr>\n";
                    foreach ($line as $col_value) {
                        echo "\t\t<td>$col_value</td>\n";
                    }
                    echo "\t</tr>\n";
                }
                ?>
            </tbody>
        </table>
    </div>
</body>

</html>


<?php
    // Check user login status
    session_start();

    $username = $_SESSION['username'];
    $password = $_SESSION['password'];

    $dbconn = pg_connect("host=localhost port=5432 dbname=carpoolerz user=postgres password=postgres")
    or die('Could not connect: ' . pg_last_error());

    $query = /** @lang text */
        "SELECT * FROM systemuser WHERE username = '$username' AND password = '$password' AND licensenum IS NOT NULL";

    $result = pg_query($dbconn, $query);

    if (pg_num_rows($result) == 0) {
        header("Location: ./driver-car-error.php");
    }

    $getCarDetailsQuery = /** @lang text */
        "SELECT * FROM owns_car WHERE driver = '$username'";

    $carResult = pg_query($dbconn, $getCarDetailsQuery);

    $number_plate = '';
    $model = '';
    $make = '';
    $car_created = false;

    if (pg_num_rows($carResult) > 0) {
        $row = pg_fetch_row($carResult);

        $numplate = $row[1];
        $model = $row[2];
        $brand = $row[3];
        $car_created = true;
    }

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php include '../header.shtml'; ?>
</head>
    <body>
        <?php include 'navbar-user.shtml'; ?>
        <div class=container>
            <div class="container-fluid">
                <h1 class="text-center">DRIVERS: Update Your Car Details</h1>
                <form role="form" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" name="login-form">
                    <div class="form-group">
                        <label for="p_username">Driver Username: </label>
                        <h1><?php echo $username?></h1>
                    </div>

                    <div class="form-group">
                        <label for="p_numplate">Car Number Plate: </label>
                        <input type="text" name="p_numplate" class="form-control" id="car_plate" value="<?php echo $numplate?>" placeholder="Enter Car Plate Number"/>
                    </div>

                    <div class="form-group">
                        <label for="p_brand">Car Brand (OPTIONAL): </label>
                        <input type="text" name="p_brand" class="form-control" id="car_brand" value="<?php echo $brand?>" placeholder="Enter Car Brand"/>
                    </div>

                    <div class="form-group">
                        <label for="p_model">Car Model (OPTIONAL): </label>
                        <input type="text" name="p_model" class="form-control" id="car_model" value="<?php echo $model?>" placeholder="Enter Car Model"/>
                    </div>

                    <button type="submit" name="updateCarTrigger" class="form-control btn btn-danger">UPDATE CAR DETAILS</button>
                </form>
            </div>
        </div>
        <?php include '../footer.shtml'; ?>
    </body>
    <?php
        if (isset($_POST['updateCarTrigger'])) {
            $numplate_updated = $_POST['p_numplate'];
            $brand_updated = $_POST['p_brand'];
            $model_updated = $_POST['p_model'];
            $action_query = "";

            echo "<h1>$numplate_updated<h1/><br/>";
            echo "<h1>$brand_updated<h1/><br/>";
            echo "<h1>$model_updated<h1/><br/>";

//            if ($car_created) {
//                echo "<h2>Car Created loh<h2/>";
//            } else {
//                echo "<h2>Car Not Yet Created loh<h2/>";
//            }

            if ($car_created) {
                $action_query = /** @lang text */
                    "UPDATE owns_car SET numplate = '$numplate_updated', model = '$model_updated', brand = '$brand_updated'
                            WHERE driver = '$username'";
            } else {
                $action_query = /** @lang text */
                    "INSERT INTO owns_car (driver, numplate, model, brand) VALUES ('$username', '$numplate_updated', '$model_updated', '$brand_updated')";
                $car_created = true;
            }

            $result = pg_query($dbconn, $action_query);

            //Cleanup by brand values
            $cleanup_brand_query = /** @lang text */
                "UPDATE owns_car SET brand = DEFAULT WHERE brand = ''";
            $cleanup_brand = pg_query($dbconn, $cleanup_brand_query);

            //Cleanup by model values
            $cleanup_model_query = /** @lang text */
                "UPDATE owns_car SET model = DEFAULT WHERE model = ''";
            $cleanup_model = pg_query($dbconn, $cleanup_model_query);

            //Delete if number plate is not there
            if ($numplate_updated == '') {
                $delete_if_no_numplate_query = /** @lang text */
                    "DELETE FROM owns_car WHERE driver = '$username'";
                $delete_if_no_numplate = pg_query($dbconn, $delete_if_no_numplate_query);
            }

            //Refresh page
            header("Refresh:0");
        }
    ?>
</html>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <link rel="stylesheet" href="bootstrap-4.0.0/dist/css/bootstrap.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.16/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/select/1.2.5/css/select.dataTables.min.css">

    <link rel="stylesheet" href="css/main.css">
    <?php

    ob_start();
    session_start();

    require 'app/DB.php';
    require 'app/Util.php';
    require 'app/dao/CustomerDAO.php';
    require 'app/dao/ReservationDAO.php';
    require 'app/dao/BookingDetailDAO.php';
    require 'app/models/Customer.php';
    require 'app/models/Reservation.php';
    require 'app/models/BookingDetail.php';
    require 'app/handlers/CustomerHandler.php';
    require 'app/handlers/ReservationHandler.php';
    require 'app/handlers/BookingDetailHandler.php';

    $username = null;
    $isSessionExists = $isAdmin = false;
    $pendingReservation = $confirmedReservation = $totalCustomers = $totalReservations = null;
    if (isset($_SESSION["username"]))
    {
        $username = $_SESSION["username"];
        $isSessionExists = true;

        $cHandler = new CustomerHandler();
        $cHandler = $cHandler->getCustomerObj($_SESSION["customerEmail"]);

        $cAdmin = new Customer();
        $cAdmin->setEmail($cHandler->getEmail());
        $isAdmin = $cAdmin->isAdminSignedIn();

        // display all reservations
        $bdHandler = new BookingDetailHandler();
        $allBookings = $cCommon = null;
        $allBookings = $bdHandler->getAllBookings();
        $cCommon = new CustomerHandler();

        // reservation stats
        $pendingReservation = $bdHandler->getPending();
        $confirmedReservation = $bdHandler->getConfirmed();
        $totalCustomers = $cCommon->totalCustomersCount();
        $rHandler = new ReservationHandler();
        $totalReservations = $rHandler->totalReservationsCount();
    }

    print_r($_SESSION);

    ?>

    <title>Manage Booking</title>
</head>
<body>

<header>
    <div class="bg-dark collapse" id="navbarHeader" style="">
        <div class="container">
            <div class="row">
                <div class="col-sm-8 col-md-7 py-4">
                    <h4 class="text-white">About</h4>
                    <p class="text-muted">Add some information about hotel booking.</p>
                </div>
                <div class="col-sm-4 offset-md-1 py-4 text-right">
                    <!-- User full name or email if logged in -->
                    <?php if ($isSessionExists) { ?>
                    <h4 class="text-white"><?php echo $username; ?></h4>
                    <ul class="list-unstyled">
                        <li><a href="#" id="sign-out-link" class="text-white">Sign out<i class="fas fa-sign-out-alt ml-2"></i></a></li>
                    </ul>
                    <?php } else { ?>
                    <h4>
                        <a class="text-white" href="sign-in.html">Sign in</a> <span class="text-white">or</span>
                        <a href="register.html" class="text-white">Register </a>
                    </h4>
                    <p class="text-muted">Log in so you can take advantage with our hotel room prices.</p>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
    <div class="navbar navbar-dark bg-dark box-shadow">
        <div class="container d-flex justify-content-between">
            <a href="#" class="navbar-brand d-flex align-items-center">
                <i class="fas fa-h-square mr-2"></i>
                <strong>Hotel Booking</strong>
            </a>
            <button class="navbar-toggler collapsed" type="button" data-toggle="collapse" data-target="#navbarHeader" aria-controls="navbarHeader" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
        </div>
    </div>
</header>

<main role="main">

    <?php if ($isSessionExists && $isAdmin) { ?>
    <div class="container my-3">
        <div class="row">
            <div class="col-xl-3 col-sm-6 mb-3">
                <div class="card text-white bg-primary o-hidden h-100">
                    <div class="card-body">
                        <div class="card-body-icon">
                            <i class="fas fa-address-book"></i>
                        </div>
                        <div class="mr-5"><?php echo $totalReservations; ?> Reservations</div>
                    </div>
                    <a class="card-footer text-white clearfix small z-1" href="#reservation">
                        <span class="float-left">View Details</span>
                        <span class="float-right"><i class="fa fa-angle-right"></i></span>
                    </a>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6 mb-3">
                <div class="card text-white bg-warning o-hidden h-100">
                    <div class="card-body">
                        <div class="card-body-icon">
                            <i class="fas fa-users ml-2"></i>
                        </div>
                        <div class="mr-5"><?php echo $totalCustomers; ?> Customers</div>
                    </div>
                    <a class="card-footer text-white clearfix small z-1" href="#customers">
                        <span class="float-left">View Details</span>
                        <span class="float-right"><i class="fa fa-angle-right"></i></span>
                    </a>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6 mb-3">
                <div class="card text-white bg-success o-hidden h-100">
                    <div class="card-body">
                        <div class="card-body-icon">
                            <i class="fas fa-check"></i>
                        </div>
                        <div class="mr-4"><?php echo $confirmedReservation; ?> Confirmed Reservations</div>
                    </div>
                    <div class="card-footer text-white clearfix small z-1"></div>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6 mb-3">
                <div class="card text-white bg-danger o-hidden h-100">
                    <div class="card-body">
                        <div class="card-body-icon">
                            <i class="fa fa-fw fa-support"></i>
                        </div>
                        <div class="mr-5"><?php echo $pendingReservation; ?> Pending Reservations</div>
                    </div>
                    <div class="card-footer text-white clearfix small z-1"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="container" id="tableContainer">
        <ul class="nav nav-tabs" id="adminTab" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="reservation-tab" data-toggle="tab" href="#reservation" role="tab"
                   aria-controls="reservation" aria-selected="true">Reservation</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="customers-tab" data-toggle="tab" href="#customers" role="tab"
                   aria-controls="customers" aria-selected="false">Customers</a>
            </li>
        </ul>
        <div class="tab-content" id="adminTabContent">
            <div class="tab-pane fade show active" id="reservation" role="tabpanel" aria-labelledby="reservation-tab">
                <table id="reservationDataTable" class="table table-striped table-bordered" cellspacing="0" width="100%">
                    <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th class="text-hide p-0" data-bookId="12">12</th>
                        <th scope="col">Email</th>
                        <th scope="col">Start</th>
                        <th scope="col">End</th>
                        <th scope="col">Room type</th>
                        <th scope="col">Timestamp</th>
                        <th scope="col">Status</th>
                        <th scope="col">Notes</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($allBookings)) { ?>
                        <?php   foreach ($allBookings as $k => $v) { ?>
                            <tr>
                                <th scope="row"><?php echo ($k + 1); ?></th>
                                <td class="text-hide p-0" data-id="<?php echo $v->getBid(); ?>">
                                    <?php echo $v->getBid(); ?>
                                </td>
                                <td><?php echo $cCommon->getCustomerObjByCid($v->getCid())->getEmail(); ?></td>
                                <td><?php echo $v->getStart(); ?></td>
                                <td><?php echo $v->getEnd(); ?></td>
                                <td><?php echo $v->getType(); ?></td>
                                <td><?php echo $v->getTimestamp(); ?></td>
                                <td><?php echo $v->getStatus(); ?></td>
                                <td><?php echo $v->getNotes(); ?></td>
                            </tr>
                        <?php } ?>
                    <?php } ?>
                    </tbody>
                </table>
                <div class="my-3">
                    <label class="text-secondary font-weight-bold">With selected:</label>
                    <button type="button" id="confirm-booking" class="btn btn-outline-success btn-sm">Confirm</button>
                    <button type="button" id="cancel-booking" class="btn btn-outline-danger btn-sm">Cancel</button>
                </div>
            </div>
            <div class="tab-pane fade" id="customers" role="tabpanel" aria-labelledby="customers-tab">
                <table class="table table-bordered">
                    <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">First</th>
                        <th scope="col">Last</th>
                        <th scope="col">Handle</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <th scope="row">1</th>
                        <td>Mark</td>
                        <td>Otto</td>
                        <td>@mdo</td>
                    </tr>
                    <tr>
                        <th scope="row">2</th>
                        <td>Jacob</td>
                        <td>Thornton</td>
                        <td>@fat</td>
                    </tr>
                    <tr>
                        <th scope="row">3</th>
                        <td colspan="2">Larry the Bird</td>
                        <td>@twitter</td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <div class="modal fade" id="confirmModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm selected reservation(s)</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to proceed with this action?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id="confirmTrue">Yes</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">No</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="cancelModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Cancel selected reservation(s)</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to proceed with this action?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id="cancelTrue">Yes</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">No</button>
                </div>
            </div>
        </div>
    </div>

    <?php } ?>

</main>

<footer class="container">
    <p>&copy; Company 2017-2018</p>
</footer>

<!-- jQuery first, then Popper.js, then Bootstrap JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"
        integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q"
        crossorigin="anonymous"></script>
<script defer src="https://use.fontawesome.com/releases/v5.0.10/js/all.js"
        integrity="sha384-slN8GvtUJGnv6ca26v8EzVaR9DC58QEwsIk9q1QXdCU8Yu8ck/tL/5szYlBbqmS+"
        crossorigin="anonymous"></script>
<script src="bootstrap-4.0.0/dist/js/bootstrap.js"></script>
<script src="https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/select/1.2.5/js/dataTables.select.min.js"></script>
<script src="js/util.js"></script>
<script src="js/templates.js"></script>
<script src="js/form-submission.js"></script>
<script src="js/admin.js"></script>
</body>
</html>
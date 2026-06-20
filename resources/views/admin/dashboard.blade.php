<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sanatan Admin Panel</title>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">

    <!-- Bootstrap CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: #f2f5f8;
            margin: 0;
        }
        .sidebar {
            width: 250px;
            background: #222;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            padding-top: 60px;
            color: #fff;
        }
        .sidebar a {
            display: block;
            padding: 12px 20px;
            color: #ddd;
            text-decoration: none;
            font-size: 15px;
        }
        .sidebar a:hover {
            background: #444;
            color: #fff;
        }
        .topbar {
            height: 60px;
            background: #fff;
            border-bottom: 1px solid #ddd;
            padding: 0 20px;
            margin-left: 250px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .content {
            margin-left: 250px;
            padding: 25px;
        }
        .profile-box {
            cursor: pointer;
        }
        .stat-card {
            transition: 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-4px);
        }
    </style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
    <h4 class="text-center mb-4" style="color: #fff;">Sanatan Admin</h4>
    <a href="#">Dashboard</a>
   <a href="{{ route('admin.users') }}">Users</a>
   <a href="{{ route('admin.media') }}">Media Posts</a>
    <a href="#">Videos</a>
    <a href="#">Events</a>
    <a href="#">Settings</a>
</div>

<!-- Top Navbar -->
<div class="topbar">
    <h5 class="m-0">Dashboard</h5>
    <div class="dropdown">
        <span class="profile-box dropdown-toggle" data-bs-toggle="dropdown">
            👤 Admin Profile
        </span>
        <ul class="dropdown-menu dropdown-menu-end">
            <li><a class="dropdown-item" href="#">My Profile</a></li>
            <li><a class="dropdown-item" href="#">Change Password</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item text-danger" href="#">Logout</a></li>
        </ul>
    </div>
</div>

<!-- Main Content -->
<div class="content">
    <h2>Welcome to Sanatan Admin Panel</h2>
    <p>Yaha se aap poora app manage kar sakte ho.</p>

    <div class="row">
        <div class="col-md-4">
            <div class="p-4 bg-white rounded shadow-sm mb-3 stat-card">
                <h4>Total Users</h4>
                <p>1280</p>
            </div>
        </div>

        <div class="col-md-4">
            <div class="p-4 bg-white rounded shadow-sm mb-3 stat-card">
                <h4>Total Videos</h4>
                <p>540</p>
            </div>
        </div>

        <div class="col-md-4">
            <div class="p-4 bg-white rounded shadow-sm mb-3 stat-card">
                <h4>Total Posts</h4>
                <p>320</p>
            </div>
        </div>
    </div>

    <!-- Graph Section -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="p-4 bg-white rounded shadow-sm">
                <h4>User Data Graph</h4>
                <canvas id="userChart" height="90"></canvas>
            </div>
        </div>
    </div>

</div>

<!-- ChartJS -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
var ctx = document.getElementById('userChart').getContext('2d');
var userChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
        datasets: [{
            label: 'New Users Join Per Month',
            data: [120, 190, 300, 500, 250, 400],
            borderWidth: 3,
            borderColor: '#ff6b00',
            backgroundColor: 'rgba(255, 107, 0, 0.2)',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { position: 'top' }
        }
    }
});
</script>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>

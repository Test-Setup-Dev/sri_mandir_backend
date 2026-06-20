<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users - Sanatan Admin</title>

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
        .sidebar a:hover,
        .sidebar a.active {
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
    </style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
    <h4 class="text-center mb-4">Sanatan Admin</h4>
    <a href="{{ route('admin.dashboard') }}">Dashboard</a>
    <a href="{{ route('admin.users') }}">Users</a>
    <a href="{{ route('admin.media') }}">Media Posts</a>
    <a href="/admin/videos">Videos</a>
    <a href="/admin/events">Events</a>
    <a href="/admin/settings">Settings</a>
</div>

<!-- Top Navbar -->
<div class="topbar">
    <h5 class="m-0">Users</h5>
    <div class="dropdown">
        <span class="profile-box dropdown-toggle" data-bs-toggle="dropdown" style="cursor:pointer;">
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
    <h3>User Management</h3>
    <p>Yaha se aap users ko manage kar sakte ho.</p>

    <div class="card p-3 shadow-sm rounded bg-white">
        <h5 class="mb-3">Users List</h5>

        <table class="table table-bordered table-striped align-middle">
            <thead class="table-dark">
                <tr>
                    <th style="width: 60px;">ID</th>
                    <th>Name</th>
                    <th>Email / Phone</th>
                    <th style="width: 120px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <!-- Example Static Rows (Baad me DB se aayenge) -->
                <tr>
                    <td>1</td>
                    <td>Rahul Sharma</td>
                    <td>rahul@example.com</td>
                    <td>
                        <a href="#" class="btn btn-sm btn-primary">Edit</a>
                        <a href="#" class="btn btn-sm btn-danger">Delete</a>
                    </td>
                </tr>

                <tr>
                    <td>2</td>
                    <td>Vivek Pal</td>
                    <td>vivek@example.com</td>
                    <td>
                        <a href="#" class="btn btn-sm btn-primary">Edit</a><br>
                        <a href="#" class="btn btn-sm btn-danger">Delete</a>
                    </td>
                </tr>

            </tbody>
        </table>
    </div>

</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>

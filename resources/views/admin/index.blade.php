<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Media Posts - Sanatan Admin</title>

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
    <a href="#">Videos</a>
    <a href="#">Events</a>
    <a href="#">Settings</a>
</div>

<!-- Top Navbar -->
<div class="topbar">
    <h5 class="m-0">Media Posts</h5>
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

<!-- Content -->
<div class="content">
    <h3>All Media Posts</h3>
    <p>Yaha se app ke saare media (video, audio, text) dekh sakte ho.</p>

    <div class="card p-3 shadow-sm bg-white rounded">

        <table class="table table-bordered table-striped align-middle">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>Type</th>
                    <th>Title / Text</th>
                    <th>Preview</th>
                    <th>Created</th>
                </tr>
            </thead>
            <tbody>
                
                <!-- Static Row 1 - Video -->
                <tr>
                    <td>1</td>
                    <td>Video</td>
                    <td>Bhagwan Shiva Bhajan</td>
                    <td>
                        <video width="120" height="80" controls>
                            <source src="https://samplelib.com/lib/preview/mp4/sample-5s.mp4" type="video/mp4">
                        </video>
                    </td>
                    <td>10 Feb 2025</td>
                </tr>

                <!-- Static Row 2 - Audio -->
                <tr>
                    <td>2</td>
                    <td>Audio</td>
                    <td>Hanuman Chalisa</td>
                    <td>
                        <audio controls style="width:150px;">
                            <source src="https://samplelib.com/lib/preview/mp3/sample-3s.mp3" type="audio/mpeg">
                        </audio>
                    </td>
                    <td>09 Feb 2025</td>
                </tr>

                <!-- Static Row 3 - Text -->
                <tr>
                    <td>3</td>
                    <td>Text</td>
                    <td>“ॐ नमः शिवाय” ka jap roj subah karo.</td>
                    <td><span class="badge bg-info">Text Post</span></td>
                    <td>05 Feb 2025</td>
                </tr>

            </tbody>
        </table>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>

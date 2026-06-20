<!DOCTYPE html>
<html>
<head>
    <title>About Us</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; padding: 20px; background: #f7f7f7; }
        .container { max-width: 800px; margin: auto; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h1 { color: #333; margin-bottom: 20px; }
        p { color: #555; }
    </style>
</head>
<body>
    <div class="container">
        @if($about)
            <h1>{{ $about->title }}</h1>
            <p>{!! nl2br(e($about->description)) !!}</p>
        @else
            <h1>About Us</h1>
            <p>Content not available.</p>
        @endif
    </div>
</body>
</html>

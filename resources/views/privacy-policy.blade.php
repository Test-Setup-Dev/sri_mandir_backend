<!DOCTYPE html>
<html>
<head>
    <title>Privacy Policy</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f7f7f7; line-height: 1.6; padding: 20px; }
        .container { max-width: 800px; margin: auto; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h1 { color: #333; margin-bottom: 20px; }
        p { color: #555; margin-bottom: 15px; }
    </style>
</head>
<body>
    <div class="container">
        @if($policy)
            <h1>{{ $policy->title }}</h1>
            <p>{!! nl2br(e($policy->description)) !!}</p>
        @else
            <h1>Privacy Policy</h1>
            <p>Content not available.</p>
        @endif
    </div>
</body>
</html>

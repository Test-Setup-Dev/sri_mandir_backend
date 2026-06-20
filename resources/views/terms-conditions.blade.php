<!DOCTYPE html>
<html>
<head>
    <title>Terms & Conditions</title>
    <style>
        body { font-family: Arial; background: #f7f7f7; padding: 20px; }
        .container { max-width: 800px; margin:auto; background:#fff; padding:20px; border-radius:8px; }
        h1 { color:#333; }
        p { color:#555; line-height:1.6; margin-bottom: 15px; }
    </style>
</head>
<body>
    <div class="container">
        @if($terms)
            <h1>{{ $terms->title }}</h1>
            <p>{!! nl2br(e($terms->description)) !!}</p>
        @else
            <h1>Terms & Conditions</h1>
            <p>Content not available.</p>
        @endif
    </div>
</body>
</html>

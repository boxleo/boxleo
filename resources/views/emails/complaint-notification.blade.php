<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Complaint Notification</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 600px;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin: auto;
        }

        h1 {
            color: #5a0b61;
            border-bottom: 2px solid #5a0b61;
            padding-bottom: 10px;
        }

        p {
            color: #333;
            line-height: 1.6;
        }

        .label {
            font-weight: bold;
            color: #5a0b61;
        }

        .status {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 5px;
            font-weight: bold;
        }

        .status-open {
            background-color: #ffeb3b;
            color: #333;
        }

        .status-closed {
            background-color: #4caf50;
            color: white;
        }

        .priority {
            font-weight: bold;
            color: #d32f2f;
        }

        .attachments,
        .links {
            margin-top: 10px;
        }

        .attachments img {
            max-width: 100%;
            border-radius: 5px;
            margin-top: 10px;
        }

        .links a {
            display: block;
            color: #1e88e5;
            text-decoration: none;
            margin-top: 5px;
        }
    </style>
</head>

<body>

    <div class="container">
        <h1>New Suggestion Received</h1>
        <p>A new suggestion has been registered:</p>

        <p><span class="label">Subject:</span> {{ $complaint->subject ?? 'N/A' }}</p>
        <p><span class="label">Description:</span> {{ $complaint->description ?? 'N/A' }}</p>
        <p><span class="label">Category:</span> {{ $complaint->category ?? 'N/A' }}</p>
        <p><span class="label priority">Priority:</span> {{ $complaint->priority ?? 'N/A' }}</p>

        @php
            $statusClass = isset($complaint->status) && strtolower($complaint->status) === 'open' ? 'status-open' : 'status-closed';
        @endphp
        <p><span class="label">Status:</span>
            <span class="status {{ $statusClass }}">
                {{ $complaint->status ?? 'N/A' }}
            </span>
        </p>

        <!-- Attachments -->
        @php
            $attachments = isset($complaint->attachments) ? (is_array($complaint->attachments) ? $complaint->attachments : json_decode($complaint->attachments, true)) : [];
        @endphp
        @if (!empty($attachments))
            <div class="attachments">
                <p><span class="label">Attachments:</span></p>
                @foreach ($attachments as $attachment)
                    <img src="{{ asset($attachment['path'] ?? '#') }}" alt="{{ $attachment['original_name'] ?? 'Attachment' }}">
                @endforeach
            </div>
        @endif

        <!-- Links -->
        @php
            $links = isset($complaint->links) ? (is_array($complaint->links) ? $complaint->links : json_decode($complaint->links, true)) : [];
        @endphp
        @if (!empty($links))
            <div class="links">
                <p><span class="label">Reference Links:</span></p>
                @foreach ($links as $link)
                    <a href="{{ $link }}" target="_blank">{{ $link }}</a>
                @endforeach
            </div>
        @endif



           <!-- Login Button -->
           <a href="https://hrm.boxleocourier.com" class="login-button" target="_blank">Login to HRM</a>

</div>

    

    </div>

</body>

</html>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HQ Operations Request Form</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 90%;
            margin: 0 auto;
        }
        .logo {
            text-align: center;
            margin-bottom: 20px;
        }
        .logo img {
            max-width: 150px;
        }
        .title {
            text-align: center;
            font-weight: bold;
            text-decoration: underline;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }
        .instructions, .footer {
            margin-top: 20px;
        }
        .signatures {
            margin-top: 40px;
        }
        .signature-section {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
        <img src="{{ public_path('assets/img/logo.png') }}" alt="Company Logo" style="max-width: 150px;">

        </div>
        <div class="title">
            REQUEST FORM
        </div>
        <p>
            Requesting Officer: {{ $requisition->user->firstname }} {{ $requisition->user->lastname }} <br>
            Department: {{ $requisition->user->department->name ?? 'N/A' }} <br>
            Date: {{ $requisition->created_at }}
        </p>
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Description</th>
                    <th>QTY</th>
                    <th>Unit Cost</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($requisition->items as $item)
                <tr>
                    <td>{{ $item->name }}</td>
                    <td>{{ $item->description }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>{{ number_format($item->unit_cost, 2) }}</td>
                    <td>{{ number_format($item->total_cost, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="instructions">
            <p>Special Instructions: {{ $requisition->special_instructions ?? 'N/A' }}</p>
            <p>Comments: {{ $requisition->comment ?? 'N/A' }}</p>
        </div>
        <div class="footer">
            <p>
                Budgeted Expenses: {{ $requisition->budgeted_expenses ? 'YES' : 'NO' }} <br>
                Funds Available: {{ $requisition->funds_available ? 'YES' : 'NO' }}
            </p>
        </div>
        <div class="signatures">
            @foreach ($requisition->requisitionLogs as $log)
            <div class="signature-section">
                {{ $log->user->firstname ?? 'Unknown User' }}: {{ $log->action }} <br>
                Date: {{ $log->created_at }} <br>
            </div>
            @endforeach
        </div>

       <p> Status: {{$requisition->status}}</p>
       <p> Proof Payment: {{$requisition->pop}}</p>

    </div>
</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Requisition Invoice</title>
    <link rel="icon" href="/assets/img/logo.png" type="image/png">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 2px;
            padding: 0;
        }
        .container {
            width: 100%;
            max-width: 900px;
            margin: auto;
            border: 1px solid #ddd;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h2 {
            margin: 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            table-layout: fixed;

        }
        table, th, td {
            border: 1px solid black;
            /* border: none; */
        }
        th, td {
            padding: 8px;
            text-align: left;
            font-size: 12px;

        }
        .footer {
            margin-top: 10px;
            text-align: center;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="{{ public_path('assets/img/logo.png') }}" alt="Company Logo" style="max-width: 150px;">
            <p>Requisition </p>
        </div>
        
        @php
            $grandTotal = 0;
        @endphp

        <table>
            <thead>
                <tr>
                    <!-- <th>#</th> -->
                    <th>Requisition ID</th>
                    <th>Requesting Officer</th>
                    <th>Status</th>
                    <th>Special Instructions</th>
                    <th>Department</th>
                    <th>Item Name</th>
                    <th>Description</th>
                    <th>Quantity</th>
                    <th>Unit Cost</th>
                    <th>Total Cost</th>
                    <th>Payment Code</th>
                </tr>
            </thead>
            <tbody>
                @foreach($requisitions as $requisition)
                    @foreach($requisition['items'] as $index => $item)
                    @php
                        $grandTotal += $item['total_cost'];
                    @endphp
                    <tr>
                        <!-- <td>{{ $index + 1 }}</td> -->
                        <td>{{ $requisition['id'] }}</td>
                        <td>{{ $requisition['user']['firstname'] }} {{ $requisition['user']['lastname'] }}</td>
                        <td>{{ $requisition['status'] }}</td>
                        <td>{{ $requisition['special_instructions'] }}</td>
                        <td>{{ $requisition['user']['department']['name'] }}</td>
                        <td>{{ $item['name'] }}</td>
                        <td>{{ $item['description'] }}</td>
                        <td>{{ $item['quantity'] }}</td>
                        <td>{{ number_format($item['unit_cost'], 2) }}</td>
                        <td>{{ number_format($item['total_cost'], 2) }}</td>
                        <td>{{ $item['payment_code'] ?? 'N/A' }}</td>
                    </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>

        <div class="footer">
            
            <p>Grand Total: {{ number_format($grandTotal, 2) }}</p>
        </div>
    </div>
</body>
</html>
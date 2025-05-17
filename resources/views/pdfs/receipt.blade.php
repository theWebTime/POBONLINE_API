<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Receipt</title>
<style>
    :root {
        --primary: #2c3e50; /* Darker primary color from invoice */
        --secondary: #7f8c8d; /* Secondary grey from invoice */
        --accent: #3498db; /* Accent blue from invoice */
        --light: #f9f9f9; /* Lighter background from invoice */
        --dark: #333; /* Dark text color from invoice */
        --success: #198754;
        --warning: #ffc107;
        --danger: #e74c3c; /* Red color from invoice's discount */
        --gold: #ffca28;
        --bs-border-color: #eee; /* Light border from invoice */
    }

    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        margin: 0; /* Reset body margin */
        padding: 20px; /* Add some body padding */
        color: var(--dark);
        background-color: var(--light);
        line-height: 1.5; /* Line height from invoice */
        font-size: 14px; /* Font size from invoice body */
    }

    .receipt-container {
        max-width: 800px; /* Max width from invoice container */
        margin: 30px auto; /* Adjusted margins */
        background: white;
        border-radius: 8px; /* Slightly softer border radius */
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); /* Box shadow from invoice */
        overflow: hidden;
        border: 1px solid var(--bs-border-color);
    }

    .header { /* Using a more generic 'header' class */
        text-align: center;
        margin-bottom: 30px;
        padding-bottom: 20px;
        border-bottom: 1px solid var(--bs-border-color);
    }

    .header h1 { /* Targeting h1 inside header */
        margin: 0;
        font-size: 28px;
        color: var(--primary);
        font-weight: 600;
        letter-spacing: 1px;
    }

    .header p { /* Targeting p inside header */
        color: var(--secondary);
        margin-top: 5px;
        font-size: 14px;
    }

    .client-info {
        padding: 20px; /* Adjusted padding */
        background-color: white;
        margin-bottom: 20px; /* Added margin */
        border-bottom: 2px solid var(--accent); /* Using accent color as a separator */
    }

    .info-grid {
        display: flex; /* Using flex for better alignment */
        justify-content: space-between;
        gap: 20px;
    }

    .info-section { /* Grouping info items */
        flex: 1;
        min-width: 0;
    }

    .info-item strong {
        display: block;
        font-size: 16px; /* Slightly larger strong text */
        color: var(--primary);
        margin-bottom: 5px;
    }

    .amounts-section {
        padding: 20px;
        background-color: #f8f9fa; /* Keeping a light background */
        border-top: 1px solid var(--bs-border-color);
        border-bottom: 1px solid var(--bs-border-color);
        display: grid; /* Changed back to grid for better control over individual items */
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); /* Distribute items in columns */
        gap: 15px; /* Adjust gap as needed */
        margin-bottom: 20px;
    }

    .amount-item {
        padding: 15px;
        background: white;
        border-radius: 4px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        border-left: 4px solid var(--accent); /* Accent border */
        text-align: left; /* Align text to the left */
    }

    .amount-item strong {
        display: block;
        font-size: 14px;
        color: var(--primary);
        margin-bottom: 8px;
    }

    .final-total-container {
        background-color: var(--primary);
        color: white;
        padding: 15px;
        border-radius: 4px;
        text-align: center;
        margin-bottom: 20px;
    }

    .final-total-container strong {
        font-size: 1.1rem;
        display: block;
        margin-bottom: 5px;
        color: rgba(255, 255, 255, 0.8);
    }

    .final-total {
        font-size: 1.5rem;
        font-weight: 600;
    }

    .payment-slots {
        padding: 20px;
    }

    .payment-slots h3 {
        color: var(--primary);
        margin-top: 0;
        margin-bottom: 15px;
        font-size: 1.6rem;
        font-weight: 600;
        position: relative;
        padding-bottom: 8px;
        border-bottom: 2px solid var(--accent);
        display: inline-block;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
        background-color: white;
        border-radius: 4px;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
        border: 1px solid var(--bs-border-color);
        font-size: 0.9rem;
    }

    th {
        background-color: var(--primary);
        color: white;
        padding: 10px 12px;
        text-align: left;
        font-weight: 500;
        font-size: 1rem;
    }

    td {
        padding: 10px 12px;
        border-bottom: 1px solid var(--bs-border-color);
        font-size: 0.9rem;
    }

    tr:last-child td {
        border-bottom: none;
    }

    .status-paid {
        color: var(--success);
        font-weight: 600;
    }

    .status-pending {
        color: var(--danger);
        font-weight: 600;
    }

    .receipt-footer {
        margin-top: 30px;
        padding-top: 15px;
        border-top: 1px solid var(--bs-border-color);
        text-align: center;
        font-size: 0.8rem;
        color: var(--secondary);
    }

    @media print {
        body {
            background: none;
            padding: 0;
            font-size: 12px;
        }

        .receipt-container {
            box-shadow: none;
            max-width: 100%;
            border: none;
            border-radius: 0;
            margin: 0;
        }
    }
</style>
</head>
<body>
    <div class="receipt-container">
        <div class="receipt-header">
            <h2>PAYMENT RECEIPT</h2>
        </div>

        <div class="client-info">
            <div class="info-grid">
                <div>
                    <strong>CLIENT NAME</strong>
                    {{ $client_name }}
                </div>
                <div>
                    <strong>PHONE NUMBER</strong>
                    {{ $client_phone }}
                </div>
                <div>
                    <strong>ADDRESS</strong>
                    {{ $client_address }}
                </div>
                <div>
                    </div>
            </div>
        </div>

        <div class="amounts-section">
            <div class="amount-item">
                <strong>GRAND TOTAL</strong>
                {{ $grand_total }}
            </div>
            <div class="amount-item">
                <strong>DISCOUNT PERCENTAGE</strong>
                {{ $discount_percentage }}%
            </div>
            <div class="amount-item">
                <strong>DISCOUNT AMOUNT</strong>
                {{ $discount_amount }}
            </div>
            <div class="final-total-container">
                <strong>FINAL TOTAL</strong>
                <span class="final-total">{{ $final_total }}</span>
            </div>
        </div>

        <div class="payment-slots">
            <h3>Payment Schedule</h3>
            <table>
                <thead>
                    <tr>
                        <th>Installment</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Payment Date</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($slots as $slot)
                        <tr>
                            <td>{{ $slot->slot_name }}</td>
                            <td>{{ $slot->slot_percentage }}</td>
                            <td class="status-{{ $slot->payment == 1 ? 'paid' : 'pending' }}">
                                {{ $slot->payment == 1 ? 'Paid' : 'Pending' }}
                            </td>
                            <td>{{ \Carbon\Carbon::parse($slot->date)->format('d M, Y') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice - {{ $client['name'] }}</title>
    <style>
        @page {
            margin: 10mm;
            size: A4;
        }
        
        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #333;
            line-height: 1.5;
            background-color: #f9f9f9;
            font-size: 14px;
        }

        .cover-page {
            height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
            color: white;
            padding: 40px;
            box-sizing: border-box;
        }

        .cover-page h1 {
            font-size: 48px;
            margin-bottom: 10px;
            font-weight: 300;
        }

        .cover-page h2 {
            font-size: 24px;
            margin-top: 0;
            font-weight: 300;
        }

        .container {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background: white;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }

        .header h1 {
            margin: 0;
            font-size: 28px;
            color: #2c3e50;
            font-weight: 600;
            letter-spacing: 1px;
        }

        .header p {
            color: #7f8c8d;
            margin-top: 5px;
        }

        .invoice-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            gap: 30px;
        }

        .invoice-section {
            flex: 1;
            min-width: 0;
        }

        .section-title {
            font-size: 16px;
            color: #2c3e50;
            margin-bottom: 15px;
            padding-bottom: 5px;
            border-bottom: 2px solid #3498db;
            font-weight: 600;
        }

        .info-card {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            border-left: 4px solid #3498db;
        }

        .info-card p {
            margin: 8px 0;
            color: #555;
        }

        .info-card strong {
            color: #2c3e50;
        }

        .studio-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 10px;
        }

        .studio-logo {
            width: 70px;
            height: 70px;
            object-fit: contain;
            border-radius: 4px;
            border: 1px solid #eee;
        }

        .studio-name {
            margin: 0;
            color: #2c3e50;
            font-size: 20px;
            font-weight: 600;
        }

        .services-header {
            margin: 25px 0 15px;
            padding-bottom: 8px;
            border-bottom: 2px solid #3498db;
        }

        .services-header h2 {
            color: #2c3e50;
            margin: 0;
            font-size: 18px;
            font-weight: 600;
        }

        .invoice-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .invoice-table thead th {
            background-color: #2c3e50;
            color: white;
            padding: 10px 12px;
            text-align: left;
            font-weight: 500;
        }

        .invoice-table tbody tr {
            border-bottom: 1px solid #eee;
        }

        .invoice-table tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        .invoice-table td {
            padding: 10px 12px;
            vertical-align: top;
        }

        .function-header {
            background-color: #34495e;
            color: white;
            padding: 8px 12px;
            margin: 20px 0 5px;
            font-size: 14px;
            border-radius: 4px 4px 0 0;
        }

        .summary {
            margin-top: 30px;
            margin-left: auto;
            width: 300px;
        }

        .summary-table {
            width: 100%;
            border-collapse: collapse;
        }

        .summary-table td {
            padding: 8px 10px;
            border-bottom: 1px solid #eee;
        }

        .summary-label {
            font-weight: 600;
            color: #2c3e50;
        }

        .discount {
            color: #e74c3c;
        }

        .total-row {
            background-color: #2c3e50;
            color: white;
            font-weight: 600;
        }

        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #eee;
            text-align: center;
            font-size: 12px;
            color: #7f8c8d;
        }

        .services-container {
            padding: 20px;
            box-sizing: border-box;
        }

        .services-section {
            margin-bottom: 30px;
        }

        .services-section h2 {
            color: #2c3e50;
            border-bottom: 2px solid #3498db;
            padding-bottom: 8px;
            margin-top: 0;
            font-size: 18px;
            font-weight: 600;
        }

        .services-list {
            list-style-type: none;
            padding-left: 0;
        }

        .services-list li {
            margin-bottom: 10px;
            padding-left: 20px;
            position: relative;
        }

        .services-list li:before {
            content: "•";
            color: #3498db;
            font-weight: bold;
            position: absolute;
            left: 0;
        }

        .privacy-policy-container {
            padding: 20px;
            box-sizing: border-box;
        }

        .privacy-policy-container h2 {
            color: #2c3e50;
            border-bottom: 2px solid #3498db;
            padding-bottom: 8px;
            margin-top: 0;
            font-size: 18px;
            font-weight: 600;
        }

        .privacy-policy-content {
            list-style-type: none;
            padding-left: 0;
        }

        .privacy-policy-content li {
            margin-bottom: 12px;
            padding-left: 25px;
            position: relative;
            counter-increment: item;
        }

        .privacy-policy-content li:before {
            content: counter(item) ".";
            position: absolute;
            left: 0;
            color: #3498db;
            font-weight: bold;
        }

        .page-break {
            page-break-after: always;
        }

        @media print {
            body {
                background: none;
                font-size: 13px;
            }
            .container {
                box-shadow: none;
                padding: 0;
            }
            .cover-page {
                height: 100%;
                padding: 20mm;
            }
            .header {
                margin-bottom: 20px;
            }
            .invoice-table td, 
            .invoice-table th {
                padding: 8px 10px;
            }
        }
    </style>
</head>
<body>
    {{-- Only show images if they exist --}}
    @php
        $showImage1 = !empty($client['image']) && file_exists(public_path('images/yourStory/' . $client['image']));
        $showImage2 = !empty($client['image2']) && file_exists(public_path('images/yourStory/' . $client['image2']));
    @endphp

    @if($showImage1)
        <div style="page-break-after: always; height: 100vh;">
            <img src="{{ public_path('images/yourStory/' . $client['image']) }}" style="width: 100%; height: 100%; object-fit: cover;">
        </div>
    @endif

    @if($showImage2)
        <div style="page-break-after: {{ $showImage1 ? 'always' : 'auto' }}; height: 100vh;">
            <img src="{{ public_path('images/yourStory/' . $client['image2']) }}" style="width: 100%; height: 100%; object-fit: cover;">
        </div>
    @endif


    {{-- Invoice Content --}}
    <div class="container">
        <div class="header">
            <h1>INVOICE</h1>
            <p>Date: {{ date('F j, Y') }} | Event Tpe : {{ $client['particular_function']['name'] ?? 'N/A' }}</p>
        </div>

        <div class="invoice-info">
            <div class="invoice-section">
                <div class="section-title">BILL FROM</div>
                <div class="info-card">
                    <div class="studio-header">
                        @if(!empty($client['user_details']['image']) && file_exists(public_path('images/user/' . $client['user_details']['image'])))
                            <img src="{{ public_path('images/user/' . $client['user_details']['image']) }}" class="studio-logo" alt="Studio Logo">
                        @endif
                        <h3 class="studio-name">{{ $client['user_details']['studio_name'] ?? $client['user_details']['name'] ?? 'N/A' }}</h3>
                    </div>
                    <p><strong>Name:</strong> {{ $client['user_details']['name'] ?? 'N/A' }}</p>
                    <p><strong>Phone:</strong> {{ $client['user_details']['phone_number'] ?? 'N/A' }}</p>
                    <p><strong>Visit Instagram Profile:</strong> {{ $client['user_details']['instagram_link'] ?? 'N/A' }}</p>
                    <p><strong>Visit Facebook Profile:</strong> {{ $client['user_details']['facebook_link'] ?? 'N/A' }}</p>
                    <p><strong>Visit Youtube Channel:</strong> {{ $client['user_details']['youtube_link'] ?? 'N/A' }}</p>
                    <p><strong>Visit Our Website:</strong> {{ $client['user_details']['website_link'] ?? 'N/A' }}</p>
                    <p><strong>Email:</strong> {{ $client['user_details']['email'] ?? 'N/A' }}</p>
                    <p><strong>Address:</strong> {{ $client['user_details']['address'] ?? 'N/A' }}</p>
                </div>
            </div>

            <div class="invoice-section">
                <div class="section-title">BILL TO</div>
                <div class="info-card">
                    <p><strong>{{ $client['name'] ?? 'N/A' }}</strong></p>
                    <p>{{ $client['address'] ?? 'N/A' }}</p>
                    <p><strong>Phone:</strong> {{ $client['phone_number'] ?? 'N/A' }}</p>
                    <p><strong>Start Date:</strong> {{ !empty($client['starting_date']) ? date('F j, Y', strtotime($client['starting_date'])) : 'N/A' }}</p>
                    <!-- <p><strong>Event Type:</strong> {{ $client['particular_function']['name'] ?? 'N/A' }}</p> -->
                </div>
            </div>
        </div>

        <div class="services-header">
            <h2>SERVICES RENDERED</h2>
        </div>

        @foreach (($client['generate_bill']['breakdown'] ?? []) as $func)
            <div class="function-header">
                {{ isset($func['function_name']) ? strtoupper($func['function_name']) : 'FUNCTION' }} - 
                {{ isset($func['date']) ? date('F j, Y', strtotime($func['date'])) : 'N/A' }} 
                ({{ $func['day_label'] ?? 'N/A' }})
            </div>

            <table class="invoice-table">
                <thead>
                    <tr>
                        <th style="width: 70%">Service</th>
                        <th style="width: 30%">Qty</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach (($func['categories'] ?? []) as $category)
                        <tr>
                            <td>{{ $category['category_role'] ?? 'N/A' }}</td>
                            <td>{{ $category['quantity'] ?? '0' }}</td>
                        </tr>
                    @endforeach
                    <!-- <tr>
                        <td colspan="2" style="text-align: right; padding-right: 20px;">
                            <strong>Subtotal for {{ $func['function_name'] ?? 'Function' }}:</strong>
                            <span style="margin-left: 15px;">
                                @if(isset($func['total']) && is_numeric($func['total']))
                                    {{ number_format((float)$func['total'], 2) }}
                                @else
                                    N/A
                                @endif
                            </span>
                        </td>
                    </tr> -->
                </tbody>
            </table>
        @endforeach

        <div class="summary">
            <table class="summary-table">
                <tr>
                    <td class="summary-label">Subtotal:</td>
                    <td style="text-align: right;">
                        @if(isset($client['generate_bill']['grand_total']) && is_numeric($client['generate_bill']['grand_total']))
                            {{ number_format((float)$client['generate_bill']['grand_total'], 2) }}
                        @else
                            N/A
                        @endif
                    </td>
                </tr>
                @if(isset($client['generate_bill']['discount_percentage']) && $client['generate_bill']['discount_percentage'] > 0)
                <tr>
                    <td class="summary-label discount">Discount ({{ $client['generate_bill']['discount_percentage'] }}%):</td>
                    <td style="text-align: right;" class="discount">
                        @if(isset($client['generate_bill']['grand_total']) && is_numeric($client['generate_bill']['grand_total']))
                            -{{ number_format((float)($client['generate_bill']['grand_total'] * $client['generate_bill']['discount_percentage'] / 100), 2) }}
                        @else
                            N/A
                        @endif
                    </td>
                </tr>
                @endif
                <tr class="total-row">
                    <td>TOTAL AMOUNT DUE:</td>
                    <td style="text-align: right;">
                        @if(isset($client['generate_bill']['grand_total']) && is_numeric($client['generate_bill']['grand_total']))
                            @php
                                $discount = isset($client['generate_bill']['discount_percentage']) ? 
                                    (float)$client['generate_bill']['discount_percentage'] : 0;
                                $grandTotal = (float)$client['generate_bill']['grand_total'];
                                $total = $grandTotal - ($grandTotal * $discount / 100);
                            @endphp
                            {{ number_format($total, 2) }}
                        @else
                            N/A
                        @endif
                    </td>
                </tr>
            </table>
        </div>

        <div class="footer">
            <!-- <p>Thank you for your business. Please make payments to the account details provided above.</p> -->
            <p>{{ $client['user_details']['studio_name'] ?? $client['user_details']['name'] ?? '' }}</p>
        </div>
    </div>

    {{-- Additional Services --}}
    <div class="page-break"></div>
    <div class="container services-container">
        <div class="services-section">
            <h2>COMPLIMENT SERVICES</h2>
            <ul class="services-list">
                @forelse($client['compliment_services'] ?? [] as $service)
                    <li>{{ $service['name'] ?? 'N/A' }}</li>
                @empty
                    <li>No compliment services available</li>
                @endforelse
            </ul>
        </div>
        
        <div class="services-section">
            <h2>EXTERNAL SERVICES</h2>
            <ul class="services-list">
                @forelse($client['external_services'] ?? [] as $service)
                    <li>
                        {{ $service['service_name'] ?? 'N/A' }} - 
                        @if(isset($service['service_price']) && is_numeric($service['service_price']))
                            {{ number_format((float)$service['service_price'], 2) }}
                        @else
                            {{ $service['service_price'] ?? 'N/A' }}
                        @endif
                    </li>
                @empty
                    <li>No external services available</li>
                @endforelse
            </ul>
        </div>
    </div>

    {{-- Privacy Policy --}}
    @if(!empty($client['privacy_policy']))
        <div class="page-break"></div>
        <div class="container privacy-policy-container">
            <h2>PRIVACY POLICY</h2>
            <ol class="privacy-policy-content">
                @foreach(explode("\n", $client['privacy_policy']) as $paragraph)
                    @if(trim($paragraph))
                        <li>{{ $paragraph }}</li>
                    @endif
                @endforeach
            </ol>
        </div>
    @endif
</body>
</html>
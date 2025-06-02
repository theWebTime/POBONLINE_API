<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Team Assignment Details</title>
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
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 20px; /* Added padding */
            color: var(--dark);
            line-height: 1.5;
            background-color: var(--light); /* Set background color */
        }

        .header {
            background-color: var(--primary); /* Use primary color */
            color: white;
            padding: 20px;
            text-align: center;
            margin-bottom: 30px;
            border-radius: 8px 8px 0 0; /* Add border radius to top */
        }

        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 600; /* Added font weight */
        }

        .staff-info {
            background-color: white; /* Set background to white */
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            border: 1px solid var(--bs-border-color); /* Add border */
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .info-item {
            margin-bottom: 8px; /* Adjusted margin */
        }

        .label {
            font-weight: bold;
            color: var(--primary); /* Use primary color */
            min-width: 120px; /* Adjusted width */
            display: inline-block;
        }

        .function-count {
            background-color: var(--accent); /* Use accent color */
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            display: inline-block;
            font-weight: bold;
            margin-top: 15px; /* Adjusted margin */
        }

        .section-title {
            color: var(--primary); /* Use primary color */
            border-bottom: 2px solid var(--accent); /* Use accent color */
            padding-bottom: 8px; /* Adjusted padding */
            margin-bottom: 20px; /* Adjusted margin */
            font-size: 20px; /* Adjusted font size */
            font-weight: 600; /* Added font weight */
        }

        .assignments-section {
            padding: 20px 0; /* Added padding */
        }

        .function-card {
            border-left: 4px solid var(--accent); /* Use accent color for the line */
            background-color: white; /* Set background to white */
            border-radius: 6px;
            padding: 15px;
            margin-bottom: 15px;
            box-shadow: 0 2px 3px rgba(0,0,0,0.1);
            page-break-inside: avoid;
            border-top-right-radius: 0; /* Ensure no top-right rounding */
            border-bottom-right-radius: 0; /* Ensure no bottom-right rounding */
            border-top: 1px solid var(--bs-border-color); /* Add top border */
            border-right: 1px solid var(--bs-border-color); /* Add right border */
            border-bottom: 1px solid var(--bs-border-color); /* Add bottom border */
        }

        .function-card h3 {
            margin-top: 0;
            color: var(--accent); /* Use accent color */
            border-bottom: 1px dashed var(--bs-border-color);
            padding-bottom: 8px;
            font-size: 1.4rem; /* Adjusted font size */
            font-weight: 500; /* Added font weight */
        }

        .function-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-top: 10px; /* Added margin */
        }

        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid var(--bs-border-color);
            color: var(--secondary); /* Use secondary color */
            font-size: 0.8rem; /* Adjusted font size */
        }

        .empty-state {
            text-align: center;
            padding: 20px;
            color: var(--secondary); /* Use secondary color */
            font-style: italic;
            background-color: #f8f9fa;
            border-radius: 6px;
            border: 1px solid var(--bs-border-color); /* Add border */
        }

        .date-highlight {
            color: var(--danger); /* Use danger color */
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>TEAM ASSIGNMENT DETAILS</h1>
    </div>

    <div class="staff-info">
        <h2 class="section-title">Team Information</h2>
        <div class="info-grid">
            <div class="info-item">
                <span class="label">Team Member Name:</span> {{ $staff_name }}
            </div>
            <div class="info-item">
                <span class="label">Phone Number:</span> {{ $staff_phone }}
            </div>
        </div>
        <div class="function-count">
            Total Assigned Functions: {{ $function_count }}
        </div>
    </div>

    <div class="assignments-section">
        <h2 class="section-title">Function Assignments</h2>

        @forelse ($assigned_functions as $function)
            <div class="function-card">
                <h3>{{ $function->clientFunction->function_name }}</h3>
                <div class="function-grid">
                    <div class="info-item">
                        <span class="label">Date:</span>
                        <span class="date-highlight">
                            {{ date('d-m-Y', strtotime($function->clientFunction->date)) }}
                        </span>
                    </div>
                    <div class="info-item">
                        <span class="label">Client Name:</span> {{ $function->client->name }}
                    </div>
                    <div class="info-item">
                        <span class="label">Day:</span> {{ $function->clientFunction->day_label }}
                    </div>
                    <div class="info-item">
                        <span class="label">Time:</span> {{ $function->clientFunction->function_time }}
                    </div>
                    <div class="info-item">
                        <span class="label">Venue:</span> {{ $function->clientFunction->venue }}
                    </div>
                </div>
            </div>
        @empty
            <div class="empty-state">
                No functions currently assigned to this staff member
            </div>
        @endforelse
    </div>

    <!-- <div class="footer">
        <p>Generated on {{ date('F j, Y') }}</p>
    </div> -->
</body>
</html>
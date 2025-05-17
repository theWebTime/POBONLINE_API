<!DOCTYPE html>
<html>
<head>
  <title>Organized Staff List</title>
  <style>
    body { font-family: sans-serif; font-size: 12px; }
    table { width: 100%; border-collapse: collapse; margin-top: 20px; }
    th, td { border: 1px solid #000; padding: 8px; text-align: left; }
    th { background-color: #f0f0f0; }
  </style>
</head>
<body>
  <h2>Organized Staff List</h2>

  <table>
    <thead>
      <tr>
        <th>Function Date</th>
        <th>Time</th>
        <th>Venue</th>
        <th>Category</th>
        <th>Staff Name</th>
        <th>Client</th>
      </tr>
    </thead>
    <tbody>
      @foreach($organizedStaff as $item)
        <tr>
          <td>{{ $item->function_date }}</td>
          <td>{{ $item->function_time }}</td>
          <td>{{ $item->venue }}</td>
          <td>{{ $item->category->category_role ?? '-' }}</td>
          <td>{{ $item->staff->name ?? '-' }}</td>
          <td>{{ $item->client->name ?? '-' }}</td>
        </tr>
      @endforeach
    </tbody>
  </table>
</body>
</html>

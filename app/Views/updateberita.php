<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>VA Owner Table</title>
<style>
  table {
    border-collapse: collapse;
    width: 100%;
    text-align: center;
  }
  th, td {
    border: 1px solid #000;
    padding: 6px;
  }
  thead {
    background: #f2f2f2;
  }
  a.update-btn {
    display: inline-block;
    padding: 4px 8px;
    background: #007bff;
    color: white;
    text-decoration: none;
    border-radius: 4px;
  }
  a.update-btn:hover {
    background: #0056b3;
  }
</style>
</head>
<body>

<table>
  <thead>
    <tr>
      <th>VA Owner VA</th>
      <th>Virtual Account Phone</th>
      <th>Virtual Account Email</th>
      <th>BERITA 3</th>
      <th>NAMA</th>
      <th>Action</th>
    </tr>
  </thead>
  <tbody>
    <?php if (!empty($result)): ?>
      <?php foreach ($result as $row): ?>
        <tr>
          <td><?= esc($row->va_owner_va) ?></td>
          <td><?= esc($row->va_owner_hp) ?></td>
          <td><?= esc($row->va_owner_email) ?></td>
          <td><?= esc($row->va_owner_berita_3) ?></td>
          <td><?= esc($row->va_owner_nama) ?></td>
          <td>
            <a href="<?= site_url('sendupdate/' . $row->va_owner_va.'/'.$offset) ?>" 
               class="update-btn">
               Update
            </a>
          </td>
        </tr>
      <?php endforeach; ?>
    <?php else: ?>
      <tr><td colspan="6">No data found.</td></tr>
    <?php endif; ?>
  </tbody>
</table>

<!-- Optional: Auto-click all update buttons every 15 seconds -->
<script>
// setInterval(function() {
//   document.querySelectorAll('.update-btn').forEach(btn => btn.click());
// }, 2000);
</script>

</body>
</html>

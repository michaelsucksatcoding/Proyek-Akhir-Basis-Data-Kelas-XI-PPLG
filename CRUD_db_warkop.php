<?php
declare(strict_types=1);
/**
 * Single-file CRUD for produk, pelanggan, penjualan using PDO + Tailwind
 * Save as crud_warkop.php
 *
 * Requirements:
 * - MySQL database db_warkop (use your provided schema / inserts)
 * - PHP 8.0+ (you said 8.4.10)
 */

/* ---------- Configuration ---------- */
$dbConfig = [
    'host' => '127.0.0.1',
    'dbname' => 'db_warkop',
    'user' => 'root',
    'pass' => '',
    'charset' => 'utf8mb4',
];

/* ---------- PDO Connection ---------- */
try {
    $dsn = "mysql:host={$dbConfig['host']};dbname={$dbConfig['dbname']};charset={$dbConfig['charset']}";
    $pdo = new PDO($dsn, $dbConfig['user'], $dbConfig['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo "<h1>Database connection failed</h1><pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
    exit;
}

/* ---------- Helpers ---------- */
function h($s) { return htmlspecialchars((string)$s, ENT_QUOTES|ENT_SUBSTITUTE, 'UTF-8'); }

$entity = $_GET['entity'] ?? 'produk'; // default landing entity
$action = $_GET['action'] ?? 'list';
$baseUrl = strtok($_SERVER["REQUEST_URI"], '?'); // keeps current file path

/* ---------- Handle POST actions (Create/Update/Delete) ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postEntity = $_POST['entity'] ?? null;
    $postAction = $_POST['action'] ?? null;

    if ($postEntity === 'produk') {
        if ($postAction === 'create') {
            $nama = trim($_POST['nama_produk'] ?? '');
            $harga = (int)($_POST['harga'] ?? 0);
            $stok = (int)($_POST['stok'] ?? 0);

            $stmt = $pdo->prepare("INSERT INTO produk (nama_produk, harga, stok) VALUES (:nama, :harga, :stok)");
            $stmt->execute([':nama'=>$nama, ':harga'=>$harga, ':stok'=>$stok]);
            header("Location: {$baseUrl}?entity=produk&action=list");
            exit;
        }
        if ($postAction === 'update') {
            $id = (int)($_POST['id_produk'] ?? 0);
            $nama = trim($_POST['nama_produk'] ?? '');
            $harga = (int)($_POST['harga'] ?? 0);
            $stok = (int)($_POST['stok'] ?? 0);

            $stmt = $pdo->prepare("UPDATE produk SET nama_produk = :nama, harga = :harga, stok = :stok WHERE id_produk = :id");
            $stmt->execute([':nama'=>$nama, ':harga'=>$harga, ':stok'=>$stok, ':id'=>$id]);
            header("Location: {$baseUrl}?entity=produk&action=list");
            exit;
        }
        if ($postAction === 'delete') {
            $id = (int)($_POST['id_produk'] ?? 0);
            $stmt = $pdo->prepare("DELETE FROM produk WHERE id_produk = :id");
            $stmt->execute([':id' => $id]);
            header("Location: {$baseUrl}?entity=produk&action=list");
            exit;
        }
    }

    if ($postEntity === 'pelanggan') {
        if ($postAction === 'create') {
            $nama = trim($_POST['nama_pelanggan'] ?? '');
            $nohp = trim($_POST['no_hp'] ?? '');
            $stmt = $pdo->prepare("INSERT INTO pelanggan (nama_pelanggan, no_hp) VALUES (:nama, :nohp)");
            $stmt->execute([':nama'=>$nama, ':nohp'=>$nohp]);
            header("Location: {$baseUrl}?entity=pelanggan&action=list");
            exit;
        }
        if ($postAction === 'update') {
            $id = (int)($_POST['id_pelanggan'] ?? 0);
            $nama = trim($_POST['nama_pelanggan'] ?? '');
            $nohp = trim($_POST['no_hp'] ?? '');
            $stmt = $pdo->prepare("UPDATE pelanggan SET nama_pelanggan = :nama, no_hp = :nohp WHERE id_pelanggan = :id");
            $stmt->execute([':nama'=>$nama, ':nohp'=>$nohp, ':id'=>$id]);
            header("Location: {$baseUrl}?entity=pelanggan&action=list");
            exit;
        }
        if ($postAction === 'delete') {
            $id = (int)($_POST['id_pelanggan'] ?? 0);
            $stmt = $pdo->prepare("DELETE FROM pelanggan WHERE id_pelanggan = :id");
            $stmt->execute([':id'=>$id]);
            header("Location: {$baseUrl}?entity=pelanggan&action=list");
            exit;
        }
    }

    if ($postEntity === 'penjualan') {
        if ($postAction === 'create') {
            $id_produk = (int)($_POST['id_produk'] ?? 0);
            $id_pelanggan = (int)($_POST['id_pelanggan'] ?? 0);
            $tanggal = $_POST['tanggal'] ?? date('Y-m-d');
            $jumlah = (int)($_POST['jumlah'] ?? 0);

            // get product price
            $stmt = $pdo->prepare("SELECT harga FROM produk WHERE id_produk = :id LIMIT 1");
            $stmt->execute([':id'=>$id_produk]);
            $prod = $stmt->fetch();
            $harga = $prod ? (int)$prod['harga'] : 0;
            $total = $harga * $jumlah;

            $stmt = $pdo->prepare("INSERT INTO penjualan (id_produk, id_pelanggan, tanggal, jumlah, total_harga) VALUES (:prod, :pel, :tgl, :jml, :total)");
            $stmt->execute([':prod'=>$id_produk, ':pel'=>$id_pelanggan, ':tgl'=>$tanggal, ':jml'=>$jumlah, ':total'=>$total]);
            header("Location: {$baseUrl}?entity=penjualan&action=list");
            exit;
        }
        if ($postAction === 'update') {
            $id_penjualan = (int)($_POST['id_penjualan'] ?? 0);
            $id_produk = (int)($_POST['id_produk'] ?? 0);
            $id_pelanggan = (int)($_POST['id_pelanggan'] ?? 0);
            $tanggal = $_POST['tanggal'] ?? date('Y-m-d');
            $jumlah = (int)($_POST['jumlah'] ?? 0);

            $stmt = $pdo->prepare("SELECT harga FROM produk WHERE id_produk = :id LIMIT 1");
            $stmt->execute([':id'=>$id_produk]);
            $prod = $stmt->fetch();
            $harga = $prod ? (int)$prod['harga'] : 0;
            $total = $harga * $jumlah;

            $stmt = $pdo->prepare("UPDATE penjualan SET id_produk = :prod, id_pelanggan = :pel, tanggal = :tgl, jumlah = :jml, total_harga = :total WHERE id_penjualan = :id");
            $stmt->execute([':prod'=>$id_produk, ':pel'=>$id_pelanggan, ':tgl'=>$tanggal, ':jml'=>$jumlah, ':total'=>$total, ':id'=>$id_penjualan]);
            header("Location: {$baseUrl}?entity=penjualan&action=list");
            exit;
        }
        if ($postAction === 'delete') {
            $id = (int)($_POST['id_penjualan'] ?? 0);
            $stmt = $pdo->prepare("DELETE FROM penjualan WHERE id_penjualan = :id");
            $stmt->execute([':id' => $id]);
            header("Location: {$baseUrl}?entity=penjualan&action=list");
            exit;
        }
    }
}

/* ---------- Render functions ---------- */
function renderHeader($currentEntity) {
    ?>
    <!doctype html>
    <html lang="en">
    <head>
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width,initial-scale=1">
      <title>Warkop CRUD</title>
      <!-- Tailwind CDN (simple) -->
      <script src="https://cdn.tailwindcss.com"></script>
    </head>
    <body class="bg-gray-50 text-gray-800">
      <div class="max-w-6xl mx-auto p-6">
        <header class="mb-6">
          <h1 class="text-2xl font-semibold">CRUD Warkop</h1>
          <p class="text-sm text-gray-600 mt-1">PDO | Tailwind</p>
        </header>

        <nav class="mb-6">
          <div class="flex space-x-2">
            <?php $ents = ['produk'=>'Produk','pelanggan'=>'Pelanggan','penjualan'=>'Penjualan']; ?>
            <?php foreach ($ents as $key => $label): ?>
              <a href="?entity=<?=$key?>&action=list" class="px-3 py-1 rounded <?= $currentEntity === $key ? 'bg-blue-600 text-white' : 'bg-white border' ?>">
                <?= h($label) ?>
              </a>
            <?php endforeach; ?>
          </div>
        </nav>
    <?php
}

function renderFooter() {
    ?>
        <footer class="mt-8 text-xs text-gray-500">
          <div>Michael Trisatrio Mukti · XI PPLG</div>
        </footer>
      </div>
      <script>
        // confirm delete globally
        function confirmDelete(form) {
          if (confirm('Are you sure you want to delete this record?')) {
            return true;
          }
          return false;
        }
      </script>
    </body>
    </html>
    <?php
}

/* ---------- Pages for each entity ---------- */
if ($entity === 'produk') {
    renderHeader('produk');

    if ($action === 'list') {
        // fetch all produk
        $stmt = $pdo->query("SELECT * FROM produk ORDER BY id_produk ASC");
        $rows = $stmt->fetchAll();
        ?>
        <div class="bg-white p-4 rounded shadow">
          <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg font-medium">Daftar Produk</h2>
            <a href="?entity=produk&action=create" class="px-3 py-1 bg-green-600 text-white rounded">Tambah Produk</a>
          </div>

          <table class="min-w-full text-sm">
            <thead>
              <tr class="text-left text-gray-600 border-b">
                <th class="py-2">ID</th>
                <th>Nama Produk</th>
                <th>Harga</th>
                <th>Stok</th>
                <th class="text-right">Aksi</th>
              </tr>
            </thead>
            <tbody>
            <?php foreach ($rows as $r): ?>
              <tr class="border-b">
                <td class="py-2"><?= h($r['id_produk']) ?></td>
                <td><?= h($r['nama_produk']) ?></td>
                <td><?= number_format((int)$r['harga']) ?></td>
                <td><?= h($r['stok']) ?></td>
                <td class="text-right">
                  <a href="?entity=produk&action=edit&id=<?=h($r['id_produk'])?>" class="px-2 py-1 bg-yellow-400 rounded text-white">Edit</a>
                  <form onsubmit="return confirmDelete(this)" class="inline" method="post" style="display:inline">
                    <input type="hidden" name="entity" value="produk">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id_produk" value="<?=h($r['id_produk'])?>">
                    <button class="px-2 py-1 bg-red-600 text-white rounded">Delete</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <?php
    } elseif ($action === 'create' || $action === 'edit') {
        $isEdit = $action === 'edit';
        $row = ['id_produk'=>'', 'nama_produk'=>'', 'harga'=>'', 'stok'=>''];
        if ($isEdit) {
            $id = (int)($_GET['id'] ?? 0);
            $stmt = $pdo->prepare("SELECT * FROM produk WHERE id_produk = :id LIMIT 1");
            $stmt->execute([':id'=>$id]);
            $row = $stmt->fetch() ?: $row;
        }
        ?>
        <div class="bg-white p-6 rounded shadow max-w-xl">
          <h2 class="text-lg font-medium mb-4"><?= $isEdit ? 'Edit Produk' : 'Tambah Produk' ?></h2>
          <form method="post">
            <input type="hidden" name="entity" value="produk">
            <input type="hidden" name="action" value="<?= $isEdit ? 'update' : 'create' ?>">
            <?php if ($isEdit): ?>
              <input type="hidden" name="id_produk" value="<?=h($row['id_produk'])?>">
            <?php endif; ?>

            <label class="block mb-2">
              <div class="text-sm text-gray-700">Nama Produk</div>
              <input name="nama_produk" required class="mt-1 block w-full border rounded px-3 py-2" value="<?=h($row['nama_produk'])?>">
            </label>

            <label class="block mb-2">
              <div class="text-sm text-gray-700">Harga (IDR)</div>
              <input name="harga" type="number" min="0" required class="mt-1 block w-full border rounded px-3 py-2" value="<?=h($row['harga'])?>">
            </label>

            <label class="block mb-4">
              <div class="text-sm text-gray-700">Stok</div>
              <input name="stok" type="number" min="0" required class="mt-1 block w-full border rounded px-3 py-2" value="<?=h($row['stok'])?>">
            </label>

            <div class="flex space-x-2">
              <button class="px-4 py-2 bg-blue-600 text-white rounded"><?= $isEdit ? 'Simpan Perubahan' : 'Buat Produk' ?></button>
              <a href="?entity=produk&action=list" class="px-4 py-2 bg-gray-200 rounded">Batal</a>
            </div>
          </form>
        </div>
        <?php
    } else {
        echo "<p>Unknown action.</p>";
    }

    renderFooter();
    exit;
}

/* ---------- pelanggan ---------- */
if ($entity === 'pelanggan') {
    renderHeader('pelanggan');

    if ($action === 'list') {
        $stmt = $pdo->query("SELECT * FROM pelanggan ORDER BY id_pelanggan ASC");
        $rows = $stmt->fetchAll();
        ?>
        <div class="bg-white p-4 rounded shadow">
          <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg font-medium">Daftar Pelanggan</h2>
            <a href="?entity=pelanggan&action=create" class="px-3 py-1 bg-green-600 text-white rounded">Tambah Pelanggan</a>
          </div>

          <table class="min-w-full text-sm">
            <thead>
              <tr class="text-left text-gray-600 border-b">
                <th class="py-2">ID</th>
                <th>Nama</th>
                <th>No HP</th>
                <th class="text-right">Aksi</th>
              </tr>
            </thead>
            <tbody>
            <?php foreach ($rows as $r): ?>
              <tr class="border-b">
                <td class="py-2"><?= h($r['id_pelanggan']) ?></td>
                <td><?= h($r['nama_pelanggan']) ?></td>
                <td><?= h($r['no_hp']) ?></td>
                <td class="text-right">
                  <a href="?entity=pelanggan&action=edit&id=<?=h($r['id_pelanggan'])?>" class="px-2 py-1 bg-yellow-400 rounded text-white">Edit</a>
                  <form onsubmit="return confirmDelete(this)" class="inline" method="post" style="display:inline">
                    <input type="hidden" name="entity" value="pelanggan">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id_pelanggan" value="<?=h($r['id_pelanggan'])?>">
                    <button class="px-2 py-1 bg-red-600 text-white rounded">Delete</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <?php
    } elseif ($action === 'create' || $action === 'edit') {
        $isEdit = $action === 'edit';
        $row = ['id_pelanggan'=>'', 'nama_pelanggan'=>'', 'no_hp'=>''];
        if ($isEdit) {
            $id = (int)($_GET['id'] ?? 0);
            $stmt = $pdo->prepare("SELECT * FROM pelanggan WHERE id_pelanggan = :id LIMIT 1");
            $stmt->execute([':id'=>$id]);
            $row = $stmt->fetch() ?: $row;
        }
        ?>
        <div class="bg-white p-6 rounded shadow max-w-xl">
          <h2 class="text-lg font-medium mb-4"><?= $isEdit ? 'Edit Pelanggan' : 'Tambah Pelanggan' ?></h2>
          <form method="post">
            <input type="hidden" name="entity" value="pelanggan">
            <input type="hidden" name="action" value="<?= $isEdit ? 'update' : 'create' ?>">
            <?php if ($isEdit): ?>
              <input type="hidden" name="id_pelanggan" value="<?=h($row['id_pelanggan'])?>">
            <?php endif; ?>

            <label class="block mb-2">
              <div class="text-sm text-gray-700">Nama Pelanggan</div>
              <input name="nama_pelanggan" required class="mt-1 block w-full border rounded px-3 py-2" value="<?=h($row['nama_pelanggan'])?>">
            </label>

            <label class="block mb-4">
              <div class="text-sm text-gray-700">No HP</div>
              <input name="no_hp" required class="mt-1 block w-full border rounded px-3 py-2" value="<?=h($row['no_hp'])?>">
            </label>

            <div class="flex space-x-2">
              <button class="px-4 py-2 bg-blue-600 text-white rounded"><?= $isEdit ? 'Simpan Perubahan' : 'Buat Pelanggan' ?></button>
              <a href="?entity=pelanggan&action=list" class="px-4 py-2 bg-gray-200 rounded">Batal</a>
            </div>
          </form>
        </div>
        <?php
    } else {
        echo "<p>Unknown action.</p>";
    }

    renderFooter();
    exit;
}

/* ---------- penjualan ---------- */
if ($entity === 'penjualan') {
    renderHeader('penjualan');

    if ($action === 'list') {
        // join to show names
        $stmt = $pdo->query("SELECT pjl.*, pr.nama_produk, pel.nama_pelanggan
                             FROM penjualan pjl
                             LEFT JOIN produk pr ON pjl.id_produk = pr.id_produk
                             LEFT JOIN pelanggan pel ON pjl.id_pelanggan = pel.id_pelanggan
                             ORDER BY pjl.id_penjualan ASC");
        $rows = $stmt->fetchAll();
        ?>
        <div class="bg-white p-4 rounded shadow">
          <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg font-medium">Daftar Penjualan</h2>
            <a href="?entity=penjualan&action=create" class="px-3 py-1 bg-green-600 text-white rounded">Tambah Penjualan</a>
          </div>

          <table class="min-w-full text-sm">
            <thead>
              <tr class="text-left text-gray-600 border-b">
                <th class="py-2">ID</th>
                <th>Tanggal</th>
                <th>Produk</th>
                <th>Pelanggan</th>
                <th>Jumlah</th>
                <th>Total Harga</th>
                <th class="text-right">Aksi</th>
              </tr>
            </thead>
            <tbody>
            <?php foreach ($rows as $r): ?>
              <tr class="border-b">
                <td class="py-2"><?= h($r['id_penjualan']) ?></td>
                <td><?= h($r['tanggal']) ?></td>
                <td><?= h($r['nama_produk'] ?? '—') ?></td>
                <td><?= h($r['nama_pelanggan'] ?? '—') ?></td>
                <td><?= h($r['jumlah']) ?></td>
                <td><?= number_format((int)$r['total_harga']) ?></td>
                <td class="text-right">
                  <a href="?entity=penjualan&action=edit&id=<?=h($r['id_penjualan'])?>" class="px-2 py-1 bg-yellow-400 rounded text-white">Edit</a>
                  <form onsubmit="return confirmDelete(this)" class="inline" method="post" style="display:inline">
                    <input type="hidden" name="entity" value="penjualan">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id_penjualan" value="<?=h($r['id_penjualan'])?>">
                    <button class="px-2 py-1 bg-red-600 text-white rounded">Delete</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <?php
    } elseif ($action === 'create' || $action === 'edit') {
        $isEdit = $action === 'edit';
        $row = ['id_penjualan'=>'', 'id_produk'=>'', 'id_pelanggan'=>'', 'tanggal'=>date('Y-m-d'), 'jumlah'=>1, 'total_harga'=>0];

        // fetch produk & pelanggan lists for selects
        $prods = $pdo->query("SELECT id_produk, nama_produk, harga FROM produk ORDER BY nama_produk ASC")->fetchAll();
        $pels = $pdo->query("SELECT id_pelanggan, nama_pelanggan FROM pelanggan ORDER BY nama_pelanggan ASC")->fetchAll();

        if ($isEdit) {
            $id = (int)($_GET['id'] ?? 0);
            $stmt = $pdo->prepare("SELECT * FROM penjualan WHERE id_penjualan = :id LIMIT 1");
            $stmt->execute([':id'=>$id]);
            $f = $stmt->fetch();
            if ($f) $row = $f;
        }
        ?>
        <div class="bg-white p-6 rounded shadow max-w-2xl">
          <h2 class="text-lg font-medium mb-4"><?= $isEdit ? 'Edit Penjualan' : 'Tambah Penjualan' ?></h2>
          <form method="post">
            <input type="hidden" name="entity" value="penjualan">
            <input type="hidden" name="action" value="<?= $isEdit ? 'update' : 'create' ?>">
            <?php if ($isEdit): ?>
              <input type="hidden" name="id_penjualan" value="<?=h($row['id_penjualan'])?>">
            <?php endif; ?>

            <div class="grid grid-cols-2 gap-4">
              <label class="block">
                <div class="text-sm text-gray-700">Produk</div>
                <select name="id_produk" required class="mt-1 block w-full border rounded px-3 py-2">
                  <option value="">-- pilih produk --</option>
                  <?php foreach ($prods as $p): ?>
                    <option value="<?=h($p['id_produk'])?>" <?= (int)$p['id_produk'] === (int)$row['id_produk'] ? 'selected' : '' ?>>
                      <?= h($p['nama_produk']) ?> — <?= number_format((int)$p['harga']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </label>

              <label class="block">
                <div class="text-sm text-gray-700">Pelanggan</div>
                <select name="id_pelanggan" required class="mt-1 block w-full border rounded px-3 py-2">
                  <option value="">-- pilih pelanggan --</option>
                  <?php foreach ($pels as $pl): ?>
                    <option value="<?=h($pl['id_pelanggan'])?>" <?= (int)$pl['id_pelanggan'] === (int)$row['id_pelanggan'] ? 'selected' : '' ?>>
                      <?= h($pl['nama_pelanggan']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </label>

              <label class="block">
                <div class="text-sm text-gray-700">Tanggal</div>
                <input name="tanggal" type="date" required class="mt-1 block w-full border rounded px-3 py-2" value="<?=h($row['tanggal'])?>">
              </label>

              <label class="block">
                <div class="text-sm text-gray-700">Jumlah</div>
                <input name="jumlah" type="number" min="1" required class="mt-1 block w-full border rounded px-3 py-2" value="<?=h($row['jumlah'])?>">
              </label>
            </div>

            <p class="text-sm text-gray-500 mt-3">Total harga dihitung otomatis berdasarkan harga produk × jumlah saat submit.</p>

            <div class="mt-4 flex space-x-2">
              <button class="px-4 py-2 bg-blue-600 text-white rounded"><?= $isEdit ? 'Simpan Perubahan' : 'Buat Penjualan' ?></button>
              <a href="?entity=penjualan&action=list" class="px-4 py-2 bg-gray-200 rounded">Batal</a>
            </div>
          </form>
        </div>
        <?php

    } else {
        echo "<p>Unknown action.</p>";
    }

    renderFooter();
    exit;
}

/* If entity not matched: show default */
renderHeader($entity);
echo "<p>Select an entity from the menu.</p>";
renderFooter();
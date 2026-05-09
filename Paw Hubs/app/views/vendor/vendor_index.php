<?php
$products = $products ?? [];
$message = $message ?? null;
$errors = $errors ?? [];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Vendor Products</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">

    <style>
        * {
            box-sizing: border-box;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        body {
            margin: 0;
            background: #f5faf8;
            color: #234047;
        }

        .vendor-page {
            max-width: 1240px;
            margin: 0 auto;
            padding: 34px;
        }

        .page-head {
            display: flex;
            justify-content: space-between;
            gap: 18px;
            align-items: center;
            margin-bottom: 24px;
        }

        .page-head h1 {
            margin: 0;
            font-size: 34px;
        }

        .page-head p {
            margin: 6px 0 0;
            color: #5b7378;
        }

        .alert {
            padding: 14px 16px;
            border-radius: 14px;
            margin-bottom: 16px;
            font-weight: 700;
        }

        .alert.ok {
            background: #e7f7ef;
            color: #24734d;
        }

        .alert.err {
            background: #fff0f0;
            color: #a13a3a;
        }

        .panel {
            background: #fff;
            border: 1px solid #e2f0ea;
            border-radius: 22px;
            padding: 22px;
            box-shadow: 0 18px 45px rgba(35, 64, 71, .08);
            margin-bottom: 24px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 14px;
        }

        .input-group {
            display: flex;
            flex-direction: column;
            gap: 7px;
        }

        label {
            font-size: 13px;
            font-weight: 800;
            color: #45666d;
        }

        input,
        textarea,
        select {
            border: 1px solid #d9e9e3;
            border-radius: 12px;
            padding: 11px 12px;
            font: inherit;
            outline: none;
            background: #fbfefd;
        }

        textarea {
            min-height: 44px;
            resize: vertical;
        }

        .wide {
            grid-column: span 2;
        }

        .btn {
            border: 0;
            border-radius: 12px;
            background: #6BB5A8;
            color: white;
            padding: 11px 16px;
            font-weight: 800;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            justify-content: center;
            text-decoration: none;
        }

        .btn:hover {
            background: #5aa093;
        }

        .btn.danger {
            background: #e56b6f;
        }

        .btn.secondary {
            background: #94CDD3;
            color: #234047;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 14px 12px;
            border-bottom: 1px solid #edf5f2;
            text-align: left;
            vertical-align: middle;
        }

        th {
            color: #5b7378;
            font-size: 13px;
        }

        td strong {
            color: #234047;
        }

        .stock {
            font-weight: 800;
            color: #4f9186;
        }

        .actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .edit-row {
            display: none;
            background: #f7fcfa;
        }

        .edit-row.open {
            display: table-row;
        }

        @media (max-width: 900px) {
            .form-grid {
                grid-template-columns: 1fr;
            }

            .wide {
                grid-column: span 1;
            }

            .table-wrap {
                overflow-x: auto;
            }
        }
    </style>
</head>

<body>
    <main class="vendor-page">
        <header class="page-head">
            <div>
                <h1>Vendor Products</h1>
                <p>Add, update, delete products, and manage stock quantities.</p>
            </div>
        </header>

        <?php if ($message): ?>
            <div class="alert ok"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <?php foreach ($errors as $error): ?>
            <div class="alert err"><?= htmlspecialchars($error) ?></div>
        <?php endforeach; ?>

        <section class="panel">
            <h2>Add Product</h2>

            <form method="POST" class="form-grid">
                <input type="hidden" name="action" value="add_product">

                <div class="input-group">
                    <label>Name</label>
                    <input name="name" required>
                </div>

                <div class="input-group">
                    <label>Price</label>
                    <input name="price" type="number" step="0.01" min="0" required>
                </div>

                <div class="input-group">
                    <label>Stock Quantity</label>
                    <input name="stock" type="number" min="0" value="0" required>
                </div>

                <div class="input-group">
                    <label>Category</label>
                    <input name="category" placeholder="supplements, treats, toys...">
                </div>

                <div class="input-group wide">
                    <label>Description</label>
                    <textarea name="short_description"></textarea>
                </div>

                <div class="input-group">
                    <label>Image filename</label>
                    <input name="image" placeholder="Dry-Food.jpg">
                </div>

                <div class="input-group">
                    <label>Rating</label>
                    <input name="rating" type="number" step="0.1" min="0" max="5" value="4.8">
                </div>

                <label style="display:flex;align-items:center;gap:8px;margin-top:28px;">
                    <input type="checkbox" name="is_recommended" checked>
                    Recommended
                </label>

                <button class="btn" type="submit" style="margin-top:24px;">
                    <i class="fas fa-plus"></i> Add Product
                </button>
            </form>
        </section>

        <section class="panel">
            <h2>Product Inventory</h2>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Recommended</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if (empty($products)): ?>
                            <tr>
                                <td colspan="6">No products yet.</td>
                            </tr>
                        <?php endif; ?>

                        <?php foreach ($products as $product): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($product['name']) ?></strong><br>
                                    <small><?= htmlspecialchars($product['short_description'] ?? '') ?></small>
                                </td>
                                <td><?= htmlspecialchars($product['category'] ?? '') ?></td>
                                <td>EGP <?= number_format((float)$product['price'], 2) ?></td>
                                <td><span class="stock"><?= (int)$product['stock'] ?></span></td>
                                <td><?= !empty($product['is_recommended']) ? 'Yes' : 'No' ?></td>
                                <td>
                                    <?php if (!empty($product['is_static'])): ?>
                                        <span class="stock">Static</span>
                                    <?php else: ?>
                                        <div class="actions">
                                            <button class="btn secondary" type="button" onclick="toggleEdit(<?= (int)$product['id'] ?>)">
                                                <i class="fas fa-pen"></i> Edit
                                            </button>

                                            <form method="POST" onsubmit="return confirm('Delete this product?')">
                                                <input type="hidden" name="action" value="delete_product">
                                                <input type="hidden" name="product_id" value="<?= (int)$product['id'] ?>">
                                                <button class="btn danger" type="submit"><i class="fas fa-trash"></i></button>
                                            </form>
                                        </div>
                                    <?php endif; ?>
                                </td>
                            </tr>

                            <?php if (empty($product['is_static'])): ?>
                                <tr class="edit-row" id="edit-<?= (int)$product['id'] ?>">
                                    <td colspan="6">
                                        <form method="POST" class="form-grid">
                                            <input type="hidden" name="action" value="update_product">
                                            <input type="hidden" name="product_id" value="<?= (int)$product['id'] ?>">

                                            <div class="input-group">
                                                <label>Name</label>
                                                <input name="name" value="<?= htmlspecialchars($product['name']) ?>" required>
                                            </div>

                                            <div class="input-group">
                                                <label>Price</label>
                                                <input name="price" type="number" step="0.01" min="0" value="<?= htmlspecialchars($product['price']) ?>" required>
                                            </div>

                                            <div class="input-group">
                                                <label>Stock Quantity</label>
                                                <input name="stock" type="number" min="0" value="<?= (int)$product['stock'] ?>" required>
                                            </div>

                                            <div class="input-group">
                                                <label>Category</label>
                                                <input name="category" value="<?= htmlspecialchars($product['category'] ?? '') ?>">
                                            </div>

                                            <div class="input-group wide">
                                                <label>Description</label>
                                                <textarea name="short_description"><?= htmlspecialchars($product['short_description'] ?? '') ?></textarea>
                                            </div>

                                            <div class="input-group">
                                                <label>Image filename</label>
                                                <input name="image" value="<?= htmlspecialchars($product['image'] ?? '') ?>">
                                            </div>

                                            <div class="input-group">
                                                <label>Rating</label>
                                                <input name="rating" type="number" step="0.1" min="0" max="5" value="<?= htmlspecialchars($product['rating'] ?? '4.8') ?>">
                                            </div>

                                            <label style="display:flex;align-items:center;gap:8px;margin-top:28px;">
                                                <input type="checkbox" name="is_recommended" <?= !empty($product['is_recommended']) ? 'checked' : '' ?>>
                                                Recommended
                                            </label>

                                            <button class="btn" type="submit" style="margin-top:24px;">
                                                <i class="fas fa-save"></i> Save
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>

    <script>
        function toggleEdit(id) {
            document.getElementById('edit-' + id)?.classList.toggle('open');
        }
    </script>
</body>

</html>
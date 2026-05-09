<?php

class VendorController extends Controller
{
    public function index()
    {
        if (!isset($_SESSION['user_id'])) {
            header("Location: index.php?url=auth/login");
            exit;
        }

        $db = Database::getInstance()->getConnection();
        $user = $this->fetchOne($db, "SELECT * FROM users WHERE id = ?", [$_SESSION['user_id']]);
        $role = $user['role'] ?? ($_SESSION['role'] ?? 'pet_owner');

        if (!in_array($role, ['service_provider', 'admin'], true)) {
            http_response_code(403);
            die("Access denied. Vendor page is available for vendors only.");
        }

        $message = null;
        $errors = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            [$message, $errors] = $this->handleProductAction($db);
        }

        $products = $this->fetchAll(
            $db,
            "SELECT * FROM marketplace_items ORDER BY created_at DESC, id DESC"
        );

        $this->view('vendor/index', [
            'products' => $products,
            'message' => $message,
            'errors' => $errors,
            'user' => $user
        ]);
    }

    private function handleProductAction($db)
    {
        $action = $_POST['action'] ?? '';

        if ($action === 'delete_product') {
            $id = (int)($_POST['product_id'] ?? 0);

            if ($id <= 0) {
                return [null, ['Invalid product.']];
            }

            $stmt = $db->prepare("DELETE FROM marketplace_items WHERE id = ?");
            $stmt->execute([$id]);

            return ['Product deleted successfully.', []];
        }

        $id = (int)($_POST['product_id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['short_description'] ?? '');
        $price = (float)($_POST['price'] ?? 0);
        $category = trim($_POST['category'] ?? '');
        $image = trim($_POST['image'] ?? '');
        $rating = (float)($_POST['rating'] ?? 4.8);
        $stock = max(0, (int)($_POST['stock'] ?? 0));
        $isRecommended = isset($_POST['is_recommended']) ? 1 : 0;

        if ($name === '') {
            return [null, ['Product name is required.']];
        }

        if ($price <= 0) {
            return [null, ['Price must be greater than 0.']];
        }

        if ($action === 'add_product') {
            $stmt = $db->prepare("
                INSERT INTO marketplace_items
                    (name, short_description, price, category, image, rating, stock, is_recommended)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $stmt->execute([
                $name,
                $description,
                $price,
                $category,
                $image,
                $rating,
                $stock,
                $isRecommended
            ]);

            return ['Product added successfully.', []];
        }

        if ($action === 'update_product') {
            if ($id <= 0) {
                return [null, ['Invalid product.']];
            }

            $stmt = $db->prepare("
                UPDATE marketplace_items
                SET name = ?,
                    short_description = ?,
                    price = ?,
                    category = ?,
                    image = ?,
                    rating = ?,
                    stock = ?,
                    is_recommended = ?
                WHERE id = ?
            ");

            $stmt->execute([
                $name,
                $description,
                $price,
                $category,
                $image,
                $rating,
                $stock,
                $isRecommended,
                $id
            ]);

            return ['Product updated successfully.', []];
        }

        return [null, ['Invalid action.']];
    }

 
}

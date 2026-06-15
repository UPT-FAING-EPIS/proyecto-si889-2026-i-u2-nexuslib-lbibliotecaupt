<?php

require_once __DIR__ . '/../Services/SavedBooksService.php';

class CollectionsController
{
    private SavedBooksService $savedBooksService;

    public function __construct(SavedBooksService $savedBooksService)
    {
        $this->savedBooksService = $savedBooksService;
    }

    public function create(): void
    {
        $data = $this->getInputData();
        $userUuid = trim((string) ($data['user_uuid'] ?? ($_POST['user_uuid'] ?? ($_GET['user_uuid'] ?? ''))));
        $name = trim((string) ($data['name'] ?? ($_POST['name'] ?? ($_GET['name'] ?? ''))));
        if ($userUuid === '' || $name === '') {
            $this->jsonResponse(['error' => 'Faltan datos requeridos'], 400);
            return;
        }

        $result = $this->savedBooksService->createCollection($userUuid, $name);

        if ($result === false) {
            $this->jsonResponse(['success' => false, 'message' => 'No se pudo crear la colección'], 400);
            return;
        }

        $this->jsonResponse(['success' => true, 'id' => (int)$result], 201);
    }

    public function getCollections(): void
    {
        $userUuid = trim($_GET['user_uuid'] ?? '');

        if ($userUuid === '') {
            $this->jsonResponse(['error' => 'Falta user_uuid'], 400);
            return;
        }

        $rows = $this->savedBooksService->getCollections($userUuid);
        $this->jsonResponse(['data' => $rows], 200);
    }

    public function delete(): void
    {
        $data = $this->getInputData();
        $id = isset($data['id']) ? (int)$data['id'] : (isset($_GET['id']) ? (int)$_GET['id'] : 0);

        if ($id <= 0) {
            $this->jsonResponse(['error' => 'Falta id de colección'], 400);
            return;
        }

        $ok = $this->savedBooksService->deleteCollection($id);
        $status = $ok ? 200 : 400;
        $this->jsonResponse(['success' => (bool)$ok], $status);
    }

    public function update(): void
    {
        $data = $this->getInputData();
        $id = isset($data['id']) ? (int)$data['id'] : (isset($_POST['id']) ? (int)$_POST['id'] : 0);
        $name = trim((string) ($data['name'] ?? ($_POST['name'] ?? ($_GET['name'] ?? ''))));

        if ($id <= 0 || $name === '') {
            $this->jsonResponse(['error' => 'Faltan id o name'], 400);
            return;
        }

        $ok = $this->savedBooksService->renameCollection($id, $name);
        $status = $ok ? 200 : 400;
        $this->jsonResponse(['success' => (bool)$ok], $status);
    }

    public function addItem(): void
    {
        $data = $this->getInputData();
        $collectionId = isset($data['collection_id']) ? (int)$data['collection_id'] : (isset($_POST['collection_id']) ? (int)$_POST['collection_id'] : 0);
        $savedBookId = isset($data['saved_book_id']) ? (int)$data['saved_book_id'] : (isset($_POST['saved_book_id']) ? (int)$_POST['saved_book_id'] : 0);

        if ($collectionId <= 0 || $savedBookId <= 0) {
            $this->jsonResponse(['error' => 'Faltan collection_id o saved_book_id'], 400);
            return;
        }

        $result = $this->savedBooksService->addBookToCollection($collectionId, $savedBookId);
        $status = !empty($result['success']) ? 201 : 400;
        $this->jsonResponse($result, $status);
    }

    public function getItems(): void
    {
        // Supports either ?collection_id= or ?saved_book_id=
        $collectionId = isset($_GET['collection_id']) ? (int)$_GET['collection_id'] : 0;
        $savedBookId = isset($_GET['saved_book_id']) ? (int)$_GET['saved_book_id'] : 0;

        if ($collectionId > 0) {
            $rows = $this->savedBooksService->getCollectionItems($collectionId);
            $this->jsonResponse(['data' => $rows], 200);
            return;
        }

        if ($savedBookId > 0) {
            $rows = $this->savedBooksService->getCollectionsForSavedBook($savedBookId);
            $this->jsonResponse(['data' => $rows], 200);
            return;
        }

        $this->jsonResponse(['error' => 'Falta collection_id o saved_book_id'], 400);
    }

    public function removeItem(): void
    {
        $data = $this->getInputData();
        $collectionId = isset($data['collection_id']) ? (int)$data['collection_id'] : (isset($_GET['collection_id']) ? (int)$_GET['collection_id'] : 0);
        $savedBookId = isset($data['saved_book_id']) ? (int)$data['saved_book_id'] : (isset($_GET['saved_book_id']) ? (int)$_GET['saved_book_id'] : 0);

        if ($collectionId <= 0 || $savedBookId <= 0) {
            $this->jsonResponse(['error' => 'Faltan collection_id o saved_book_id'], 400);
            return;
        }

        $result = $this->savedBooksService->removeBookFromCollection($collectionId, $savedBookId);
        $status = !empty($result['success']) ? 200 : 400;
        $this->jsonResponse($result, $status);
    }

    public function getAvailableBooks(): void
    {
        $userUuid = trim((string) ($_GET['user_uuid'] ?? ''));
        $collectionId = isset($_GET['collection_id']) ? (int)$_GET['collection_id'] : 0;

        if ($userUuid === '' || $collectionId <= 0) {
            $this->jsonResponse(['error' => 'Faltan user_uuid o collection_id'], 400);
            return;
        }

        $rows = $this->savedBooksService->getAvailableBooksForCollection($userUuid, $collectionId);
        $this->jsonResponse(['data' => $rows], 200);
    }

    private function getInputData(): array
    {
        $raw = file_get_contents('php://input');
        if ($raw === false || trim($raw) === '') {
            return [];
        }

        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : [];
    }

    private function jsonResponse(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
    }
}

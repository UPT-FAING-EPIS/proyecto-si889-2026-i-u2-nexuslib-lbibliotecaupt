<?php

require_once __DIR__ . '/../Config/Database.php';

class CollectionsRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function createCollection(string $userUuid, string $name)
    {
        try {
            $sql = 'INSERT INTO collections (user_uuid, name, created_at) VALUES (:user_uuid, :name, NOW())';
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':user_uuid', $userUuid, PDO::PARAM_STR);
            $stmt->bindValue(':name', $name, PDO::PARAM_STR);
            $ok = $stmt->execute();

            if ($ok) {
                return (int) $this->pdo->lastInsertId();
            }
            return false;
        } catch (PDOException $e) {
            return false;
        }
    }

    public function getCollectionsByUser(string $userUuid): array
    {
        $sql = 'SELECT collections.id, collections.user_uuid, collections.name, collections.created_at, COUNT(collection_items.saved_book_id) AS total_items FROM collections LEFT JOIN collection_items ON collections.id = collection_items.collection_id WHERE collections.user_uuid = :user_uuid GROUP BY collections.id ORDER BY collections.created_at DESC, collections.id DESC';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':user_uuid', $userUuid, PDO::PARAM_STR);
        $stmt->execute();

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $rows === false ? [] : $rows;
    }

    public function deleteCollection(int $id): bool
    {
        try {
            // Use a transaction to remove pivot rows and then the collection itself
            $this->pdo->beginTransaction();

            $sqlItems = 'DELETE FROM collection_items WHERE collection_id = :id';
            $stmtItems = $this->pdo->prepare($sqlItems);
            $stmtItems->bindValue(':id', $id, PDO::PARAM_INT);
            $stmtItems->execute();

            $sql = 'DELETE FROM collections WHERE id = :id';
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            $deleted = ($stmt->rowCount() > 0);
            $this->pdo->commit();
            return $deleted;
        } catch (PDOException $e) {
            try { $this->pdo->rollBack(); } catch (\Throwable $_) {}
            return false;
        }
    }

    public function updateCollection(int $id, string $name): bool
    {
        try {
            $sql = 'UPDATE collections SET name = :name WHERE id = :id';
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':name', $name, PDO::PARAM_STR);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return ($stmt->rowCount() > 0);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function addBookToCollection(int $collectionId, int $savedBookId): bool
    {
        try {
            $sql = 'INSERT INTO collection_items (collection_id, saved_book_id, added_at) VALUES (:collection_id, :saved_book_id, NOW())';
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':collection_id', $collectionId, PDO::PARAM_INT);
            $stmt->bindValue(':saved_book_id', $savedBookId, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            // Duplicate or other constraint
            return false;
        }
    }

    public function removeBookFromCollection(int $collectionId, int $savedBookId): bool
    {
        try {
            $sql = 'DELETE FROM collection_items WHERE collection_id = :collection_id AND saved_book_id = :saved_book_id';
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':collection_id', $collectionId, PDO::PARAM_INT);
            $stmt->bindValue(':saved_book_id', $savedBookId, PDO::PARAM_INT);
            $stmt->execute();
            return ($stmt->rowCount() > 0);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function isBookInCollection(int $collectionId, int $savedBookId): bool
    {
        $sql = 'SELECT COUNT(*) FROM collection_items WHERE collection_id = :collection_id AND saved_book_id = :saved_book_id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':collection_id', $collectionId, PDO::PARAM_INT);
        $stmt->bindValue(':saved_book_id', $savedBookId, PDO::PARAM_INT);
        $stmt->execute();
        return ((int) $stmt->fetchColumn()) > 0;
    }

    public function cleanupOrphanedItems(): bool
    {
        try {
            $sql = 'DELETE ci FROM collection_items ci LEFT JOIN saved_books sb ON ci.saved_book_id = sb.id WHERE sb.id IS NULL';
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Get saved_books rows that belong to a given collection.
     * Returns array of associative rows from saved_books joined with collection_items.added_at
     */
    public function getItemsByCollection(int $collectionId): array
    {
        $sql = 'SELECT sb.* , ci.added_at FROM collection_items ci JOIN saved_books sb ON ci.saved_book_id = sb.id WHERE ci.collection_id = :collection_id ORDER BY ci.added_at DESC';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':collection_id', $collectionId, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $rows === false ? [] : $rows;
    }

    /**
     * Get collections (rows from collections) that contain a given saved_book_id
     */
    public function getCollectionsForSavedBook(int $savedBookId): array
    {
        $sql = 'SELECT c.* FROM collection_items ci JOIN collections c ON ci.collection_id = c.id WHERE ci.saved_book_id = :saved_book_id ORDER BY c.created_at DESC';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':saved_book_id', $savedBookId, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $rows === false ? [] : $rows;
    }

    public function getAvailableBooksForCollection(string $userUuid, int $collectionId): array
    {
        $sql = 'SELECT * FROM saved_books WHERE user_uuid = :user_uuid AND id NOT IN (SELECT saved_book_id FROM collection_items WHERE collection_id = :collection_id) ORDER BY id DESC';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':user_uuid', $userUuid, PDO::PARAM_STR);
        $stmt->bindValue(':collection_id', $collectionId, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $rows === false ? [] : $rows;
    }
}

<?php

require_once __DIR__ . '/../Repositories/LibraryRepositoryInterface.php';
require_once __DIR__ . '/../Repositories/CollectionsRepository.php';

class SavedBooksService
{
	private LibraryRepositoryInterface $repository;
	private ?CollectionsRepository $collectionsRepository = null;

	public function __construct(LibraryRepositoryInterface $repository, ?CollectionsRepository $collectionsRepository = null)
	{
		$this->repository = $repository;
		$this->collectionsRepository = $collectionsRepository;
	}

	public function saveBook(SavedBook $book, ?string $portadaUrl = null): array
	{
		$isSaved = $this->repository->isBookSaved($book->getUserUuid(), $book->getCodigo(), $book->getOrigen());

		if ($isSaved) {
			$removed = $this->repository->removeSavedBook($book->getUserUuid(), $book->getCodigo(), $book->getOrigen());
			if (!$removed) {
				return ['success' => false];
			}

			// Cleanup collection items that may reference deleted saved_books rows
			$this->cleanupCollectionItemsAfterSavedBookRemoval();

			return ['success' => true, 'action' => 'removed'];
		}

		$added = $this->repository->saveBook($book, $portadaUrl);
		if (!$added) {
			return ['success' => false];
		}

		// If repository set the inserted id on the model, return it to caller
		$savedBookId = $book->getId();
		if (is_int($savedBookId) && $savedBookId > 0) {
			return ['success' => true, 'action' => 'added', 'saved_book_id' => $savedBookId];
		}

		return ['success' => true, 'action' => 'added'];
	}

	public function getSavedBooks(string $uuid): array
	{
		return $this->repository->getSavedBooksByUser($uuid);
	}

	public function getAllSavedBooks(): array
	{
		if (method_exists($this->repository, 'getAllSavedBooks')) {
			return $this->repository->getAllSavedBooks();
		}

		return [];
	}

	public function isBookSaved(string $userUuid, string $codigo, string $origen): bool
	{
		return $this->repository->isBookSaved($userUuid, $codigo, $origen);
	}

	/* Collections related helpers */
	public function createCollection(string $userUuid, string $name)
	{
		if ($this->collectionsRepository === null) return false;
		return $this->collectionsRepository->createCollection($userUuid, $name);
	}

	public function getCollections(string $userUuid): array
	{
		if ($this->collectionsRepository === null) return [];
		return $this->collectionsRepository->getCollectionsByUser($userUuid);
	}

	public function deleteCollection(int $id): bool
	{
		if ($this->collectionsRepository === null) return false;
		return $this->collectionsRepository->deleteCollection($id);
	}

	public function renameCollection(int $id, string $name): bool
	{
		if ($this->collectionsRepository === null) return false;
		return $this->collectionsRepository->updateCollection($id, $name);
	}

	public function addBookToCollection(int $collectionId, int $savedBookId): array
	{
		if ($this->collectionsRepository === null) return ['success' => false, 'message' => 'collections_repo_missing'];

		// Validation: avoid duplicates
		if ($this->collectionsRepository->isBookInCollection($collectionId, $savedBookId)) {
			return ['success' => false, 'message' => 'already_exists'];
		}

		$ok = $this->collectionsRepository->addBookToCollection($collectionId, $savedBookId);
		return ['success' => (bool)$ok];
	}

	public function removeBookFromCollection(int $collectionId, int $savedBookId): array
	{
		if ($this->collectionsRepository === null) return ['success' => false, 'message' => 'collections_repo_missing'];
		$ok = $this->collectionsRepository->removeBookFromCollection($collectionId, $savedBookId);
		return ['success' => (bool)$ok];
	}

	public function getCollectionItems(int $collectionId): array
	{
		if ($this->collectionsRepository === null) return [];
		return $this->collectionsRepository->getItemsByCollection($collectionId);
	}

	public function getCollectionsForSavedBook(int $savedBookId): array
	{
		if ($this->collectionsRepository === null) return [];
		return $this->collectionsRepository->getCollectionsForSavedBook($savedBookId);
	}

	public function getAvailableBooksForCollection(string $userUuid, int $collectionId): array
	{
		if ($this->collectionsRepository === null) return [];
		return $this->collectionsRepository->getAvailableBooksForCollection($userUuid, $collectionId);
	}

	public function cleanupCollectionItemsAfterSavedBookRemoval(): void
	{
		if ($this->collectionsRepository === null) return;
		$this->collectionsRepository->cleanupOrphanedItems();
	}
}

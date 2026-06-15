<?php

require_once __DIR__ . '/../app/Config/Database.php';
require_once __DIR__ . '/../app/Models/SavedBook.php';
require_once __DIR__ . '/../app/Models/ReservedBook.php';
require_once __DIR__ . '/../app/Repositories/LibraryRepositoryInterface.php';
require_once __DIR__ . '/../app/Repositories/LibraryRepository.php';
require_once __DIR__ . '/../app/Repositories/CollectionsRepository.php';
require_once __DIR__ . '/../app/Services/SavedBooksService.php';
require_once __DIR__ . '/../app/Services/ReservationService.php';
require_once __DIR__ . '/../app/Controllers/LibraryController.php';
require_once __DIR__ . '/../app/Controllers/CollectionsController.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS, DELETE');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
	http_response_code(204);
	exit;
}

try {
	$pdo = Database::getConnection();
	$repository = new LibraryRepository($pdo);
	$collectionsRepo = new CollectionsRepository($pdo);
	$savedBooksService = new SavedBooksService($repository, $collectionsRepo);
	$reservationService = new ReservationService($repository);
	$controller = new LibraryController($savedBooksService, $reservationService);
	$collectionsController = new CollectionsController($savedBooksService);

	$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';

	if (strpos($uri, '/admin/') === false && preg_match('/\/(save|saved|check-status|reserve|reserved|cancel)$/', $uri, $matches)) {
		switch ($matches[1]) {
			case 'save':
				$controller->save();
				exit;

			case 'saved':
				$controller->getSaved();
				exit;

			case 'check-status':
				$controller->checkStatus();
				exit;

			case 'reserve':
				$controller->reserve();
				exit;

			case 'reserved':
				$controller->getReserved();
				exit;

			case 'cancel':
				$controller->cancel();
				exit;
		}
	}

	// Collections endpoints: /collections and /collections/items

	// Collections update endpoint: /collections/update
	if (preg_match('#/collections/update$#', $uri)) {
		switch ($_SERVER['REQUEST_METHOD'] ?? 'GET') {
			case 'POST':
				$collectionsController->update();
				exit;
			default:
				http_response_code(405);
				echo json_encode(['error' => 'Method not allowed'], JSON_UNESCAPED_UNICODE);
				exit;
		}
	}

	// /collections/available-books -> list saved_books not yet in the collection
	if (preg_match('#/collections/available-books$#', $uri)) {
		switch ($_SERVER['REQUEST_METHOD'] ?? 'GET') {
			case 'GET':
				$collectionsController->getAvailableBooks();
				exit;
			default:
				http_response_code(405);
				echo json_encode(['error' => 'Method not allowed'], JSON_UNESCAPED_UNICODE);
				exit;
		}
	}

	if (preg_match('#/collections(?:/items)?$#', $uri)) {
		// /collections/items -> add/remove
		if (preg_match('#/collections/items$#', $uri)) {
			switch ($_SERVER['REQUEST_METHOD'] ?? 'GET') {
				case 'GET':
					$collectionsController->getItems();
					exit;
				case 'POST':
					$collectionsController->addItem();
					exit;
				case 'DELETE':
					$collectionsController->removeItem();
					exit;
				default:
					http_response_code(405);
					echo json_encode(['error' => 'Method not allowed'], JSON_UNESCAPED_UNICODE);
					exit;
			}
		}

		// /collections -> create, list, delete
		if (preg_match('#/collections$#', $uri)) {
			switch ($_SERVER['REQUEST_METHOD'] ?? 'GET') {
				case 'POST':
					$collectionsController->create();
					exit;
				case 'GET':
					$collectionsController->getCollections();
					exit;
				case 'DELETE':
					$collectionsController->delete();
					exit;
				default:
					http_response_code(405);
					echo json_encode(['error' => 'Method not allowed'], JSON_UNESCAPED_UNICODE);
					exit;
			}
		}
	}

	// Delegar al enrutador si la ruta contiene /admin/ o /internal/
	if (strpos($uri, '/admin/') !== false || strpos($uri, '/internal/') !== false) {
		require __DIR__ . '/../app/Routes/api.php';
		exit;
	}

	// Respuesta por defecto si no coincide nada
	http_response_code(404);
	echo json_encode(['error' => 'Endpoint not found'], JSON_UNESCAPED_UNICODE);
	exit;
} catch (\Throwable $e) {
	http_response_code(500);
	echo json_encode(['error' => 'Error interno: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
	exit;
}

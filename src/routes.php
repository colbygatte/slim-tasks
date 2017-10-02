<?php

use Slim\Http\Request;
use Slim\Http\Response;

$app->post('/v1/tasks/completed', function (Request $request, Response $response, array $args) {
    $query = $this->db->prepare('UPDATE tasks SET completed = 1 WHERE id = :id');
    $query->bindValue(':id', $request->getParsedBodyParam('task_id'));
    $query->execute();

    return $response->withStatus(200)->withJson([
        'message' => 'Marked as completed',
        'additional_data' => ['task_id' => $request->getParsedBodyParam('task_id')]
    ]);
});

/**
 * Store a task
 */
$app->post('/v1/tasks/store', function (Request $request, Response $response, array $args) {
    $query = $this->db->prepare('INSERT INTO tasks (task) VALUES (:task);');
    $query->bindValue(':task', $request->getParsedBodyParam('task'));
    $query->execute();

    return $response->withStatus(200)->withJson(['message' => 'Successfully stored']);
});

/**
 * Get a page of tasks
 */
$app->get('/v1/tasks/all', function (Request $request, Response $response, array $args) {
    $page = $request->getQueryParam('page', 1);
    $settings = $this->get('settings')['tasks'];

    $limit = $settings['perpage'];
    $offset = ($page - 1) * $limit;

    /** @var \PDOStatement $query */
    try {
        $query = $this->db->prepare('SELECT * FROM tasks LIMIT :limit OFFSET :offset;');
        $query->bindValue(':limit', $limit, PDO::PARAM_INT);
        $query->bindValue(':offset', $offset, PDO::PARAM_INT);
        $query->execute();

        $queryResults = $query->fetchAll();
    } catch (\PDOException $e) {
        $queryResults = [];
        $this->logger->addInfo('Error loading tasks.');
        $this->logger->addInfo($e->getMessage());
    }

    return $response->withStatus(200)->withJson([
        'results' => $queryResults,
        'next_page' => empty($queryResults) ? null : $page + 1
    ]);
});

/**
 * Get a single task
 */
$app->get('/v1/tasks/view/{task_id}', function (Request $request, Response $response, array $args) {
    $query = $this->db->prepare('SELECT * FROM tasks WHERE id = :id');
    $query->execute([':id' => $args['task_id']]);
    $results = $query->fetchAll();

    if ($results) {
        return $response->withStatus(200)->withJson($results);
    } else {
        return $response->withStatus(400)->withJson(['message' => 'Task not found']);
    }
});

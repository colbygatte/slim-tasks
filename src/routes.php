<?php

use Slim\Http\Request;
use Slim\Http\Response;

$app->get('/v1/tasks/all', function (Request $request, Response $response, array $args) {
    $page = $request->getQueryParam('p', 1);
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

$app->get('/v1/tasks/{task_id}', function (Request $request, Response $response, array $args) {
    $query = $this->db->prepare('SELECT * FROM tasks WHERE id = :id');
    $query->execute([':id' => $args['task_id']]);
    $results = $query->fetchAll();

    if ($results) {
        return $response->withStatus(200)->withJson($results);
    } else {
        return $response->withStatus(400)->withJson(['message' => 'Task not found']);
    }
});

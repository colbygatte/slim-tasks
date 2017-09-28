<?php

use Slim\Http\Request;
use Slim\Http\Response;

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

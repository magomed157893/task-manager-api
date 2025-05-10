<?php

namespace App\Model;

use App\Database\Database;
use Exception;
use PDO;

class Task
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function create(array $data)
    {
        if (!isset($data['title']) || empty($data['title'])) {
            throw new Exception('Title is required');
        }

        if (strlen($data['title']) > 255) {
            throw new Exception('Title cannot be longer than 255 characters');
        }

        $title = $data['title'];
        $description = $data['description'] ?? '';
        $due_date = $data['due_date'] ?? date('Y-m-d H:i:s');
        $created_date = date('Y-m-d H:i:s');
        $status = $data['status'] ?? 'Не выполнена';
        $priority = $data['priority'] ?? 'Средний';
        $category = $data['category'] ?? '';

        $validStatuses = ['Выполнена', 'Не выполнена'];
        $validPriorities = ['Низкий', 'Средний', 'Высокий'];

        if (!in_array($status, $validStatuses)) {
            throw new Exception('Invalid status. Allowed values: Выполнена, Не выполнена');
        }

        if (!in_array($priority, $validPriorities)) {
            throw new Exception('Invalid priority. Allowed values: Низкий, Средний, Высокий');
        }

        $pattern = '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/';
        if (!preg_match($pattern, $due_date) || !preg_match($pattern, $created_date)) {
            throw new Exception('Invalid date format. Use YYYY-MM-DD HH:MM:SS');
        }

        if (strlen($category) > 255) {
            throw new Exception('Category cannot be longer than 255 characters');
        }

        $query = 'INSERT INTO tasks (title, description, due_date, created_date, status, priority, category)
            VALUES (:title, :description, :due_date, :created_date, :status, :priority, :category)';

        $stmt = $this->db->prepare($query);
        $stmt->execute([
            ':title' => $title,
            ':description' => $description,
            ':due_date' => $due_date,
            ':created_date' => $created_date,
            ':status' => $status,
            ':priority' => $priority,
            ':category' => $category
        ]);

        return $this->db->lastInsertId();
    }

    public function getAll(array $params = [])
    {
        $query = 'SELECT * FROM tasks';
        $queryParams = [];

        if (!empty($params['search'])) {
            $query .= ' WHERE title LIKE :search';
            $queryParams[':search'] = '%' . $params['search'] . '%';
        }

        $sortableFields = ['due_date', 'created_date'];
        $orderBy = 'created_date';
        if (!empty($params['sort']) && in_array($params['sort'], $sortableFields)) {
            $orderBy = $params['sort'];
        }
        $query .= ' ORDER BY ' . $orderBy;

        $limit = (int)($params['limit'] ?? 10);
        $page = (int)($params['page'] ?? 1);
        if (!is_numeric($page) || $page <= 0) {
            throw new Exception('Invalid page. It must be a positive integer');
        }
        if (!is_numeric($limit) || $limit <= 0) {
            throw new Exception('Invalid limit. It must be a positive integer');
        }

        $offset = ($page - 1) * $limit;
        $query .= ' LIMIT :limit OFFSET :offset';
        $queryParams[':limit'] = $limit;
        $queryParams[':offset'] = $offset;

        $stmt = $this->db->prepare($query);
        foreach ($queryParams as $param => $value) {
            if ($param === ':limit' || $param === ':offset') {
                $stmt->bindValue($param, $value, PDO::PARAM_INT);
            } else {
                $stmt->bindValue($param, $value);
            }
        }
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function getByID(int $id)
    {
        $query = 'SELECT * FROM tasks WHERE id = :id';
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $task = $stmt->fetch();

        if (!$task) {
            throw new Exception('Task not found with ID ' . $id);
        }

        return $task;
    }

    public function update(int $id, array $data)
    {
        $this->getById($id);

        $updatableFields = ['title', 'description', 'due_date', 'status', 'priority', 'category'];
        $updateParts = [];
        $queryParams = [':id' => $id];

        foreach ($updatableFields as $field) {
            if (array_key_exists($field, $data)) {
                $value = $data[$field];

                if (in_array($field, ['title', 'category']) && strlen($value) > 255) {
                    throw new Exception(ucfirst($field) . ' cannot be longer than 255 characters');
                }

                if ($field === 'due_date') {
                    $pattern = '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/';
                    if (!preg_match($pattern, $value)) {
                        throw new Exception('Invalid date format. Use YYYY-MM-DD HH:MM:SS');
                    }
                }

                if ($field === 'status') {
                    $validStatuses = ['Выполнена', 'Не выполнена'];
                    if (!in_array($value, $validStatuses)) {
                        throw new Exception('Invalid status. Allowed values: Выполнена, Не выполнена');
                    }
                }

                if ($field === 'priority') {
                    $validPriorities = ['Низкий', 'Средний', 'Высокий'];
                    if (!in_array($value, $validPriorities)) {
                        throw new Exception('Invalid priority. Allowed values: Низкий, Средний, Высокий');
                    }
                }

                $updateParts[] = "$field = :$field";
                $queryParams[":$field"] = $value;
            }
        }

        if (empty($updateParts)) {
            throw new Exception('No valid fields to update');
        }

        $query = 'UPDATE tasks SET ' . implode(', ', $updateParts) . ' WHERE id = :id';
        $stmt = $this->db->prepare($query);
        $stmt->execute($queryParams);
    }

    public function delete(int $id)
    {
        $this->getById($id);

        $query = 'DELETE FROM tasks WHERE id = :id';
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() === 0) {
            throw new Exception('Failed to delete task with ID ' . $id);
        }
    }
}

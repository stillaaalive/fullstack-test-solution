<?php

namespace App\Controllers;

use App\Models\CommentModel;
use CodeIgniter\HTTP\ResponseInterface;
use DateTime;

class Comments extends BaseController
{
    private const PER_PAGE = 3;

    public function index(): ResponseInterface
    {
        $page      = max(1, (int) ($this->request->getGet('page') ?? 1));
        $sort      = $this->request->getGet('sort') ?? 'id';
        $direction = strtoupper($this->request->getGet('direction') ?? 'DESC');

        $sortColumns = [
            'id'   => 'id',
            'date' => 'comment_date',
        ];

        $column    = $sortColumns[$sort] ?? $sortColumns['id'];
        $direction = in_array($direction, ['ASC', 'DESC'], true) ? $direction : 'DESC';

        $model = new CommentModel();
        $total = $model->countAll();
        $pages = max(1, (int) ceil($total / self::PER_PAGE));

        if ($page > $pages) {
            $page = $pages;
        }

        $comments = $model
            ->orderBy($column, $direction)
            ->orderBy('id', 'DESC')
            ->findAll(self::PER_PAGE, ($page - 1) * self::PER_PAGE);

        return $this->response->setJSON([
            'comments' => $comments,
            'pagination' => [
                'page'       => $page,
                'perPage'    => self::PER_PAGE,
                'total'      => $total,
                'totalPages' => $pages,
            ],
            'sort'      => $sort,
            'direction' => $direction,
        ]);
    }

    public function create(): ResponseInterface
    {
        $data = $this->request->getJSON(true);

        if (! is_array($data)) {
            $data = $this->request->getPost();
        }

        $data = [
            'email'        => trim((string) ($data['email'] ?? '')),
            'text'         => trim((string) ($data['text'] ?? '')),
            'comment_date' => trim((string) ($data['comment_date'] ?? '')),
        ];

        $rules = [
            'email' => 'required|valid_email|max_length[255]',
            'text'  => 'required|max_length[5000]',
        ];

        if (! $this->validateData($data, $rules)) {
            return $this->response
                ->setStatusCode(422)
                ->setJSON(['errors' => $this->validator->getErrors()]);
        }

        $date = DateTime::createFromFormat('Y-m-d\\TH:i', $data['comment_date']);

        if (! $date || $date->format('Y-m-d\\TH:i') !== $data['comment_date']) {
            return $this->response
                ->setStatusCode(422)
                ->setJSON(['errors' => ['comment_date' => 'Укажите корректную дату и время.']]);
        }

        $model = new CommentModel();
        $id    = $model->insert([
            'email'        => $data['email'],
            'text'         => $data['text'],
            'comment_date' => $date->format('Y-m-d H:i:s'),
        ], true);

        if ($id === false) {
            return $this->response
                ->setStatusCode(500)
                ->setJSON(['message' => 'Не удалось сохранить комментарий.']);
        }

        return $this->response
            ->setStatusCode(201)
            ->setJSON([
                'message' => 'Комментарий добавлен.',
                'id'      => $id,
            ]);
    }

    public function delete(int $id): ResponseInterface
    {
        $model = new CommentModel();

        if ($model->find($id) === null) {
            return $this->response
                ->setStatusCode(404)
                ->setJSON(['message' => 'Комментарий не найден.']);
        }

        if (! $model->delete($id)) {
            return $this->response
                ->setStatusCode(500)
                ->setJSON(['message' => 'Не удалось удалить комментарий.']);
        }

        return $this->response->setJSON(['message' => 'Комментарий удалён.']);
    }
}

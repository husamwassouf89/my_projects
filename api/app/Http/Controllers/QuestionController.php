<?php

namespace App\Http\Controllers;

use App\Http\Requests\Question\QuestionRequest;
use App\Http\Requests\Question\UpdateQuestionRequest;
use App\Services\QuestionService;
use \Illuminate\Http\JsonResponse;

class QuestionController extends Controller
{
    public function __construct(QuestionService $service)
    {
        $this->service = $service;
    }

    public function store(QuestionRequest $request): JsonResponse
    {
        return $this->response('success', $this->service->store($request->validated()));
    }

    public function update($id, UpdateQuestionRequest $request): JsonResponse
    {
        return $this->response('success', $this->service->update($id, $request->validated()));
    }

    public function show($id): JsonResponse
    {
        return $this->response('success', $this->service->show($id));
    }

    public function destroy($id): JsonResponse
    {
        return $this->response('success', $this->service->delete($id));
    }
}

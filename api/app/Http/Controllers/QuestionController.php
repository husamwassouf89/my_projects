<?php

namespace App\Http\Controllers;

use App\Http\Requests\Question\QuestionRequest;
use App\Http\Requests\Question\UpdateQuestionRequest;
use App\Services\QuestionService;

class QuestionController extends Controller
{

    public function __construct(QuestionService $service)
    {
        $this->service = $service;
    }

    public function store(QuestionRequest $request)
    {
        return $this->response('success', $this->service->store($request->validated()));
    }

    public function update($id, UpdateQuestionRequest $request)
    {
        return $this->response('success', $this->service->update($id, $request->validated()));
    }

    public function show($id)
    {
        return $this->response('success', $this->service->show($id));
    }

    public function destroy($id)
    {
        return $this->response('success', $this->service->delete($id));
    }
}

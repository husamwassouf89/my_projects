<?php

namespace App\Http\Controllers;

use App\Http\Requests\Question\QuestionRequest;
use App\Http\Requests\Question\UpdateQuestionRequest;
use App\Models\IRS\Question;
use App\Services\QuestionService;
use Illuminate\Http\Request;

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

    public function destroy($id)
    {
        return $this->response('success', $this->service->delete($id));
    }
}

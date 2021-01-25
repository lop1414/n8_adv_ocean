<?php

namespace App\Http\Controllers\Admin\SubTask;

use App\Models\Task\TaskOceanVideoUploadModel;
use Illuminate\Http\Request;

class TaskOceanVideoUploadController extends SubTaskOceanController
{
    /**
     * constructor.
     */
    public function __construct()
    {
        $this->model = new TaskOceanVideoUploadModel();

        parent::__construct();
    }
}

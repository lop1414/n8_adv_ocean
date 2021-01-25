<?php

namespace App\Http\Controllers\Admin\SubTask;

use App\Models\Task\TaskOceanImageUploadModel;
use Illuminate\Http\Request;

class TaskOceanImageUploadController extends SubTaskOceanController
{
    /**
     * constructor.
     */
    public function __construct()
    {
        $this->model = new TaskOceanImageUploadModel();

        parent::__construct();
    }
}

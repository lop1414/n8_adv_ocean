<?php

namespace App\Http\Controllers\Admin\SubTask;

use App\Models\Task\TaskOceanAdCreativeCreateModel;
use Illuminate\Http\Request;

class TaskOceanAdCreativeCreateController extends SubTaskOceanController
{
    /**
     * constructor.
     */
    public function __construct()
    {
        $this->model = new TaskOceanAdCreativeCreateModel();

        parent::__construct();
    }
}

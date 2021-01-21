<?php

namespace App\Http\Controllers\Admin\SubTask;

use App\Models\Task\TaskOceanSyncModel;
use Illuminate\Http\Request;

class TaskOceanSyncController extends SubTaskOceanController
{
    /**
     * constructor.
     */
    public function __construct()
    {
        $this->model = new TaskOceanSyncModel();

        parent::__construct();
    }
}

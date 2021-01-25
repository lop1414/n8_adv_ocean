<?php

namespace App\Http\Controllers\Admin\Ocean;

use App\Models\Ocean\OceanAdConvertModel;
use Illuminate\Http\Request;

class AdConvertController extends OceanController
{
    /**
     * constructor.
     */
    public function __construct()
    {
        $this->model = new OceanAdConvertModel();

        parent::__construct();
    }
}

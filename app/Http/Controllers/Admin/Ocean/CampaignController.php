<?php

namespace App\Http\Controllers\Admin\Ocean;

use App\Common\Tools\CustomException;
use App\Models\Ocean\OceanCampaignModel;
use App\Services\Ocean\OceanService;
use Illuminate\Http\Request;

class CampaignController extends OceanController
{
    /**
     * constructor.
     */
    public function __construct()
    {
        $this->model = new OceanCampaignModel();

        parent::__construct();
    }
}

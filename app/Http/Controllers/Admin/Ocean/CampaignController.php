<?php

namespace App\Http\Controllers\Admin\Ocean;

use App\Common\Controllers\Admin\AdminController;
use App\Common\Tools\CustomException;
use App\Models\Ocean\OceanAccountModel;
use App\Models\Ocean\OceanCampaignModel;
use Illuminate\Http\Request;

class CampaignController extends AdminController
{
    /**
     * constructor.
     */
    public function __construct()
    {
        $this->model = new OceanCampaignModel();

        parent::__construct();
    }

    public function sync(Request $request){
        $this->validRule($request->post(), [
            'account_ids' => 'required|array'
        ]);

        $accountIds = $request->post('account_ids');

        $oceanAccountModel = new OceanAccountModel();
        $accounts = $oceanAccountModel->whereIn('account_id', $accountIds)->get();

        dd($accounts);
    }
}

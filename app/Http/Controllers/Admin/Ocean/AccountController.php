<?php

namespace App\Http\Controllers\Admin\Ocean;

use App\Common\Enums\StatusEnum;
use App\Common\Helpers\Functions;
use App\Common\Services\SystemApi\CenterApiService;
use App\Common\Tools\CustomException;
use App\Models\Ocean\OceanAccountModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class AccountController extends OceanController
{
    /**
     * @var string
     * 默认排序字段
     */
    protected $defaultOrderBy = 'account_id';

    /**
     * constructor.
     */
    public function __construct()
    {
        $this->model = new OceanAccountModel();

        parent::__construct();
    }

    /**
     * 分页列表预处理
     */
    public function selectPrepare(){
        parent::selectPrepare();

        $this->curdService->selectQueryBefore(function(){
            $this->curdService->customBuilder(function($builder){
                $this->filter();

                // 时间范围
                $startDate = $this->curdService->requestData['start_date'] ?? date('Y-m-d');
                $endDate = $this->curdService->requestData['end_date'] ?? date('Y-m-d');
                Functions::dateCheck($startDate);
                Functions::dateCheck($endDate);

                $report = DB::table('ocean_account_reports')
                    ->whereBetween('stat_datetime', ["{$startDate} 00:00:00", "{$endDate} 23:59:59"])
                    ->select(DB::raw("
                        account_id report_account_id,
                        ROUND(SUM(`cost` / 100), 2) `cost`,
                        SUM(`click`) `click`,
                        SUM(`show`) `show`,
                        SUM(`convert`) `convert`,
                        ROUND(SUM(`cost` / 100) / SUM(`show`) * 1000, 2) `show_cost`,
                        ROUND(SUM(`cost` / 100) / SUM(`click`), 2) `click_cost`,
                        ROUND(SUM(`click`) / SUM(`show`), 4) `click_rate`,
                        ROUND(SUM(`cost` / 100) / SUM(`convert`), 2) `convert_cost`,
                        ROUND(SUM(`convert`) / SUM(`click`), 4) `convert_rate`
                    "))
                    ->groupBy('account_id');

                $builder->LeftjoinSub($report, 'report', function($join){
                    $join->on('ocean_accounts.account_id', '=', 'report.report_account_id');
                });
            });
        });

        $this->curdService->selectQueryAfter(function(){
            // 获取管理员列表
            $centerApiService = new CenterApiService();
            $adminUsers = $centerApiService->apiGetAdminUsers();

            // 映射
            $adminUserMap = array_column($adminUsers, null, 'id');

            foreach($this->curdService->responseData['list'] as $k => $v){
                $this->curdService->responseData['list'][$k]['admin_name'] = isset($adminUserMap[$v->admin_id]) ? $adminUserMap[$v->admin_id]['name'] : '';
                $this->curdService->responseData['list'][$k]['roi_callback_status'] = $v->extend->roi_callback_status ?? StatusEnum::DISABLE;
            }
        });
    }

    /**
     * 列表预处理
     */
    public function getPrepare(){
        parent::getPrepare();

        $this->curdService->getQueryBefore(function(){
            $this->filter();
        });
    }

    /**
     * 过滤
     */
    private function filter(){
        $this->curdService->customBuilder(function($builder){
            // 关键词
            $keyword = $this->curdService->requestData['keyword'] ?? '';
            if(!empty($keyword)){
                $builder->whereRaw("(account_id LIKE '%{$keyword}%' OR name LIKE '%{$keyword}%')");
            }


            $builder->when($this->curdService->requestData['roi_callback_status'],function ($query,$roiCallbackStatus){
                return $query->where('roi_callback_status',$roiCallbackStatus);
            });


            $builder->where('parent_id', '<>', 0);
        });
    }

    /**
     * 更新预处理
     */
    public function updatePrepare(){
        $this->curdService->addField('name')->addValidRule('required|max:16|min:2');
        $this->curdService->addField('admin_id')->addValidRule('required');

        $this->curdService->saveBefore(function (){
            $this->model->existWithoutSelf('name',$this->curdService->handleData['name'],$this->curdService->handleData['id']);

            // 验证admin id
            $adminInfo = (new CenterApiService())->apiReadAdminUser($this->curdService->handleData['admin_id']);
            if($adminInfo['status'] != StatusEnum::ENABLE){
                throw new CustomException([
                    'code' => 'ADMIN_DISABLE',
                    'message' => '该后台用户已被禁用'
                ]);
            }
        });

        // 限制修改的字段
        $this->curdService->handleAfter(function (){
            foreach($this->curdService->handleData as $field => $val){
                if(!in_array($field,['name','admin_id','id'])){
                    unset($this->curdService->handleData[$field]);
                }
            }
        });
    }

    /**
     * @param Request $request
     * @return mixed
     * @throws CustomException
     * 批量更新管理员
     */
    public function batchUpdateAdmin(Request $request){
        $requestData = $request->post();
        $this->validRule($requestData, [
            'ids' => 'required|array',
            'admin_id' => 'required',
        ]);

        $oceanAccountModel = new OceanAccountModel();
        $builder = $oceanAccountModel->whereIn('id', $requestData['ids']);

        if($builder->count() == 0){
            throw new CustomException([
                'code' => 'NOT_FOUND_ACCOUNT',
                'message' => '找不到对应账户',
            ]);
        }

        $builder->update(['admin_id' => $requestData['admin_id']]);

        return $this->success();
    }





    /**
     * @param Request $request
     * @return mixed
     *  启用 roi回传
     */
    public function roiCallbackEnable(Request $request){
        return $this->updateRoiCallbackStatus($request,StatusEnum::ENABLE);
    }


    /**
     * @param Request $request
     * @return mixed
     *  启用 roi回传
     */
    public function roiCallbackDisable(Request $request){
        return $this->updateRoiCallbackStatus($request,StatusEnum::DISABLE);
    }


    /**
     * @param Request $request
     * @param $status
     * @return mixed
     * 更新 roi回传状态
     */
    public function updateRoiCallbackStatus(Request $request,$status){
        $requestData = $request->all();
        // 查找
        $model = $this->model->find($requestData['id']);

        $model->roi_callback_status = $status;

        $ret = $model->save();

        // 清除 model data
        $ret && $this->clearModelData($requestData);

        return $this->ret($ret);
    }
}

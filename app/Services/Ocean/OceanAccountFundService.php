<?php

namespace App\Services\Ocean;

use App\Common\Tools\CustomException;
use App\Models\Ocean\OceanAccountFundModel;
use App\Models\Ocean\OceanAccountModel;

class OceanAccountFundService extends OceanService
{
    /**
     * OceanVideoService constructor.
     * @param string $appId
     */
    public function __construct($appId = ''){
        parent::__construct($appId);
    }

    /**
     * @param $option
     * @return bool
     * @throws CustomException
     * 同步
     */
    public function sync($option = []){
        $accountIds = [];

        // 账户id过滤
        if(!empty($option['account_ids'])){
            $accountIds = $option['account_ids'];
        }

        if(!empty($option['has_history_cost'])){
            // 历史消耗
            $accountIds = $this->getHasHistoryCostAccount($accountIds);
        }

        $builder = new OceanAccountModel();

        if(!empty($accountIds)){
            $builder = $builder->whereIn('account_id', $accountIds);
        }

        $oceanAccounts = $builder->get();

        $datetime = date('Y-m-d H:i:s');

        $data = [];
        foreach($oceanAccounts as $oceanAccount){
            $this->setAppId($oceanAccount->app_id);
            $this->setAccountId($oceanAccount->account_id);
            $this->setAccessToken();

            $item = $this->sdk->getAccountFund($oceanAccount->account_id);

            $data[] = [
                'account_id' => $oceanAccount->account_id,
                'balance' => $item['balance'] * 100,
                'valid_balance' => $item['valid_balance'] * 100,
                'cash' => $item['cash'] * 100,
                'valid_cash' => $item['valid_cash'] * 100,
                'grant' => $item['grant'] * 100,
                'valid_grant' => $item['valid_grant'] * 100,
                'extends' => json_encode($item),
                'created_at' => $datetime,
                'updated_at' => $datetime,
            ];
        }

        $this->batchSave($data);

        return true;
    }

    /**
     * @param $data
     * @return bool
     * 批量保存
     */
    public function batchSave($data){
        $oceanAccountFundModel = new OceanAccountFundModel();
        $oceanAccountFundModel->chunkInsertOrUpdate($data, 50, $oceanAccountFundModel->getTable(), $oceanAccountFundModel->getTableColumnsWithPrimaryKey());
        return true;
    }
}

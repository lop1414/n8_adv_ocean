<?php

namespace App\Services\Ocean;

use App\Common\Helpers\Functions;
use App\Common\Tools\CustomException;
use App\Models\Ocean\OceanAccountFundDailyStatModel;
use App\Models\Ocean\OceanAccountModel;

class OceanAccountFundDailyStatService extends OceanService
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
        $builder = $builder->enable();

        if(!empty($accountIds)){
            $builder = $builder->whereIn('account_id', $accountIds);
        }

        $oceanAccounts = $builder->get();

        $datetime = date('Y-m-d H:i:s');

        $dateRange = Functions::getDateRange($option['date']);
        $dateList = Functions::getDateListByRange($dateRange);

        foreach($dateList as $date){
            $data = [];
            foreach($oceanAccounts as $oceanAccount){
                $this->setAppId($oceanAccount->app_id);
                $this->setAccountId($oceanAccount->account_id);
                $this->setAccessToken();

                $ret = $this->sdk->getAccountFundDailyStat($oceanAccount->account_id, $date, $date);

                $item = current($ret['list']);

                if($item['cost'] <= 0){
                    continue;
                }

                $data[] = [
                    'account_id' => $oceanAccount->account_id,
                    'stat_datetime' => "{$item['date']} 00:00:00",
                    'balance' => $item['balance'] * 100,
                    'cash_cost' => $item['cash_cost'] * 100,
                    'frozen' => $item['frozen'] * 100,
                    'income' => $item['income'] * 100,
                    'reward_cost' => $item['reward_cost'] * 100,
                    'shared_wallet_cost' => $item['shared_wallet_cost'] * 100,
                    'transfer_in' => $item['transfer_in'] * 100,
                    'transfer_out' => $item['transfer_out'] * 100,
                    'extends' => json_encode($item),
                    'created_at' => $datetime,
                    'updated_at' => $datetime,
                ];
                sleep(1);
            }

            $this->batchSave($data);
        }

        return true;
    }

    /**
     * @param $data
     * @return bool
     * 批量保存
     */
    public function batchSave($data){
        $oceanAccountFundDailyStatModel = new OceanAccountFundDailyStatModel();
        $oceanAccountFundDailyStatModel->chunkInsertOrUpdate($data, 50, $oceanAccountFundDailyStatModel->getTable(), $oceanAccountFundDailyStatModel->getTableColumns());
        return true;
    }
}

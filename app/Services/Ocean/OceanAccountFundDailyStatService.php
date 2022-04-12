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
     * @param $accountIds
     * @param $accessToken
     * @param $filtering
     * @param $page
     * @param $pageSize
     * @param array $param
     * @return mixed|void
     * sdk批量获取列表
     */
    public function sdkMultiGetList($accountIds, $accessToken, $filtering, $page, $pageSize, $param = []){
        return $this->sdk->multiGetAccountFundDailyStat($accountIds, $accessToken, $filtering, $page, $pageSize, $param);
    }

    /**
     * @param $option
     * @return bool
     * @throws CustomException
     * 同步
     */
    public function sync($option = []){

        // 并发分片大小
        if(!empty($option['multi_chunk_size'])){
            $this->sdk->setMultiChunkSize(2);
        }

        $datetime = date('Y-m-d H:i:s');

        $dateRange = Functions::getDateRange($option['date']);
        $dateList = Functions::getDateListByRange($dateRange);

        foreach($dateList as $date){
            $accountIds = [];

            // 账户id过滤
            if(!empty($option['account_ids'])){
                $accountIds = $option['account_ids'];
            }

            if(!empty($option['has_history_cost'])){
                // 历史消耗
                $accountIds = $this->getHasHistoryCostAccount($accountIds, $date);
            }

            // 获取子账户组
            $accountGroup = $this->getSubAccountGroup($accountIds);

            $param = [
                'start_date' => $date,
                'end_date' => $date,
            ];

            $pageSize = 200;
            foreach($accountGroup as $g){
                $items = $this->multiGetPageList($g, [], $pageSize, $param);

                Functions::consoleDump('count:'. count($items));

                $cost = 0;

                // 保存
                $data = [];
                foreach($items as $item) {
                    $cost += $item['cost'];

                    if($item['cost'] <= 0){
                        continue;
                    }

                    $data[] = [
                        'account_id' => $item['advertiser_id'],
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
                }

                // 批量保存
                $this->batchSave($data);

                Functions::consoleDump('cost:'. $cost);
            }
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

<?php

namespace App\Services;

use App\Common\Services\BaseService;
use App\Common\Tools\CustomException;
use Illuminate\Support\Facades\DB;

class SyncDatabaseService extends BaseService
{
    /**
     * @var array
     * 授权表
     */
    protected $accessTables = [
        'ocean_accounts',
        'ocean_account_funds',
    ];

    /**
     * constructor.
     */
    public function __construct(){
        parent::__construct();
    }

    /**
     * @param $param
     * @return bool
     * @throws CustomException
     * 执行
     */
    public function run($param){
        $table = $param['table'] ?? '';
        if(!in_array($table, $this->accessTables)){
            throw new CustomException([
                'code' => 'THIS_TABLE_NOT_IN_ACCESS_TABLES',
                'message' => '该表不在允许范围内',
                'data' => [
                    'table' => $table,
                    'access_tables' => $this->accessTables,
                ],
            ]);
        }

        $sql = "REPLACE INTO n8_adv_ocean_v2.{$table} select * from n8_adv_ocean.{$table}";
        $this->execSql($sql);

        return true;
    }

    /**
     * @param $sql
     * @return bool
     * 执行sql
     */
    public function execSql($sql){
        var_dump($sql);
        DB::select($sql);
        return true;
    }
}

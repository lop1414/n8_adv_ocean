<?php

namespace App\Console\Commands;

use App\Common\Console\BaseCommand;
use App\Common\Models\ConvertCallbackModel;

class TestCommand extends BaseCommand
{
    /**
     * 命令行执行命令
     * @var string
     */
    protected $signature = 'test';

    /**
     * 命令描述
     *
     * @var string
     */
    protected $description = '测试';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(){
        parent::__construct();
    }

    /**
     * @throws \App\Common\Tools\CustomException
     * 处理
     */
    public function handle(){
        $this->reflashConvertCallback();
        dd('done');
    }

    /**
     * 刷新转化回传
     */
    public function reflashConvertCallback(){
        $convertCallbackModel = new ConvertCallbackModel();
        do{
            $convertCallbacks = $convertCallbackModel->where('updated_at', '<', '2021-07-22 11:00:00')->orderBy('id', 'asc')->take(5000)->get();
            var_dump($convertCallbacks->count());
            foreach($convertCallbacks as $convertCallback){
                if(empty($convertCallback->extends->convert)){
                    $extends = [
                        'convert' => $convertCallback->extends,
                    ];
                    $convertCallback->extends = $extends;
                    $convertCallback->save();
                    var_dump($convertCallback->id);
                }
            }
        }while($convertCallbacks->count() > 0);
    }
}

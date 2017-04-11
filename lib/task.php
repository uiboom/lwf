<?php
/**
 *
 * Created by PhpStorm.
 * User: lejianwen
 * Date: 2017/3/14
 * Time: 17:23
 * QQ: 84855512
 */
namespace lib;

use lib\traits\message;
use server\websocket;

/**任务运行
 * Class task
 * @package lib
 */
class task
{
    use message;
    protected $frame;
    protected $data;

    public function __construct($data)
    {
        $this->frame = $data['frame'];
        $this->data = $data['data'];
    }

    /**push信息
     * @param $fd
     * @param $data
     */
    protected function _push($fd, $data)
    {
        server()->push($fd, self::encode($data));
    }

}
<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019-06-27
 * Time: 16:59
 */

namespace OppoPush;

use OppoPush\Http\Http;
use OppoPush\Http\Request;
use OppoPush\Http\Response;


class OppoPush
{

    /**
     * 构造函数。
     *
     * @param array $config
     * @throws \Exception
     */
    public function __construct($appId, $appKey, $appSecret, $logFile)
    {
        $this->appId = $appId;
        $this->appKey = $appKey;
        $this->appSecret = $appSecret;
        $this->logFile = $logFile;
        $this->AccessToken = '';
        $this->message_id = '';

        $this->app_message_id = '';

        $this->title = '';
        $this->sub_title = '';
        $this->content = '';
        $this->click_action_type = 0;
        $this->click_action_activity = '';
        $this->click_action_url = '';
        $this->action_parameters = '';
        $this->show_time_type = 0;
        $this->show_start_time = '';
        $this->show_end_time = '';
        $this->off_line = true;
        $this->off_line_ttl = 60 * 60 * 6;
        $this->push_time_type = 0;
        $this->push_start_time = '';
        $this->time_zone = '';
        $this->fix_speed = '';
        $this->fix_speed_rate = '';
        $this->network_type = 0;
        $this->call_back_url = '';
        $this->call_back_parameter = '';


        $this->url = 'https://api.push.oppomobile.com';
    }


    /**
     * 获取AccessToken信息
     * @return mixed
     * @throws \Exception
     */
    public function getAccessToken()
    {
        $sendData = [
            'appKey' => $this->appKey,
            'appId' => $this->appId,
            'timestamp' => time() . '000',
        ];
        $sign = md5($sendData['appKey'] . $sendData['timestamp'] . $this->appSecret);
        $sendData['sign'] = $sign;
        $url = $this->url . '/server/v1/auth';
        $data = $this->getDataByInfo($url, $sendData);
        if (is_array($data) && isset($data['authToken']) && $data['authToken']) {
            $this->AccessToken = $data['authToken'];
        }
        return $this;
    }

    /*
     * 保存通知栏消息内容体
     */
    public function saveMessageContent()
    {
        $this->check();
        $url = $this->url . '/server/v1/message/notification/save_message_content';

        $sendArr = $this->getMsgArr();

        $data = $this->getDataByInfo($url, $sendArr, $this->AccessToken);
        if (is_array($data) && isset($data['data']['message_id']) && $data['data']['message_id']) {
            $this->message_id = $data['message_id'];
        }
        return $data;
    }

    /**
     * 广 播推送-通知栏消息
     * @return mixed|string
     * @throws \Exception
     */
    public function broadcast()
    {
        $this->saveMessageContent();
        if (!$this->message_id) {
            file_put_contents($this->logFile, json_encode(['errot' => 'OPPO推送必须要设置message_id', 'dateTime' => date("Y-m-d H:i:s")]) . "\r\n", FILE_APPEND);
            throw new \Exception("OPPO推送必须要设置message_id");
        }
        $sendArr = [
            'message_id' => $this->message_id,
            'target_type' => 1,
        ];
        $url = $this->url . '/server/v1/message/notification/broadcast';
        $data = $this->getDataByInfo($url, $sendArr, $this->AccessToken);
        return $data;
    }

    /**
     * 单推也使用此接口
     * 批量单推-通知栏消息推送(
     */
    public function unicast_batch($ridTokens)
    {
        $this->saveMessageContent();
        if (!$this->message_id) {
            file_put_contents($this->logFile, json_encode(['errot' => 'OPPO推送必须要设置message_id', 'dateTime' => date("Y-m-d H:i:s")]) . "\r\n", FILE_APPEND);
            throw new \Exception("OPPO推送必须要设置message_id");
        }
        $sendArr = [
            'message_id' => $this->message_id,
            'target_type' => 2,
            'target_value' => implode(',',$ridTokens),
            'notification' => json_encode($this->getMsgArr()),
        ];
        $url = $this->url . '/server/v1/message/notification/unicast_batch';
        $data = $this->getDataByInfo($url, $sendArr, $this->AccessToken);
        return $data;
    }

    /**
     * 消息内容
     * @return array
     */
    public function getMsgArr()
    {
        $sendArr = [];

        if ($this->title !== '') $sendArr['title'] = $this->title;
        if ($this->sub_title !== '') $sendArr['sub_title'] = $this->sub_title;
        if ($this->content !== '') $sendArr['content'] = $this->content;
        if ($this->click_action_type !== '') $sendArr['click_action_type'] = $this->click_action_type;
        if ($this->click_action_activity !== '') $sendArr['click_action_activity'] = $this->click_action_activity;
        if ($this->click_action_url !== '') $sendArr['click_action_url'] = $this->click_action_url;
        if ($this->action_parameters !== '') $sendArr['action_parameters'] = $this->action_parameters;
        if ($this->show_time_type !== '') $sendArr['show_time_type'] = $this->show_time_type;
        if ($this->show_start_time !== '') $sendArr['show_start_time'] = $this->show_start_time;
        if ($this->show_end_time !== '') $sendArr['show_end_time'] = $this->show_end_time;
        if ($this->off_line !== '') $sendArr['off_line'] = $this->off_line;
        if ($this->off_line_ttl !== '') $sendArr['off_line_ttl'] = $this->off_line_ttl;
        if ($this->push_time_type !== '') $sendArr['push_time_type'] = $this->push_time_type;
        if ($this->push_start_time !== '') $sendArr['push_start_time'] = $this->push_start_time;
        if ($this->time_zone !== '') $sendArr['time_zone'] = $this->time_zone;
        if ($this->fix_speed !== '') $sendArr['fix_speed'] = $this->fix_speed;
        if ($this->fix_speed_rate !== '') $sendArr['fix_speed_rate'] = $this->fix_speed_rate;
        if ($this->network_type !== '') $sendArr['network_type'] = $this->network_type;
        if ($this->call_back_url !== '') $sendArr['call_back_url'] = $this->call_back_url;
        if ($this->call_back_parameter !== '') $sendArr['call_back_parameter'] = $this->call_back_parameter;

        return $sendArr;
    }


    /**
     * 参数检查
     * @throws \Exception
     */
    private function check()
    {
        if ($this->AccessToken) {
            $this->getAccessToken();
        }
        $this->app_message_id = $this->randCode();

        if (!$this->AccessToken) {
            file_put_contents($this->logFile, json_encode(['errot' => 'OPPO推送必须要设置AccessToken', 'dateTime' => date("Y-m-d H:i:s")]) . "\r\n", FILE_APPEND);
            throw new \Exception("OPPO推送必须要设置AccessToken");
        }
        if (!$this->title) {
            file_put_contents($this->logFile, json_encode(['errot' => 'OPPO推送必须要设置title', 'dateTime' => date("Y-m-d H:i:s")]) . "\r\n", FILE_APPEND);
            throw new \Exception("OPPO推送必须要设置title");
        }
        if (!$this->sub_title) {
            file_put_contents($this->logFile, json_encode(['errot' => 'OPPO推送必须要设置sub_title', 'dateTime' => date("Y-m-d H:i:s")]) . "\r\n", FILE_APPEND);
            throw new \Exception("OPPO推送必须要设置sub_title");
        }
        if (!$this->content) {
            file_put_contents($this->logFile, json_encode(['errot' => 'OPPO推送必须要设置content', 'dateTime' => date("Y-m-d H:i:s")]) . "\r\n", FILE_APPEND);
            throw new \Exception("OPPO推送必须要设置content");
        }

        return $this;
    }

    /**
     * 设置在通知栏展示的通知栏标题, 【字
     * 数限制 1~32， 中英文均以一个计算】
     * @param string $title
     * @return $this
     */
    public function setTitle($title = "")
    {
        $this->title = $title;
        $this->setsubTitle($this->subtext($title, 10));
        return $this;
    }

    /**
     * 子标题 设置在通知栏展示的通知栏标
     * 题, 【字数限制 1~10， 中英文均以一个
     * 计算】
     * @param string $subTitle
     * @return $this
     */
    public function setsubTitle($subTitle = "")
    {
        $this->sub_title = $subTitle;
        return $this;
    }

    /**
     * 设置在通知栏展示的通知的内容,【必
     * 填， 字数限制 200 以内， 中英文均以一
     * 个计算】
     * @param string $content
     * @return $this
     */
    public function setcontent($content = "")
    {
        $this->content = $content;
        return $this;
    }

    /**
     * 点击动作类型 0， 启动应用； 1， 打开应
     * 用内页（activity 的 intent action）； 2，
     * 打开网页； 4， 打开应用内页（activity）；
     * 【非必填， 默认值为 0】 ;5,Intent
     * scheme URL
     * @param string $click_action_type
     * @return $this
     */
    public function setclick_action_type($click_action_type = 0)
    {
        $this->click_action_type = $click_action_type;
        return $this;
    }

    /**
     * 应用内页地址【click_action_type 为
     * 1 或 4 时必填， 长度 500】
     * <activity
     * android:name="com.coloros.push.de
     * mo.component.InternalActivity">
     * <intent-filter>
     * <action
     * android:name="com.coloros.push.de
     * mo.internal" />
     * <category
     * android:name="android.intent.cate
     * gory.DEFAULT" />
     * </intent-filter>
     * </activity>
     * click_action_type 为 1 时这里填写
     * com.coloros.push.demo.internal
     * click_action_type 为 4 时这里填写：
     * 是com.coloros.push.demo.component.I
     * nternalActivity
     * @param string $click_action_activity
     * @return $this
     */
    public function setclick_action_activity($click_action_activity = "")
    {
        $this->click_action_activity = $click_action_activity;
        return $this;
    }

    /**
     * 网页地址【click_action_type 为 2 必
     * 填， 长度 500】
     * @param string $click_action_url
     * @return $this
     */
    public function setclick_action_url($click_action_url = "")
    {
        $this->click_action_url = $click_action_url;
        return $this;
    }

    /**
     * 动作参数， 打开应用内页或网页时传递
     * 给应用或网页【JSON 格式， 非必填】，
     * 字符数不能超过 4K， 示例：
     * {"key1":"value1","key2":"value2"}
     * @param string $action_parameters
     * @return $this
     */
    public function setaction_parameters($action_parameters = "")
    {
        if (is_array($action_parameters)) {
            $this->action_parameters = json_encode($action_parameters);
        } else {
            $this->action_parameters = $action_parameters;
        }
        return $this;
    }

    /**
     * 展示类型 (0, “即时” ),(1, “定
     * 时” )
     * @param int $show_time_type
     * @return $this
     */
    public function setshow_time_type($show_time_type = 1)
    {
        $this->show_time_type = $show_time_type;
        return $this;
    }

    /**
     * 定时展示开始时间（根据 time_zone 转
     * 换成当地时间）， 时间的毫秒数
     * @param string $show_start_time
     * @return $this
     */
    public function setshow_start_time($show_start_time = "")
    {
        $this->show_start_time = $show_start_time;
        return $this;
    }

    /**
     * 定时展示结束时间（根据 time_zone 转
     * 换成当地时间）， 时间的毫秒数
     * @param string $show_end_time
     * @return $this
     */
    public function setshow_end_time($show_end_time = "")
    {
        $this->show_end_time = $show_end_time;
        return $this;
    }

    /**
     * 是否进离线消息,【非必填， 默认为
     * True】
     * @param bool $off_line
     */
    public function setoff_line($off_line = true)
    {
        $this->off_line = $off_line;
    }

    /**
     * 离线消息的存活时间(time_to_live)
     * (单位： 秒), 【off_line 值为 true 时，
     * 必填， 最长 3 天】
     * @param int $off_line_ttl
     * @return $this
     */
    public function setoff_line_ttl($off_line_ttl = 60 * 60 * 6)
    {
        $this->off_line_ttl = $off_line_ttl;
        return $this;
    }

    /**
     * 定时推送 (0, “即时” ),(1, “定
     * 时” ), 【只对全部用户推送生效】
     * @param int $push_time_type
     * @return $this
     */
    public function setpush_time_type($push_time_type = 0)
    {
        $this->push_time_type = $push_time_type;
        return $this;
    }

    /**
     * 定时推送开始时间（根据 time_zone 转
     * 换成当地时间）, 【push_time_type 为
     * 1 必填】， 时间的毫秒数
     * @param string $push_start_time
     * @return $this
     */
    public function setpush_start_time($push_start_time = "")
    {
        $this->push_start_time = $push_start_time;
        return $this;
    }

    /**
     * 时区， 默认值：（GMT+08:00） 北京， 香
     * 港， 新加坡
     * @param string $time_zone
     * @return $this
     */
    public function settime_zone($time_zone = "GMT+08:00")
    {
        $this->time_zone = $time_zone;
        return $this;
    }

    /**
     * 是否定速推送,【非必填， 默认值为
     * false】
     * @param string $fix_speed
     * @return $this
     */
    public function setfix_speed($fix_speed = "")
    {
        $this->fix_speed = $fix_speed;
        return $this;
    }

    /**
     * 定速速率【fixSpeed 为 true 时， 必填】
     * @param string $ix_speed_rate
     * @return $this
     */
    public function setix_speed_rate($ix_speed_rate = "")
    {
        $this->fix_speed_rate = $ix_speed_rate;
        return $this;
    }

    /**
     * 0： 不限联网方式, 1： 仅 wifi 推送；
     * @param int $network_type
     * @return $this
     */
    public function setnetwork_type($network_type = 0)
    {
        $this->network_type = $network_type;
        return $this;
    }

    /**
     * *仅支持 registrationId 或 aliasName
     * 两种推送方式----------太长，请查看文档
     * @param string $call_back_url
     * @return $this
     */
    public function setcall_back_url($call_back_url = "")
    {
        $this->call_back_url = $call_back_url;
        return $this;
    }

    /**
     * App 开发者自定义回执参数， 字数限制
     * 50 以内， 中英文均以一个计算。
     * @param string $call_back_parameter
     * @return $this
     */
    public function setcall_back_parameter($call_back_parameter = "")
    {
        $this->call_back_parameter = $call_back_parameter;
        return $this;
    }


    /**
     * @param $url
     * @param $sendData
     * @return mixed|string
     */
    public function getDataByInfo($url, $sendData = [], $authToken = '')
    {
        $this->_http = new Request();
        $this->_http->setHttpVersion(Http::HTTP_VERSION_1_1);

        //urlencode
        $option = [
            'data' => json_encode($sendData),
            'headers' => [
                "Content-Type" => "application/x-www-form-urlencoded",
            ]
        ];
        if ($authToken) {
            $sendData['auth_token'] = $authToken;
        }
        try {
            $response = $this->_http->post($url, $option);
            $logRes = $res = $response->getResponseArray();
        } catch (\Exception $e) {
            $logRes = $e->getMessage();
            $res = '';
        }
        $option['data'] = $sendData;
        file_put_contents($this->logFile, json_encode(['url' => $url, 'option' => $option, 'res' => $logRes, 'dateTime' => date("Y-m-d H:i:s")]) . "\r\n", FILE_APPEND);
        return $res;
    }

    /**
     * 生成随机字符串
     * @param int $length 要生成的随机字符串长度
     * @param string $type 随机码类型：0，数字+大小写字母；1，数字；2，小写字母；3，大写字母；4，特殊字符；-1，数字+大小写字母+特殊字符
     * @return string
     */
    public function randCode($length = 32, $type = 0)
    {
        $arr = array(1 => "0123456789", 2 => "abcdefghijklmnopqrstuvwxyz", 3 => "ABCDEFGHIJKLMNOPQRSTUVWXYZ", 4 => "~@#$%^&*(){}[]|");
        if ($type == 0) {
            array_pop($arr);
            $string = implode("", $arr);
        } elseif ($type == "-1") {
            $string = implode("", $arr);
        } else {
            $string = $arr[$type];
        }
        $count = strlen($string) - 1;
        $code = '';
        for ($i = 0; $i < $length; $i++) {
            $code .= $string[rand(0, $count)];
        }
        return $code;
    }

    /**
     * 截取中文字符串
     * @param $text
     * @param $length
     * @return string
     */
    public static function subtext($text, $length)
    {
        if (mb_strlen($text, 'utf8') > $length)
            return mb_substr($text, 0, $length, 'utf8');
        return $text;
    }
}
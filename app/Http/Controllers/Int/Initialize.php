<?php

namespace App\Http\Controllers\Int;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class Initialize extends Controller
{

    /*
     * 适用于mysql5.6 5.7版本
     * */
    public function createDBTable()
    {
        $sql['member'] = '
        CREATE TABLE IF NOT EXISTS `member` (
          `user_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
          `permission` tinyint(3) DEFAULT NULL,
          `username` varchar(30) NOT NULL,
          `password` varchar(64) NOT NULL,
          `user_group` varchar(30) DEFAULT NULL,
          `remark` text,
          `reg_date` timestamp NOT NULL,
          `email` varchar(50) DEFAULT NOT NULL UNIQUE,
          `update_time` timestamp NOT NULL DEFAULT \'0000-00-00 00:00:00\' ON UPDATE CURRENT_TIMESTAMP,
          `token` varchar(255) DEFAULT NULL,
          `reg_ip` varchar(30),
          PRIMARY KEY (`user_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8';

        $sql['createAdmin'] = '
        INSERT INTO `member` (permission, username, password, user_group, remark, email) 
        VALUES (1, \'admin\', \'8a2a523b38559c1b7df1a6d47b9bdf2c\', \'ultimate\', \'默认管理员账号\')';

        $sql['nodeList'] = '
        CREATE TABLE IF NOT EXISTS `node_list` (
          `zone_id` smallint(4) NOT NULL AUTO_INCREMENT,
          `id` int(11) NOT NULL,
          `zone_name` varchar(20) NOT NULL,
          `description` text,
          `price` int(5) NOT NULL,
          `remark` tinytext,
          `update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          `name` varchar(128) NOT NULL,
          `type` tinyint(3) unsigned NOT NULL,
          `server` varchar(128) NOT NULL,
          `method` varchar(64) NOT NULL,
          `custom_method` tinyint(3) unsigned NOT NULL DEFAULT \'0\',
          `traffic_rate` float NOT NULL DEFAULT \'1\',
          `info` varchar(128) NOT NULL,
          `status` varchar(128) NOT NULL,
          `offset` int(11) NOT NULL DEFAULT \'0\',
          `sort` int(3) NOT NULL,
          `used_port` int(10) unsigned NOT NULL,
          PRIMARY KEY (`zone_id`),
          KEY `ZoneIndex` (`zone_name`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8';

        $sql['order'] = '
        CREATE TABLE IF NOT EXISTS `order` (
        order_id int(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id INT(10) UNSIGNED NOT NULL,
        order_no varchar(30) NOT NULL,
        zone_id SMALLINT UNSIGNED NOT NULL,
        zone_name varchar(20) NOT NULL,
        period tinyint(4) UNSIGNED NOT NULL,
        total_price int(10) NOT NULL,
        password VARCHAR(255) NOT NULL,
        pay_status tinyint(4) UNSIGNED NOT NULL,
        coupon_code varchar(30),
        coupon_discount varchar(30),
        remark tinytext,
        create_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
        update_time TIMESTAMP DEFAULT \'0000-00-00 00:00:00\' ON UPDATE CURRENT_TIMESTAMP
        )ENGINE=InnoDB DEFAULT CHARSET=utf8';

        $sql['usageStatus'] = '
        CREATE TABLE IF NOT EXISTS usage_status (
        id int(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id INT(10) UNSIGNED NOT NULL,
        username VARCHAR(30) NOT NULL,
        zone_name varchar(20) NOT NULL,
        valid_time varchar(255) NOT NULL,
        data_used text,
        status_mark tinyint(4) UNSIGNED NOT NULL,
        remark tinytext,
        create_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
        update_time TIMESTAMP DEFAULT \'0000-00-00 00:00:00\' ON UPDATE CURRENT_TIMESTAMP
        )ENGINE=InnoDB DEFAULT CHARSET=utf8';

        $sql['lmList'] = '
        CREATE TABLE IF NOT EXISTS lm_list (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        lm_id varchar(30) NOT NULL UNIQUE,
        total_period VARCHAR(255) NOT NULL,
        remain_period VARCHAR(255) NOT NULL,
        min_period VARCHAR(255),
        total_number INT UNSIGNED NOT NULL,
        remain_number INT UNSIGNED NOT NULL,
        create_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
        update_time TIMESTAMP DEFAULT \'0000-00-00 00:00:00\' ON UPDATE CURRENT_TIMESTAMP
        )ENGINE=InnoDB DEFAULT CHARSET=utf8';

        $sql['lmListFetched'] = '
        CREATE TABLE IF NOT EXISTS lm_list_fetched (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        lm_id varchar(30) NOT NULL UNIQUE,
        user_id INT UNSIGNED NOT NULL,
        period_fetched INT UNSIGNED NOT NULL,
        zone_fetched VARCHAR(20) NOT NULL,
        fetch_status TINYINT UNSIGNED NOT NULL,
        create_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
        update_time TIMESTAMP DEFAULT \'0000-00-00 00:00:00\' ON UPDATE CURRENT_TIMESTAMP
        )ENGINE=InnoDB DEFAULT CHARSET=utf8';

        $sql['introduction'] = '
        CREATE TABLE IF NOT EXISTS `introduction` (
          `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
          `author_user_id` INT UNSIGNED NOT NULL,
          `title` text,
          `type` TINYINT UNSIGNED NOT NULL,
          `content` longtext NOT NULL,
          `create_time` TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
          `update_time` TIMESTAMP DEFAULT \'0000-00-00 00:00:00\' ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci';

        $sql['invCode'] = '
        CREATE TABLE IF NOT EXISTS inv_code (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        inv_code varchar(30) NOT NULL UNIQUE,
        valid_times SMALLINT UNSIGNED NOT NULL,
        create_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
        update_time TIMESTAMP DEFAULT \'0000-00-00 00:00:00\' ON UPDATE CURRENT_TIMESTAMP
        )ENGINE=InnoDB DEFAULT CHARSET=utf8';

        $sql['stockLog'] = '
        CREATE TABLE IF NOT EXISTS stock_log (
        id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
        order_no varchar(30) NOT NULL,
        zone_id TINYINT UNSIGNED NOT NULL,
        zone_name varchar(20) NOT NULL,
        period TINYINT UNSIGNED NOT NULL,
        remain_period INT UNSIGNED NOT NULL,
        buyer VARCHAR(30) NOT NULL,
        remark VARCHAR(30),
        create_time TIMESTAMP NOT NULL
        )DEFAULT charset=utf8 ENGINE=INNODB;';

        $sql['stock'] = '
        CREATE TABLE IF NOT EXISTS stock_log (
        id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
        order_no varchar(30) NOT NULL,
        zone_id TINYINT UNSIGNED NOT NULL,
        zone_name varchar(20) NOT NULL,
        period TINYINT UNSIGNED NOT NULL,
        remain_period INT UNSIGNED NOT NULL,
        buyer VARCHAR(30) NOT NULL,
        remark VARCHAR(30),
        create_time TIMESTAMP NOT NULL
        )DEFAULT charset=utf8 ENGINE=INNODB;';

        $sql['triggerUpdateStockAndStockLog'] = '
        delimiter $
        CREATE TRIGGER update_stock_and_log
        AFTER INSERT ON `order`
        FOR EACH ROW
        BEGIN
        DECLARE RP INT;
        UPDATE stock SET remain_period = remain_period - new.period WHERE zone_name = new.zone_name;
        SET RP = (SELECT remain_period FROM stock WHERE zone_name = new.zone_name);
        INSERT stock_log (zone_id, zone_name, period, order_no, buyer, remain_period) VALUES (new.zone_id, new.zone_name, new.period, new.order_no, new.user_id, RP);
        END $
        delimiter ;';

        $sql['ss_node'] = '
        CREATE TABLE IF NOT EXISTS `ss_node` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `name` varchar(128) NOT NULL,
          `type` int(3) NOT NULL,
          `server` varchar(128) NOT NULL,
          `method` varchar(64) NOT NULL,
          `custom_method` tinyint(1) NOT NULL DEFAULT \'0\',
          `traffic_rate` float NOT NULL DEFAULT \'1\',
          `info` varchar(128) NOT NULL,
          `status` varchar(128) NOT NULL,
          `offset` int(11) NOT NULL DEFAULT \'0\',
          `sort` int(3) NOT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4';

        $sql['SSNodeInfoLog'] = '
        CREATE TABLE IF NOT EXISTS `ss_node_info_log` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `node_id` int(11) NOT NULL,
          `uptime` float NOT NULL,
          `load` varchar(32) NOT NULL,
          `log_time` int(11) NOT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8';

        $sql['SSNodeOnlineLog'] = '
        CREATE TABLE IF NOT EXISTS `ss_node_online_log` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `node_id` int(11) NOT NULL,
          `online_user` int(11) NOT NULL,
          `log_time` int(11) NOT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8';

        $sql['user'] = '
        CREATE TABLE IF NOT EXISTS `user` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `node_id` int(10) unsigned DEFAULT NULL,
          `node_name` varchar(32) DEFAULT NULL,
          `user_name` varchar(128) CHARACTER SET utf8mb4 NOT NULL,
          `uid` int(10) unsigned NOT NULL,
          `email` varchar(32) NOT NULL DEFAULT \'666@666.com\',
          `pass` varchar(64) NOT NULL DEFAULT \'666@666.com\',
          `passwd` varchar(16) NOT NULL,
          `t` int(11) NOT NULL DEFAULT \'0\',
          `u` bigint(20) NOT NULL DEFAULT \'0\',
          `d` bigint(20) NOT NULL DEFAULT \'0\',
          `transfer_enable` bigint(20) NOT NULL,
          `port` int(11) NOT NULL,
          `protocol` varchar(32) NOT NULL DEFAULT \'origin\',
          `obfs` varchar(32) NOT NULL DEFAULT \'plain\',
          `switch` tinyint(4) NOT NULL DEFAULT \'1\',
          `enable` tinyint(4) NOT NULL DEFAULT \'1\',
          `type` tinyint(4) NOT NULL DEFAULT \'1\',
          `last_get_gift_time` int(11) NOT NULL DEFAULT \'0\',
          `last_check_in_time` int(11) NOT NULL DEFAULT \'0\',
          `last_rest_pass_time` int(11) NOT NULL DEFAULT \'0\',
          `reg_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          `invite_num` int(8) NOT NULL DEFAULT \'0\',
          `is_admin` int(2) NOT NULL DEFAULT \'0\',
          `ref_by` int(11) NOT NULL DEFAULT \'0\',
          `expire_time` int(11) NOT NULL DEFAULT \'0\',
          `method` varchar(64) NOT NULL DEFAULT \'rc4-md5\',
          `is_email_verify` tinyint(4) NOT NULL DEFAULT \'0\',
          `reg_ip` varchar(128) NOT NULL DEFAULT \'127.0.0.1\',
          PRIMARY KEY (`id`),
          UNIQUE KEY `port` (`port`),
          KEY `node_id` (`node_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8';

        $sql['userTrafficLog'] = '
        CREATE TABLE IF NOT EXISTS `user_traffic_log` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `user_id` int(11) NOT NULL,
          `u` int(11) NOT NULL,
          `d` int(11) NOT NULL,
          `node_id` int(11) NOT NULL,
          `rate` float NOT NULL,
          `traffic` decimal(20,3) NOT NULL,
          `log_time` int(11) NOT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8';

        foreach ($sql as $key => $go) {
            DB::statement($go);
        }

        unset($sql);

        return '数据表创建成功，默认管理员账号为admin，密码nimda';
    }
}
